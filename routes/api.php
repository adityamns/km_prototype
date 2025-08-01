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
Route::post('/store_manual', [KnowledgeController::class, 'storeManual'])->middleware(['verify.token.scope:old-pegawai']);
Route::post('/store_file', [KnowledgeController::class, 'storePdf'])->middleware(['verify.token.scope:old-pegawai']);
Route::post('/store_url', [KnowledgeController::class, 'storeFromUrl'])->middleware(['verify.token.scope:old-pegawai']);
Route::post('/embed', [KnowledgeController::class, 'embedText'])->middleware(['verify.token.scope:old-pegawai']);
Route::post('/similarity', [KnowledgeController::class, 'searchSimilarKnowledge'])->middleware(['verify.token.scope:old-pegawai']);
Route::get('/knowledges/crawl/{agent_id}', [KnowledgeController::class, 'getCrawledDocuments'])->middleware(['verify.token.scope:old-pegawai']);
Route::get('/knowledges/pdf/{agent_id}', [KnowledgeController::class, 'getPdfDocuments'])->middleware(['verify.token.scope:old-pegawai']);
Route::get('/knowledges/manual/{agent_id}', [KnowledgeController::class, 'getManualTexts'])->middleware(['verify.token.scope:old-pegawai']);
Route::delete('/knowledges/{document_id}', [KnowledgeController::class, 'deleteByDocumentId'])->middleware(['verify.token.scope:old-pegawai']);

Route::get('/agents', [AgentController::class, 'index'])->middleware(['verify.token.scope:old-pegawai']);
Route::post('/agents', [AgentController::class, 'store'])->middleware(['verify.token.scope:old-pegawai']);
Route::get('/agents/{id}', [AgentController::class, 'show'])->middleware(['verify.token.scope:old-pegawai']);
Route::put('/agents/{id}', [AgentController::class, 'update'])->middleware(['verify.token.scope:old-pegawai']);
Route::delete('/agents/{id}', [AgentController::class, 'destroy'])->middleware(['verify.token.scope:old-pegawai']);


Route::post('/chats', [ChatController::class, 'chat'])->middleware(['verify.token.scope:old-pegawai']);
Route::post('/stateless-chat', [ChatController::class, 'stateless_chat']);
Route::get('/chat-topics/{agent_id}', [ChatController::class, 'getList'])->middleware(['verify.token.scope:old-pegawai']);
Route::delete('/chats/{id}', [ChatController::class, 'destroy'])->middleware(['verify.token.scope:old-pegawai']);
Route::get('/chat-histories/{chat_id}/{agent_id}', [ChatController::class, 'getHistory'])->middleware(['verify.token.scope:old-pegawai']);