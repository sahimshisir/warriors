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
<<<<<<< HEAD
Route::post('login', [UserController::class, 'login'])->name('login');
Route::get('/logout', [UserController::class, 'logout'])->name('logout');

=======
Route::post('login', [UserController::class, 'login'])->name('login')->middleware('auth.redirect');
>>>>>>> 8cf39585f873122ca884bd73c623fd0641aee1fd

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
