<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneratedProductsController;
use App\Http\Controllers\GenerateLabelsPersonalController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Generate Label Category Personal API
    Route::get('/gen-perso-label/{noPo}',[GenerateLabelsPersonalController::class, 'show']);

// Generate Label Category Non-Personal API
    Route::get('/gen-nonPerso-po/{noPo}',[GeneratedProductsController::class, 'show']);
    Route::put('/gen-nonPerso-po/{noPo}',[GeneratedProductsController::class, 'updateStatus']);
