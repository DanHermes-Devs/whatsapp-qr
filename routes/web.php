<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/whatsapp-login', function () {
    return view('whatsapp-login');
});

Route::post('/save-chats', [ChatController::class, 'saveChats']);
Route::get('/get-chats', [ChatController::class, 'getChats']);
