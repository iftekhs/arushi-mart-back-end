<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/auth', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout']);
