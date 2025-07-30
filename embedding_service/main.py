from fastapi import FastAPI, Request
from pydantic import BaseModel
from sentence_transformers import SentenceTransformer, CrossEncoder
import psycopg2
import os
from pathlib import Path
from dotenv import load_dotenv
from datetime import datetime
from zoneinfo import ZoneInfo
from typing import Optional, Dict, Any, List
import json
from langchain.text_splitter import RecursiveCharacterTextSplitter

# Load .env
dotenv_path = Path(__file__).resolve().parents[1] / '.env'
load_dotenv(dotenv_path=dotenv_path)

# FastAPI
app = FastAPI()

# Embedding model (768-dim)
model = SentenceTransformer("multi-qa-mpnet-base-dot-v1", device="cpu")

# Reranker model (untuk hasil similarity yang lebih akurat)
reranker = CrossEncoder("cross-encoder/ms-marco-MiniLM-L-6-v2")

# DB config
DB_CONFIG = {
    "dbname": os.getenv("DB_MAIN_NAME"),
    "user": os.getenv("DB_MAIN_USER"),
    "password": os.getenv("DB_MAIN_PASS"),
    "host": os.getenv("DB_MAIN_HOST"),
    "port": os.getenv("DB_MAIN_PORT", 5436)
}

# Request models
class KnowledgeRequest(BaseModel):
    text: str
    user_id: str = None
    agent_id: int = None
    source: str
    document_id: str = None
    metadata: Optional[Dict[str, Any]] = None
    created_by: str

class TextRequest(BaseModel):
    text: str

# Utility: Chunking tools
def split_text_with_langchain(text: str, chunk_size: int = 1000, chunk_overlap: int = 100) -> List[str]:
    splitter = RecursiveCharacterTextSplitter(
        chunk_size=chunk_size,
        chunk_overlap=chunk_overlap,
        separators=["\n\n", "\n", ".", " "]
    )
    return splitter.split_text(text)

# Save ke DB
def save_to_db(entries):
    conn = psycopg2.connect(**DB_CONFIG)
    cur = conn.cursor()
    for entry in entries:
        cur.execute("""
            INSERT INTO knowledges (user_id, agent_id, text, embedding, source, document_id, chunk_index, metadata, created_by, created_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """, (
            entry['user_id'],
            entry['agent_id'],
            entry['text'],
            entry['embedding'],
            entry['source'],
            entry['document_id'],
            entry['chunk_index'],
            json.dumps(entry['metadata']) if entry.get('metadata') else None,
            entry['created_by'],
            entry['created_at']
        ))
    conn.commit()
    cur.close()
    conn.close()

# Endpoint: Embed & Store
@app.post("/embed-and-store")
async def embed_and_store(data: KnowledgeRequest):
    chunks = split_text_with_langchain(data.text, chunk_size=800, chunk_overlap=100)
    results = []
    current_time = datetime.now(ZoneInfo("Asia/Jakarta")).isoformat()

    for idx, chunk in enumerate(chunks):
        embedding = model.encode(chunk).tolist()
        results.append({
            "user_id": data.user_id,
            "agent_id": data.agent_id,
            "text": chunk,
            "embedding": embedding,
            "source": data.source,
            "chunk_index": idx,
            "metadata": data.metadata,
            "document_id": data.document_id,
            "created_by": data.created_by,
            "created_at": current_time
        })

    save_to_db(results)
    return {"status": "success", "chunks_inserted": len(results)}

# Endpoint: Embed Text
@app.post("/embed")
async def embed_text(data: TextRequest):
    embedding = model.encode(data.text).tolist()
    return {"embedding": embedding}

class RerankRequest(BaseModel):
    text: str
    candidates: List[Dict[str, str]]  # id, text
# Endpoint: Rerank Top-N (Optional Example)
@app.post("/rerank")
async def rerank_candidates(data: RerankRequest):
    query = data.text
    pairs = [[query, item["text"]] for item in data.candidates]
    scores = reranker.predict(pairs)

    reranked = sorted(zip(data.candidates, scores), key=lambda x: x[1], reverse=True)

    return {
        "results": [
            {
                "id": item["id"],
                "text": item["text"],
                "score": float(score)
            }
            for item, score in reranked
        ]
    }
