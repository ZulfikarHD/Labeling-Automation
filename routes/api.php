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
Route::post('/production-order/update-rim', [ProductionOrderController::class, 'update']);
Route::put('/production-order-finish/{noPo}', [ProductionOrderController::class, 'updateStatusFinish']);
Route::get('/production-order/get-labels/{no_po}', [GeneratedLabelController::class, 'getLabels']);
Route::post('/production-order/update-label', [GeneratedLabelController::class, 'update']);

/*
|--------------------------------------------------------------------------
| Order Besar Routes
|--------------------------------------------------------------------------
*/
Route::get('/order-besar/register-no-po/{noPo}', [App\Http\Controllers\OrderBesar\RegisterNomorPoController::class, 'show']);
Route::post('/order-besar/register-no-po', [App\Http\Controllers\OrderBesar\RegisterNomorPoController::class, 'store']);
Route::post('/order-besar/cetak-label', [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'store']);
Route::post('/order-besar/cetak-label/edit', [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'edit']);
Route::post('/order-besar/cetak-label/update', [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'update']);
Route::get('/order-besar/verif/{team}', [PoSiapVerifController::class, 'fetchWorkPo']);

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
