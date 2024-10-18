<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\user\UserController;


Route::prefix('auth')->group(function () {
    require base_path('routes/auth.php');
});
// product route

// Register Account 
Route::get('/checkUsername', [UserController::class, 'checkUsername']);
Route::post('register', [UserController::class, 'register'])->name('register');
Route::post('login', [UserController::class, 'login'])->name('login');


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
