<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneratedProductsController;
use App\Http\Controllers\GenerateLabelsPersonalController;
use App\Http\Controllers\EntryPoController;
use App\Http\Controllers\GeneratedLabelsController;
use App\Http\Controllers\PrintLabelController;
use App\Http\Controllers\PrintLabelPersonalController;
use App\Http\Controllers\PendapatanHarianController;
use App\Http\Controllers\UpdateSpecController;

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
    Route::put('/nonPers-finish-order/{noPo}', [GeneratedProductsController::class, 'updateStatusFinish']);

// Generate Label Category Non-Personal API
    Route::get('/gen-labels/non-personal/{noPo}',[EntryPoController::class, 'show']);    // Fetch Spesifikasi Order
    Route::post('/gen-labels/non-personal',[EntryPoController::class, 'store']);         // Store Created Labels

// Print Label Non Personal
    Route::post('/non-perekat/non-personal/print-label',[PrintLabelController::class, 'store']);
    Route::post('/non-perekat/non-personal/print-label/edit', [PrintLabelController::class, 'edit']);
    Route::post('/non-perekat/non-personal/print-label/update',[PrintLabelController::class, 'update']);

    Route::get('/non-perekat/non-personal/verif/{team}',[GeneratedLabelsController::class, 'fetchWorkPo']);

// Print Label Personal
    Route::post('/non-perekat/personal/print-label',[PrintLabelPersonalController::class, 'store']);

// Calculation
    Route::get('/pendapatan-harian', [PendapatanHarianController::class, 'gradeHarian']);

// Update Spec
    Route::post('/update-spec', [UpdateSpecController::class, 'updateSpec']);
