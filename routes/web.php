<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/auth', [AuthController::class, 'authenticate'])->name('login')->middleware('throttle:auth-requests');
Route::post('/verify-otp', [AuthController::class, 'verify'])->middleware('throttle:otp-verifies');
Route::post('/check-otp', [AuthController::class, 'checkOtp'])->middleware('throttle:otp-checks');
Route::post('/logout', [AuthController::class, 'logout']);
