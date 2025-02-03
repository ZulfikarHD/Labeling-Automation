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

/*
|--------------------------------------------------------------------------
| Production Order Routes
|--------------------------------------------------------------------------
*/
Route::post('/register-production-order', [ProductionOrderController::class, 'store']);
Route::put('/production-order-finish/{noPo}', [ProductionOrderController::class, 'updateStatusFinish']);
Route::post('/production-order/update-rim', [ProductionOrderController::class, 'update']);
Route::get('/production-order/get-labels/{no_po}', [GeneratedLabelController::class, 'getLabels']);
Route::post('/production-order/update-label', [GeneratedLabelController::class, 'update']);
Route::post('/production-order/add-rim', [GeneratedLabelController::class, 'addRim']);
Route::post('/production-order/delete-labels', [GeneratedLabelController::class, 'batchDelete']);

/*
|--------------------------------------------------------------------------
| Order Besar Routes
|--------------------------------------------------------------------------
*/
Route::prefix('order-besar')->group(function () {
    Route::get('/register-no-po/{noPo}', [App\Http\Controllers\OrderBesar\RegisterNomorPoController::class, 'show']);
    Route::post('/register-no-po', [App\Http\Controllers\OrderBesar\RegisterNomorPoController::class, 'store']);

    Route::prefix('cetak-label')->group(function () {
        Route::get('/data/{team}/{id}', [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'getData']);

        Route::post('/', [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'store']);

        Route::post('/edit', [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'edit']);

        Route::post('/update', [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'update']);

        Route::delete('/{id}', [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'delete']);

        Route::get('/verification-status/{team}', [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'getVerificationStatus']);
    });

    Route::get('/verif/{team}', [PoSiapVerifController::class, 'fetchWorkPo']);
});

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
Route::post('/update-spec', [UpdateSpecController::class, 'updateSpec']);
Route::get('/active-teams', [PendapatanHarianController::class, 'getActiveTeams']);
