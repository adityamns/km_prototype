<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\KnowledgeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// knowledgeroutes
Route::post('/store_manual', [KnowledgeController::class, 'storeManual']);
Route::post('/store_file', [KnowledgeController::class, 'storePdf']);
Route::post('/store_url', [KnowledgeController::class, 'storeFromUrl']);
Route::post('/embed', [KnowledgeController::class, 'embedText']);
Route::post('/similarity', [KnowledgeController::class, 'searchSimilarKnowledge']);

Route::get('/agents', [AgentController::class, 'index']);
Route::post('/agents', [AgentController::class, 'store']);
Route::get('/agents/{id}', [AgentController::class, 'show']);
Route::put('/agents/{id}', [AgentController::class, 'update']);
Route::delete('/agents/{id}', [AgentController::class, 'destroy']);


Route::post('/chats', [ChatController::class, 'chat']);
Route::post('/stateless-chat', [ChatController::class, 'stateless_chat']);
Route::get('/chat-topics/{agent_id}', [ChatController::class, 'getList']);
Route::delete('/chats/{id}', [ChatController::class, 'destroy']);
Route::get('/chat-histories/{chat_id}/{agent_id}', [ChatController::class, 'getHistory']);