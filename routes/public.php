<?php

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/chat', ChatController::class)->name('chat.index');
    Route::post('/chat/conversations', [ConversationController::class, 'store'])->name('chat.conversations.store');
    Route::get('/chat/conversations/{conversation}/messages', [MessageController::class, 'index'])->name('chat.messages.index');
    Route::post('/chat/conversations/{conversation}/messages', [MessageController::class, 'store'])->name('chat.messages.store');
});
