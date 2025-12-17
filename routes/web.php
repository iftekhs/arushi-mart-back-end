<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth', [AuthController::class, 'authenticate'])->name('login')->middleware('throttle:auth-requests');
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:otp-resend');
Route::post('/check-otp', [AuthController::class, 'checkOtp'])->middleware('throttle:otp-checks');
Route::post('/check-auth-type', [AuthController::class, 'checkAuthType'])->middleware('throttle:otp-checks');
Route::post('/verify-otp', [AuthController::class, 'verify'])->middleware('throttle:otp-verifies');
Route::post('/verify-password', [AuthController::class, 'verifyPassword'])->middleware('throttle:otp-verifies');
Route::post('/logout', [AuthController::class, 'logout']);
