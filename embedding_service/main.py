from fastapi import FastAPI, Request
from pydantic import BaseModel
from sentence_transformers import SentenceTransformer
import psycopg2
import os
from pathlib import Path
from dotenv import load_dotenv
from datetime import datetime
from zoneinfo import ZoneInfo
from typing import Optional, Dict, Any
import json

dotenv_path = Path(__file__).resolve().parents[1] / '.env'

load_dotenv(dotenv_path=dotenv_path)

app = FastAPI()
model = SentenceTransformer("all-MiniLM-L6-v2")

DB_CONFIG = {
    "dbname": os.getenv("DB_MAIN_NAME"),
    "user": os.getenv("DB_MAIN_USER"),
    "password": os.getenv("DB_MAIN_PASS"),
    "host": os.getenv("DB_MAIN_HOST"),
    "port": os.getenv("DB_MAIN_PORT", 5436)
}

class KnowledgeRequest(BaseModel):
    text: str
    user_id: str = None
    agent_id: int = None
    source: str
    document_id: str = None
    metadata: Optional[Dict[str, Any]] = None
    created_by: str

def split_paragraphs(text: str):
    # Simple split by double line break
    return [p.strip() for p in text.split('\n\n') if p.strip()]

def save_to_db(entries):
    conn = psycopg2.connect(**DB_CONFIG)
    cur = conn.cursor()
    for entry in entries:
        cur.execute("""
            INSERT INTO knowledges (user_id, agent_id, text, embedding, source, document_id, chunk_index, metadata, created_by,created_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s,%s)
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

@app.post("/embed-and-store")
async def embed_and_store(data: KnowledgeRequest):
    paragraphs = split_paragraphs(data.text)
    results = []
    current_time = datetime.now(ZoneInfo("Asia/Jakarta")).isoformat()
    
    for idx, paragraph in enumerate(paragraphs):
        embedding = model.encode(paragraph).tolist()
        results.append({
            "user_id": data.user_id,
            "agent_id": data.agent_id,
            "text": paragraph,
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

class TextRequest(BaseModel):
    text: str
@app.post("/embed")
async def embed_text(data: TextRequest):
    embedding = model.encode(data.text).tolist()
    return {
        "embedding": embedding
    }

# uvicorn main:app --host 0.0.0.0 --port 8180 --reload