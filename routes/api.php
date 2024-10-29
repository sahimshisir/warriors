<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\user\UserController;


Route::prefix('auth')->group(function () {
    require base_path('routes/auth.php');
});

// Register Account 
Route::get('/checkEnquiry', [UserController::class, 'checkEnquiry']);
Route::post('register', [UserController::class, 'register'])->name('register');
Route::post('login', [UserController::class, 'login'])->name('login');
Route::post('logout', [UserController::class, 'logout'])->name('logout')->middleware('auth:sanctum');
Route::post('send-otp', [UserController::class, 'sendOtp']);
Route::post('verify-otp', [UserController::class, 'verifyOtp']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');