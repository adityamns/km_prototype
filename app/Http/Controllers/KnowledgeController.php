<?php

namespace App\Http\Controllers;

use App\Http\Services\EmbeddingService as ServicesEmbeddingService;
use App\Models\Knowledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

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
                // 'agent_id' => 'nullable|exists:agents,id',
            ]);

            $request->merge([
                'source' => 'manual',
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

    public function storePdf(Request $request)
    {
        try {
            $request->validate([
                'pdf_file' => 'required|file|mimes:pdf',
                // 'agent_id' => 'nullable|exists:agents,id',
            ]);


            $pdf = (new \Smalot\PdfParser\Parser())->parseFile($request->file('pdf_file')->getRealPath());
            $text = $pdf->getText();

            $documentId = Str::uuid()->toString();
            $filename = $request->file('pdf_file')->getClientOriginalName();

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
                // 'agent_id' => 'nullable|exists:agents,id',
            ]);

            $url = escapeshellarg($request->url);
            $scriptPath = base_path('app/Http/Controllers/Crawler/extract.js');
            // $nodePath = 'C:\\Program Files\\nodejs\\node.exe'; // pastikan path ini benar
            $nodePath = 'node';

            $command = "\"$nodePath\" \"$scriptPath\" $url";
            $output = shell_exec($command . ' 2>&1'); // <-- redirect error ke output

            $request->merge([
                'source' => 'crawl',
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
                // 'agent_id' => 'required|exists:agents,id',
                'limit' => 'required',
            ]);

            $embedding = $this->embedder->embedText($request->text);
            // Convert array to PostgreSQL vector string format
            $vector = '[' . implode(',', $embedding) . ']';

            $bindings = [$vector];

            $sql = "
                    SELECT id, text, agent_id,
                        1 - (embedding <=> ?::vector) AS similarity
                    FROM knowledges
                ";

            if ($request->agent_id) {
                $sql .= " WHERE agent_id = ? ";
                $bindings[] = $request->agent_id;
            }

            $sql .= " ORDER BY embedding <=> ?::vector ASC LIMIT ?";

            // Duplicate embedding vector for ORDER BY
            $bindings[] = $vector;
            $bindings[] = $request->limit;

            $result = DB::select($sql, $bindings);

            return response()->json(["status" => true, 'data' => $result]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
