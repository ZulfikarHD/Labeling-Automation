<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProductMonitoringController;
use App\Http\Controllers\GenerateLabelsPersonalController;
use App\Http\Middleware\Role1Access;

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

// Route for login page
Route::get('/login', function () {
    return Inertia::render('Login');
})->name('login');

// Group routes that require authentication
Route::middleware('auth')->group(function () {

    // Dashboard route
    Route::get('/', [App\Http\Controllers\OrderBesar\PoSiapVerifController::class, 'index'])->name('dashboard');

    // Group routes that require Role1Access middleware
    Route::middleware(Role1Access::class)->group(function () {
        // User management routes
        Route::get('/create-user', [\App\Http\Controllers\UserManagement\CreateUserController::class, 'index'])->name('createUser.index');
        Route::post('/create-user', [\App\Http\Controllers\UserManagement\CreateUserController::class, 'store'])->name('createUser.store');
        Route::get('/change-password', [\App\Http\Controllers\UserManagement\PasswordController::class, 'changePassword'])->name('changePassword.index');
        Route::post('/change-password', [\App\Http\Controllers\UserManagement\PasswordController::class, 'update'])->name('changePassword.update');
    });

    // Order Besar routes
    Route::prefix('order-besar')->group(function () {
        Route::get('/po-siap-verif', [App\Http\Controllers\OrderBesar\PoSiapVerifController::class, 'index'])
            ->name('orderBesar.poSiapVerif');

        Route::get('/register-nomor-po', [App\Http\Controllers\OrderBesar\RegisterNomorPoController::class, 'index'])
            ->name('orderBesar.registerNomorPo');

        Route::get('/cetak-label/{team}/{id}', [App\Http\Controllers\OrderBesar\CetakLabelController::class, 'index'])
            ->name('orderBesar.cetakLabel');
    });

    // Order Kecil routes
    Route::get('/order-kecil/cetak-label', [App\Http\Controllers\OrderKecil\CetakLabelController::class, 'index'])->name('orderKecil.cetakLabel');

    // Data PO routes
    Route::get('/data-po/{team}', [\App\Http\Controllers\ProductionOrderController::class, 'index'])->name('dataPo.index');
    Route::get('/data-po/edit/{no_po}', [\App\Http\Controllers\ProductionOrderController::class, 'edit'])->name('dataPo.edit');
    Route::post('/data-po/{team}', [\App\Http\Controllers\ProductionOrderController::class, 'data_products'])->name('dataPo.filterTeam');
    Route::delete('/data-po/{no_po}', [\App\Http\Controllers\ProductionOrderController::class, 'destroy'])->name('dataPo.destroy');
    Route::get('/data-po/{team}/{no_po}', [\App\Http\Controllers\ProductionOrderController::class, 'show'])->name('dataPo.show');

    // Monitoring Produksi routes
    Route::get('/monitoring-produksi/status-verif', [App\Http\Controllers\MonitoringProduksi\StatusVerifikasiTeamController::class, 'index'])->name('monitoringProduksi.statusVerif.index');
    Route::get('/monitoring-produksi/status-verif/{id}', [App\Http\Controllers\MonitoringProduksi\StatusVerifikasiTeamController::class, 'show'])->name('monitoringProduksi.statusVerif.show');
    Route::get('/monitoring-produksi/produksi-pegawai', [App\Http\Controllers\MonitoringProduksi\ProduksiPegawaiController::class, 'index'])->name('monitoringProduksi.produksiPegawai');
});

// Include authentication routes
require __DIR__ . '/auth.php';
