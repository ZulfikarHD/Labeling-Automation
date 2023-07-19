<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\ProductMonitoringController;
use App\Http\Controllers\GeneratedProductsController;
use App\Http\Controllers\GeneratedLabelsController;

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

    // Pilih User
        // Kepala Meja
            Route::get('/np/chooseUser', function(){
                return Inertia::render('NonPerekat/ChoseUser');
            })->name('np.choseUser');

        // Verifikator
            Route::get('/np/generateLabels',[GeneratedLabelsController::class, 'index'])
                ->name('np.generateLabels.index');

    // Section Kepala Meja Non Perekat //
   //---------------------------------//
        // Menu Kepala Meja
            Route::get('/np/pic', function(){
                return Inertia::render('NonPerekat/KepalaMeja/Menu');
            })->name('np.pic');

        // Monitoring Barang
            Route::get('/np/pic/monitorProducts',[ProductMonitoringController::class, 'index'])
                ->name('np.monitor.index');
            Route::get('/np/pic/monitorProducts/{id}',[ProductMonitoringController::class, 'show'])
                ->name('np.monitor.show');

        // List Generated Product
            Route::resource('/np/listProducts',GeneratedProductsController::class,['names' => 'np.listProducts']);

        // Generate Product
            Route::get('/np/registerProducts/create',[GeneratedProductsController::class, 'create'])
                ->name('np.registerProducts.create');
            Route::post('/np/registerProducts',[GeneratedProductsController::class, 'store'])
                ->name('np.registerProducts.store');
            Route::post('/np/generateLabels',[GeneratedLabelsController::class, 'store'])
                ->name('np.generateLabels.store');

    // Section Verifikator Non Perekat //
   //---------------------------------//
        // List Generated Labels (Barang Yang Siap Periksa)
            Route::get('/np/generateLabels/{id}',[GeneratedLabelsController::class, 'show'])
                ->name('np.generateLabels.show');
            Route::post('/np/generateLabels/getRim',[GeneratedLabelsController::class, 'getRim'])
                ->name('np.generateLabels.getRim');

 // Group Perekat //
//---------------//

        // Generate Label
            Route::get('/p/generateLabels',[GeneratedLabelsController::class, 'indexMmea'])
                ->name('p.generateLabels');
            Route::post('/p/generateLabels/callSpec',[GeneratedLabelsController::class, 'callSpec'])
                ->name('p.generateLabels.callSpec');
            Route::post('/p/generateLabels/storeMmea',[GeneratedLabelsController::class, 'storeMmea'])
                ->name('p.generateLabels.storeMmea');

// Route::get('/np-choseUser', function () {
//     return Inertia::render('NonPerekat/ChoseUser');
// })->name('np-choseUser');

// Route::get('/np-kepala-menu', function () {
//     return Inertia::render('NonPerekat/KepalaMeja/Menu');
// })->name('np-kepala-menu');

// Route::get('/np/pic/monitor-products',[ProductMonitoringController::class, 'index'])->name('np.pic.monitor-products.index');
// Route::get('/np/pic/monitor-products/{monitor}',[ProductMonitoringController::class, 'show'])->name('np.pic.monitor-products.show');
// Route::resource('/np/pic/products'   ,GeneratedProductsController::class, ['names' => 'np.products']);
// Route::resource('/np/user/genLabel'  ,GeneratedLabelsController::class,   ['names' => 'np.genLabels']);
// Route::post('/np/user/genLabel/getRim',[GeneratedLabelsController::class,   'getRim'])->name('np.genLabels.getRim');

// Route::get('/np-verif-menu', function () {
//     return Inertia::render('NonPerekat/Verifikator/Menu');
// })->name('np-verif-menu');

// Route::get('/np-verif-genLabel', function () {
//     return Inertia::render('NonPerekat/Verifikator/GenerateLabel');
// })->name('np-verif-genLabel');


// // -------------- Perekat ------------------- //

// Route::get('/p-choseUser', function () {
//     return Inertia::render('Perekat/ChoseUser');
// })->name('p-choseUser');

// Route::get('/p-kepala-menu', function () {
//     return Inertia::render('NonPerekat/KepalaMeja/Menu');
// })->name('p-kepala-menu');

// Route::get('/p/pic/monitor-products',[ProductMonitoringController::class, 'index'])->name('p.pic.monitor-products.index');
// Route::get('/p/pic/monitor-products/{monitor}',[ProductMonitoringController::class, 'show'])->name('p.pic.monitor-products.show');
// Route::resource('/p/pic/products'   ,   GeneratedProductsController::class, ['names' => 'p.products']);
// Route::resource('/p/user/genLabel'  ,   GeneratedLabelsController::class,   ['names' => 'p.genLabels']);
// Route::post('/p/user/genLabel',         [GeneratedLabelsController::class,   'indexMmea'])->name('p.genLabels.index');
// Route::post('/p/user/genLabel',         [GeneratedLabelsController::class,   'indexMmea'])->name('p.genLabels.index');
// Route::post('/p/user/genLabel/callSpec',[GeneratedLabelsController::class,   'callSpec'])->name('p.genLabels.callSpec');
// Route::post('/p/user/genLabel/print',   [GeneratedLabelsController::class,   'storeMmea'])->name('p.genLabels.storeMmea');

// Route::get('/p-verif-menu', function () {
//     return Inertia::render('Perekat/Verifikator/Menu');
// })->name('p-verif-menu');

// Route::get('/p-verif-genLabel', function () {
//     return Inertia::render('Perekat/Verifikator/GenerateLabel');
// })->name('p-verif-genLabel');




require __DIR__.'/auth.php';
