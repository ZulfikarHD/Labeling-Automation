<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneratedProductsController;
use App\Http\Controllers\GenerateLabelsPersonalController;
use App\Http\Controllers\GenerateLabelsController;
use App\Http\Controllers\PrintLabelController;

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

    Route::put('/nonPers-upStat/{noPo}',[GeneratedProductsController::class, 'updateStatus']);

// Generate Label Category Non-Personal API
    Route::get('/gen-labels/non-personal/{noPo}',[GenerateLabelsController::class, 'show']);    // Fetch Spesifikasi Order
    Route::post('/gen-labels/non-personal',[GenerateLabelsController::class, 'store']);         // Store Created Labels

// Print Label Non Personal
    Route::post('/non-perekat/non-personal/print-label',[PrintLabelController::class, 'store']);
    Route::post('/non-perekat/non-personal/print-label/edit', [PrintLabelController::class, 'edit']);
    Route::post('/non-perekat/non-personal/print-label/update',[PrintLabelController::class, 'update']);
