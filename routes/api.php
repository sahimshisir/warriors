<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    require base_path('routes/auth.php');
});
// product route

    Route::apiResource('products', ProductController::class);

Route::middleware(['auth'])->group(function () {
    // Route::apiResource('products', controller: ProductController::class);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
     