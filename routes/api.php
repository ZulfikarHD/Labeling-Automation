<?php

use App\Http\Controllers\GeneratedLabelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenerateLabelsPersonalController;
use App\Http\Controllers\OrderBesar\PoSiapVerifController;
use App\Http\Controllers\PrintLabelPersonalController;
use App\Http\Controllers\PendapatanHarianController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\UpdateSpecController;
use App\Http\Controllers\OrderBesar\RegisterNomorPoController;
use App\Http\Controllers\OrderBesar\CetakLabelController;

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

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::middleware('auth:sanctum')->group(function () {
    // Production Order Routes
    Route::prefix('production-order')->group(function () {
        // Route::post('register', [ProductionOrderController::class, 'store']);
        Route::put('{noPo}/finish', [ProductionOrderController::class, 'updateStatusFinish']);
        Route::put('update-rim', [ProductionOrderController::class, 'update']);
        // Route::get('{no_po}/labels', [GeneratedLabelController::class, 'getLabels']);
        Route::put('labels', [GeneratedLabelController::class, 'update']);
        Route::post('labels/rim', [GeneratedLabelController::class, 'addRim']);
        Route::delete('labels', [GeneratedLabelController::class, 'batchDelete']);
    });

    // Order Besar Routes
    Route::prefix('order-besar')->group(function () {
        Route::apiResource('register-no-po', RegisterNomorPoController::class);
        Route::apiResource('cetak-label', CetakLabelController::class);
        Route::put('cetak-label', [CetakLabelController::class, 'update']);
        Route::get('cetak-label/show/{dataPO}', [CetakLabelController::class, 'show']);
        Route::get('verif/{team}', [PoSiapVerifController::class, 'fetchWorkPo']);
    });
// });

/*
|--------------------------------------------------------------------------
| Order Kecil Routes
|--------------------------------------------------------------------------
*/
Route::get('/order-kecil/fetch-spec/{no_po}', [App\Http\Controllers\OrderKecil\CetakLabelController::class, 'show']);
Route::post('/order-kecil/cetak-label', [App\Http\Controllers\OrderKecil\CetakLabelController::class, 'cetakLabel']);

/*
|--------------------------------------------------------------------------
| Utility Routes
|--------------------------------------------------------------------------
*/
Route::get('/pendapatan-harian', [PendapatanHarianController::class, 'gradeHarian']);
Route::get('/team-name/{id}', [App\Models\Workstations::class, 'getTeamName']);
Route::post('/update-spec', [UpdateSpecController::class, 'updateSpec']);
Route::get('/active-teams', [PendapatanHarianController::class, 'getActiveTeams']);
