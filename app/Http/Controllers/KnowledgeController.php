<?php

namespace App\Http\Controllers;

use App\Http\Services\EmbeddingService as ServicesEmbeddingService;
use App\Models\Knowledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http; // pastikan import ini

class KnowledgeController extends Controller
{
    protected $embedder;

    public function __construct(ServicesEmbeddingService $embedder)
    {
        $this->embedder = $embedder;
    }
    public function storeManual(Request $request)
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|min:2|max:10000',
                'agent_id' => 'nullable|exists:agents,id',
            ]);

            $documentId = Str::uuid()->toString();
            $request->merge([
                'source' => 'manual',
                'document_id' => $documentId,
            ]);

            $embedding = $this->embedder->embedAndStoreText($request);

//var_dum($embedding); die;
            if ($embedding) {
                return response()->json(['message' => 'Knowledge saved successfully.'], 201);
            } else {
                return response()->json(['message' => 'Knowledge saved failed.'], 500);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function storePdf(Request $request)
    {
        // var_dump($request->file('file')->getRealPath()); die;

        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf|max:5120',
                'agent_id' => 'nullable|exists:agents,id',
            ]);


            $pdf = (new \Smalot\PdfParser\Parser())->parseFile($request->file('file')->getRealPath());
            $text = $pdf->getText();


            $documentId = Str::uuid()->toString();
            $filename = $request->file('file')->getClientOriginalName();

            $request->merge([
                'source' => 'pdf',
                'text' => $text,
                'document_id' => $documentId,
                'metadata' => [
                    'filename' => $filename,
                ],
            ]);

            $embedding = $this->embedder->embedAndStoreText($request);
            if ($embedding) {
                return response()->json(['message' => 'Knowledge saved successfully.'], 201);
            } else {
                return response()->json(['message' => 'Knowledge failed successfully.'], 500);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function storeFromUrl(Request $request)
    {
        try {
            $request->validate([
                'url' => 'required|url',
                'agent_id' => 'nullable|exists:agents,id',
            ]);

            $url = escapeshellarg($request->url);
            $scriptPath = base_path('app/Http/Controllers/Crawler/extract.js');
            // $nodePath = 'C:\\Program Files\\nodejs\\node.exe'; // pastikan path ini benar
            $nodePath = 'node';

            $command = "\"$nodePath\" \"$scriptPath\" $url";
            $output = shell_exec($command . ' 2>&1'); // <-- redirect error ke output
            $documentId = Str::uuid()->toString();
            $request->merge([
                'source' => 'crawl',
                'document_id' => $documentId,
                'text' => $output,
                'metadata' => [
                    'url' => $url,
                ],
            ]);

            $embedding = $this->embedder->embedAndStoreText($request);
            if ($embedding) {
                return response()->json(['message' => 'Knowledge saved successfully.'], 201);
            } else {
                return response()->json(['message' => 'Knowledge failed successfully.'], 500);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function embedText(Request $request)
    {
        $embedding = $this->embedder->embedText($request->text);

        return response()->json(['embedding' => $embedding]);
    }

public function searchSimilarKnowledge(Request $request)
{
    try {
        $validated = $request->validate([
            'text' => 'required|string|min:2|max:10000',
            'limit' => 'required|integer|min:1|max:50',
            'agent_id' => 'nullable|integer|exists:agents,id',
        ]);

        $embedding = $this->embedder->embedText($validated['text']);
        $vector = '[' . implode(',', $embedding) . ']';

        // Base SQL
        $sql = "
            SELECT id, text, agent_id,
                1 - (embedding <=> ?::vector) AS similarity
            FROM knowledges
        ";
        $bindings = [$vector];

        if (!empty($validated['agent_id'])) {
            $sql .= " WHERE agent_id = ? ";
            $bindings[] = $validated['agent_id'];
        }

        $sql .= " ORDER BY embedding <=> ?::vector ASC LIMIT ?";
        $bindings[] = $vector;
        $bindings[] = (int) $validated['limit'];

        $result = DB::select($sql, $bindings);
        // Jika result kosong, hentikan proses
        if (empty($result)) {
            return response()->json([
                'message' => 'Knowledge Kosong',
                'data' => [],
            ]);
        }
        // Kirim ke Python FastAPI untuk rerank
        $rerankPayload = [
            'text' => $validated['text'],
            'candidates' => array_map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->text,
                ];
            }, $result),
        ];

        $response = Http::post('http://localhost:8180/rerank', $rerankPayload); // sesuaikan port

        if ($response->successful()) {
            return response()->json([
                'status' => true,
                'data' => $response->json()['results'],
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Gagal melakukan rerank',
                'debug' => $response->body()
            ], 500);
        }

    } catch (ValidationException $e) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Internal error',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function getCrawledDocuments($agent_id)
    {
        try {
            $data = DB::table('knowledges')
                ->select('document_id', 'metadata')
                ->where('agent_id', $agent_id)
                ->where('source', 'crawl')
                ->groupBy('document_id', 'metadata')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Crawled documents retrieved successfully.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve crawled documents.',
                'data' => []
            ], 500);
        }
    }

    public function getPdfDocuments($agent_id)
    {
        try {
            $data = DB::table('knowledges')
                ->select('document_id', 'metadata')
                ->where('agent_id', $agent_id)
                ->where('source', 'pdf')
                ->groupBy('document_id', 'metadata')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'PDF documents retrieved successfully.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve PDF documents.',
                'data' => []
            ], 500);
        }
    }

    public function getManualTexts($agent_id)
    {
        try {
            $data = DB::table('knowledges')
                ->select('document_id', 'text')
                ->where('agent_id', $agent_id)
                ->where('source', 'manual')
                ->groupBy('document_id', 'text')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Manual texts retrieved successfully.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve manual texts.',
                'data' => []
            ], 500);
        }
    }

    public function deleteByDocumentId($document_id)
    {
        try {
            $deleted = DB::table('knowledges')
                ->where('document_id', $document_id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'status' => true,
                    'message' => "Knowledge(s) with document_id $document_id deleted successfully.",
                    'data' => null
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "No knowledge found with document_id $document_id.",
                    'data' => null
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete knowledge.',
                'data' => null
            ], 500);
        }
    }
}
