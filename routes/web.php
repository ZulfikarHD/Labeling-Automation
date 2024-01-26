<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProductMonitoringController;
use App\Http\Controllers\GeneratedProductsController;
use App\Http\Controllers\GenerateLabelsPersonalController;
use App\Http\Controllers\GeneratedLabelsController;
use App\Http\Controllers\GenerateLabelsController;
use App\Http\Controllers\PrintLabelController;

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
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

 // Group Non Perekat //
//-------------------//

    //Pilih Personal atau Non Pesonal
        Route::get('/non-perekat', function(){
            return Inertia::render('NonPerekat/IndexNonPerekat');
        })->name('nonPer.index');

     // Section Non Perekat, Personal //
    //-------------------------------//
        Route::get('/non-perekat/personal',[GenerateLabelsPersonalController::class, 'index'])
            ->name('nonPer.personal');
     // Section Non Perekat, Non Personal //
    //-----------------------------------//
        // Pilih User
            // Kepala Meja
                Route::get('/non-perekat/non-personal', function(){
                    return Inertia::render('NonPerekat/NonPersonal/Index');
                })->name('nonPer.nonPersonal.index');

            // Verifikator
                Route::get('/non-perekat/non-personal/verif',[GeneratedLabelsController::class, 'index'])->name('nonPer.nonPersonal.verif.index');

         // Section Kepala Meja Non Perekat //
        //---------------------------------//
            // Menu Kepala Meja
                Route::get('/non-perekat/non-personal/pic', function(){
                    return Inertia::render('NonPerekat/NonPersonal/Pic/Menu');
                })->name('nonPer.nonPersonal.pic.index');

            // Monitoring Barang
                Route::get('/non-perekat/non-personal/pic/monitorVerifikasi',[ProductMonitoringController::class, 'index'])->name('nonPer.nonPersonal.monitor.index');
                Route::get('/non-perekat/non-personal/pic/monitorVerifikasi/{id}',[ProductMonitoringController::class, 'show'])->name('nonPer.nonPersonal.monitor.show');

            // List Generated Product
                Route::get('/non-perekat/non-personal/pic/listPo/{team}',[GeneratedProductsController::class, 'index'])->name('nonPer.nonPersonal.listPo.index');
                Route::post('/non-perekat/non-personal/pic/listPo/{team}',[GeneratedProductsController::class, 'data_products']);
                Route::resource('/non-perekat/non-personal/pic/listPo',GeneratedProductsController::class,['names' => 'nonPer.nonPersonal.listPo'])->except(['index']);

            // Generate Product
                Route::get('/non-perekat/non-personal/generateLabels', [GenerateLabelsController::class, 'index'])->name('nonPer.nonPersonal.generateLabels.index');
                // Route::post('/non-perekat/non-personal/generateLabels',[GenerateLabelsController::class, 'store'])->name('nonPer.nonPersonal.generateLabels.store');

    // Section Verifikator Non Perekat //
   //---------------------------------//
        // List Generated Labels (Barang Yang Siap Periksa)
            Route::get('/non-perekat/non-personal/print-label/{workstation}/{id}', [PrintLabelController::class, 'index'])
                ->name('nonPer.nonPersonal.printLabel.index');

 // Group Perekat //
//---------------//

        // Generate Label
            Route::get('/p/generateLabels',[GeneratedLabelsController::class, 'indexMmea'])->name('p.generateLabels');
            Route::post('/p/generateLabels/callSpec', [GeneratedLabelsController::class, 'callSpec'])->name('p.generateLabels.callSpec');
            Route::post('/p/generateLabels/storeMmea',[GeneratedLabelsController::class, 'storeMmea'])->name('p.generateLabels.storeMmea');





require __DIR__.'/auth.php';
