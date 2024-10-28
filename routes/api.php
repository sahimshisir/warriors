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
<<<<<<< HEAD
=======
Route::post('logout', [UserController::class, 'logout'])
    ->name('logout')
    ->middleware('auth:sanctum'); 
>>>>>>> ae880c56c88953d20ee946050759885d47a50937

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
