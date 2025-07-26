<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;


class EmbeddingService
{
    public function embedAndStoreText(Request $req)
    {
        $requestData = [
            'user_id' => optional($req->user())->id ?? 'developer',
            'text' => $req->text,
            'agent_id' => $req->agent_id,
            'source' => $req->source,
            'created_by' => optional($req->user())->id ?? 'developer',
        ];

        if ($req->document_id !== null) {
            $requestData['document_id'] = $req->document_id;
        }

        if ($req->metadata !== null) {
            $requestData['metadata'] = $req->metadata;
        }

        $response = Http::post(env('BASE_URL_PY_APP') . '/embed-and-store', $requestData);
        // var_dump($response->json());
        // die;
        if ($response->successful()) {
            return true;
        } else {
            return false;
            // throw new \Exception('Embedding failed: ' . json_encode($response->json()));
        }
    }


    public function embedText($text)
    {
        $response = Http::post(env('BASE_URL_PY_APP') . '/embed', [
            'text' => $text,
        ]);

        if ($response->successful()) {
            return $response->json()['embedding'];
        } else {
            throw new \Exception('Embedding failed: ' . json_encode($response->json()));
        }
    }
}
