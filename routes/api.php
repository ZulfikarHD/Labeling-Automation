<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenerateLabelsPersonalController;
use App\Http\Controllers\OrderBesar\PoSiapVerifController;
use App\Http\Controllers\PrintLabelPersonalController;
use App\Http\Controllers\PendapatanHarianController;
use App\Http\Controllers\ProductionOrderController;
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
    Route::put('/production-order-finish/{noPo}', [ProductionOrderController::class, 'updateStatusFinish']);

// Generate Label Category Non-Personal API
    Route::get('/order-besar/register-no-po/{noPo}',[App\Http\Controllers\OrderBesar\RegisterNomorPoController::class, 'show']);
    Route::post('/order-besar/register-no-po',   [App\Http\Controllers\OrderBesar\RegisterNomorPoController::class, 'store']);

// Print Label Order Besar
    Route::post('/order-besar/cetak-label',[App\Http\Controllers\OrderBesar\CetakLabelController::class, 'store']);
    Route::post('/order-besar/cetak-label/edit',  [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'edit']);
    Route::post('/order-besar/cetak-label/update',[App\Http\Controllers\OrderBesar\CetakLabelController::class, 'update']);

    Route::get('/order-besar/verif/{team}',[PoSiapVerifController::class, 'fetchWorkPo']);

// Print Label OrderKecil
    Route::get('/order-kecil/fetch-spec/{no_po}',[App\Http\Controllers\OrderKecil\CetakLabelController::class, 'show']);

// Calculation
    Route::get('/pendapatan-harian', [PendapatanHarianController::class, 'gradeHarian']);

// Update Spec
    Route::post('/update-spec', [UpdateSpecController::class, 'updateSpec']);

    Route::post('/register-production-order',   [App\Http\Controllers\ProductionOrderController::class, 'store']);
