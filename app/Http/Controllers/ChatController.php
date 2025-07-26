<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Http\Controllers\KnowledgeController;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\ChatHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Proses chat (create chat dan chat_history)
     */
    public function chat(Request $request, KnowledgeController $knowledgeController)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'nullable',
            'agent_id' => 'required|exists:agents,id',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $userId = optional($request->user())->id ?? 'developer';

            // Ambil data agent
            $agent = Agent::findOrFail($request->agent_id);

            // Cek apakah chat_id valid
            if ($request->filled('chat_id')) {
                $chat = Chat::where('id', $request->chat_id)
                    ->where('user_id', $userId)
                    ->where('agent_id', $request->agent_id)
                    ->first();

                if (!$chat) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Chat ID tidak ditemukan.',
                        'data' => null
                    ], 404);
                }
            } else {
                // Buat chat baru
                $chat = Chat::create([
                    'user_id' => $userId,
                    'agent_id' => $request->agent_id,
                    'title' => substr($request->message, 0, 50),
                    'created_by' => $userId,
                ]);
            }

            // Simpan pesan user ke history
            $userMessage = ChatHistory::create([
                'chat_id' => $chat->id,
                'role' => 'user',
                'message' => $request->message,
                'created_by' => $userId,
            ]);

            $tempRequest = new Request;
            $tempRequest->merge([
                'text' => $request->message,
                'agent_id' => $request->agent_id,
                'limit' => 5,
            ]);
            // --- Step 1: Ambil Knowledge Berdasarkan Pesan ---
            $knowledgeResponse = $knowledgeController->searchSimilarKnowledge($tempRequest);
            $knowledgeData = $knowledgeResponse->getData(true)['data'];
            // --- Step 2: Siapkan prompt untuk OpenRouter ---
            $contextText = collect($knowledgeData)->pluck('text')->implode("\n");

            $histories = ChatHistory::where('chat_id', $chat->id)
                ->orderByDesc('created_at')
                ->limit(4)
                ->get()
                ->reverse();
            $messages = [
                ['role' => 'system', 'content' => $agent->system_prompt],
                ['role' => 'user', 'content' => "Berikut adalah referensi:\n\n" . $contextText],
                ['role' => 'user', 'content' => $request->message],
            ];
            foreach ($histories as $history) {
                $messages[] = [
                    'role' => $history->role,
                    'content' => $history->message,
                ];
            }

            // --- Step 3: Kirim ke OpenRouter ---
            $openRouterResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $agent->openrouter_key,
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $agent->model,
                'messages' => $messages,
                'temperature' => (float) $agent->temperature,
            ]);

            $reply = $openRouterResponse->json('choices.0.message.content');

            // --- Step 4: Simpan jawaban dari AI ke history ---
            $assistantMessage = ChatHistory::create([
                'chat_id' => $chat->id,
                'role' => 'system',
                'message' => $reply,
                'raw_response' => $openRouterResponse->json(),
                'created_by' => $userId,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Chat berhasil diproses.',
                'data' => [
                    'chat' => $chat,
                    'user_message' => $userMessage,
                    'assistant_message' => $assistantMessage,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat proses chat.',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil list chat berdasarkan user_id dan optional agent_id
     */
    public function getList(Request $request, $agent_id)
    {
        $user_id = optional($request->user())->id ?? 'developer';
        $query = Chat::where('user_id', $user_id);

        if ($request->filled('agent_id')) {
            $query->where('agent_id', $agent_id);
        }

        $chats = $query->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'List chat berhasil diambil.',
            'data' => $chats
        ]);
    }

    public function getHistory(Request $request, $chat_id, $agent_id)
    {
        $user_id = optional($request->user())->id ?? 'developer';
        // Cek bahwa chat tersebut benar milik user & agent (opsional)
        $chat = Chat::where('id', $chat_id)
            ->where('user_id', $user_id)
            ->when($request->filled('agent_id'), function ($query) use ($agent_id) {
                $query->where('agent_id', $agent_id);
            })
            ->first();

        if (!$chat) {
            return response()->json([
                'status' => false,
                'message' => 'Chat tidak ditemukan atau bukan milik Anda.',
                'data' => null
            ], 404);
        }

        $histories = ChatHistory::where('chat_id', $chat->id)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Histori chat berhasil diambil.',
            'data' => $histories
        ]);
    }

    /**
     * Hapus chat berdasarkan ID (termasuk seluruh history-nya)
     */
    public function destroy($id)
    {
        $chat = Chat::find($id);

        if (!$chat) {
            return response()->json([
                'status' => false,
                'message' => 'Chat tidak ditemukan.',
                'data' => null
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Hapus semua history terkait
            ChatHistory::where('chat_id', $chat->id)->delete();

            // Hapus chat utama
            $chat->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Chat dan seluruh historinya berhasil dihapus.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus chat.',
                'data' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Proses chat (create chat dan chat_history)
     */
    public function stateless_chat(Request $request, KnowledgeController $knowledgeController)
    {
        $validator = Validator::make($request->all(), [
            'access_key' => 'required',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            // Ambil data agent
            $agent = Agent::where('access_key', $request->access_key)
                ->where('is_publish', true)
                ->first();

            if (!$agent) {
                return response()->json([
                    'status' => false,
                    'message' => 'Agent tidak ditemukan atau belum dipublish.',
                    'data' => null
                ], 404);
            }

            $tempRequest = new Request;
            $tempRequest->merge([
                'text' => $request->message,
                'agent_id' => $request->agent_id,
                'limit' => 5,
            ]);
            // --- Step 1: Ambil Knowledge Berdasarkan Pesan ---
            $knowledgeResponse = $knowledgeController->searchSimilarKnowledge($tempRequest);
            $knowledgeData = $knowledgeResponse->getData(true)['data'];
            // --- Step 2: Siapkan prompt untuk OpenRouter ---
            $contextText = collect($knowledgeData)->pluck('text')->implode("\n");

            $messages = [
                ['role' => 'system', 'content' => $agent->system_prompt],
                ['role' => 'user', 'content' => "Berikut adalah referensi:\n\n" . $contextText],
                ['role' => 'user', 'content' => $request->message],
            ];

            // --- Step 3: Kirim ke OpenRouter ---
            $openRouterResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $agent->openrouter_key,
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => $agent->model,
                'messages' => $messages,
                'temperature' => (float) $agent->temperature,
            ]);
            $reply = $openRouterResponse->json('choices.0.message.content');

            return response()->json([
                'status' => true,
                'message' => 'Chat berhasil diproses.',
                'data' => [
                    'user_message' => $request->message,
                    'assistant_message' => $reply,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat proses chat.',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
