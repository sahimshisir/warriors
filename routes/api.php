<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\user\UserController;

// Public routes (no auth needed)
Route::post('register', [UserController::class, 'register'])->name('register');
Route::post('login', [UserController::class, 'login'])->name('login');
Route::post('logout', [UserController::class, 'logout'])->name('logout')->middleware('auth:sanctum');
Route::post('send-otp', [UserController::class, 'sendOtp']);
Route::post('verify-otp', [UserController::class, 'verifyOtp']);

// Protected routes (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/checkEnquiry', [UserController::class, 'checkEnquiry']);
    Route::post('logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
