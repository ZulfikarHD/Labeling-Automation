<?php

use App\Http\Controllers\GeneratedLabelController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderSiapPeriksaController;
use App\Http\Controllers\PendapatanHarianController;
use App\Http\Controllers\PrintLabel\PrintLabelMmeaController;
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
| Production Order Routes
|--------------------------------------------------------------------------
*/
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
    });

    Route::get('/verif/{team}', [OrderSiapPeriksaController::class, 'fetchWorkPo']);
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
Route::get('/pendapatan-harian-mmea',[PendapatanHarianController::class, 'gradeHarianMmea']);
Route::get('/team-name/{id}', [App\Models\Workstations::class, 'getTeamName']);
Route::get('/active-teams', [PendapatanHarianController::class, 'getActiveTeams']);


/*
|--------------------------------------------------------------------------
| Print Label Inspeksi Routes
|--------------------------------------------------------------------------
*/
Route::get('/print-label/inspeksi/count-remaining-label/{no_po}', [App\Http\Controllers\PrintLabel\PrintLabelInspeksiController::class, 'getRemainingLabelCount']);
Route::post('/print-label/inspeksi/store', [App\Http\Controllers\PrintLabel\PrintLabelInspeksiController::class, 'store']);


/*
|--------------------------------------------------------------------------
| Print Label MMEA Routes
|--------------------------------------------------------------------------
*/
Route::post('/print-label/mmea/store', [PrintLabelMmeaController::class, 'store']);
Route::post('/print-label/mmea/storeProduct', [PrintLabelMmeaController::class, 'storeProduct']);
Route::get('/mmea/qc-data/{nomor_po}', [PrintLabelMmeaController::class,  'qcData']);

Route::post('/update-spec', [UpdateSpecController::class, 'updateSpec']);
