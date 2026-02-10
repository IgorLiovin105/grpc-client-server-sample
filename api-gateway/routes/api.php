<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\HelloController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello', [HelloController::class, 'sendHello']);
Route::post('/send-file', [FileController::class, 'sendFile']);
