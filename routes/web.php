<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProductMonitoringController;
use App\Http\Controllers\GenerateLabelsPersonalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Pilih Produk
Route::get('/login', function () {
    return Inertia::render('Login');
})->name('login');

// Route::middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\OrderBesar\PoSiapVerifController::class, 'index'])->name('dashboard');

    // Group Non Perekat //
    //-------------------//

    //Pilih Personal atau Non Pesonal
    Route::get('/non-perekat', function () {
        return Inertia::render('NonPerekat/IndexNonPerekat');
    })->name('nonPer.index');

    // Section Non Perekat, Personal //
    //-------------------------------//
    Route::get('/non-perekat/personal', [GenerateLabelsPersonalController::class, 'index'])
        ->name('nonPer.personal');
    // Section Non Perekat, Non Personal //
    //-----------------------------------//
    // Pilih User
    // Kepala Meja
    Route::get('/non-perekat/non-personal', function () {
        return Inertia::render('NonPerekat/NonPersonal/Index');
    })->name('nonPer.nonPersonal.index');

    // Section Kepala Meja Non Perekat //
    //---------------------------------//
    // Menu Kepala Meja
    Route::get('/non-perekat/non-personal/pic', function () {
        return Inertia::render('NonPerekat/NonPersonal/Pic/Menu');
    })->name('nonPer.nonPersonal.pic.index');

    // Monitoring Barang
    Route::get('/non-perekat/non-personal/pic/monitorVerifikasi',       [ProductMonitoringController::class, 'index'])->name('nonPer.nonPersonal.monitor.index');
    Route::get('/non-perekat/non-personal/pic/monitorVerifikasi/{id}',  [ProductMonitoringController::class, 'show'])->name('nonPer.nonPersonal.monitor.show');

    // Section Verifikator Non Perekat //
    //---------------------------------//
    // Table Hasil Laporan Produksi
    Route::get('/produksi-pegawai', function () {
        return Inertia::render('ProduksiPegawai', [
            'teams' => \App\Models\Workstations::all(),
        ]);
    });
// });

Route::get('/order-besar/register-nomor-po', [App\Http\Controllers\OrderBesar\RegisterNomorPoController::class, 'index'])->name('orderBesar.registerNomorPo');
Route::get('/order-besar/po-siap-verif', [App\Http\Controllers\OrderBesar\PoSiapVerifController::class, 'index'])->name('orderBesar.poSiapVerif');
Route::get('/order-besar/cetak-label',   [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'index'])->name('orderBesar.cetakLabel');


require __DIR__ . '/auth.php';
