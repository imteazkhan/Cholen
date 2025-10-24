<?php

use App\Http\Controllers\loginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('/login', [loginController::class, 'submit']);
Route::post('/login/verify', [loginController::class, 'verify']);


// Route::post('/login/verify', [loginController::class, 'verify'])