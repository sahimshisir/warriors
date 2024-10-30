<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\user\UserController;

Route::prefix('auth')->group(function () {
    require base_path('routes/auth.php');
});

// Public Routes
Route::get('/checkEnquiry', [UserController::class, 'checkEnquiry']);
Route::post('register', [UserController::class, 'register'])->name('register');
Route::post('login', [UserController::class, 'login'])->name('login'); // Removed auth:sanctum here

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('auth:rememberMe')->get('/check-auth', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });
});

Route::post('send-otp', [UserController::class, 'sendOtp']);
Route::post('verifyOtp', [UserController::class, 'verifyOtp']);

// forgot password route
Route::post('/forgot-password', [UserController::class, 'sendOtpforgot']);
Route::post('/forgot_verify-otp', [UserController::class, 'verifyOtpforgot']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);
