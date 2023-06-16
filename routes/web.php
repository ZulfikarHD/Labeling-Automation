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

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/np-choseUser', function () {
    return Inertia::render('NonPerekat/ChoseUser');
})->name('np-choseUser');

Route::get('/np-kepala-menu', function () {
    return Inertia::render('NonPerekat/KepalaMeja/Menu');
})->name('np-kepala-menu');

Route::get('/np/pic/monitor-products',[ProductMonitoringController::class, 'index'])->name('np.pic.monitor-products');
Route::resource('/np/pic/products'   ,GeneratedProductsController::class, ['names' => 'np.products']);
Route::resource('/np/user/genLabel'  ,GeneratedLabelsController::class,   ['names' => 'np.genLabels']);
Route::post('/np/user/genLabel/getRim',[GeneratedLabelsController::class,   'getRim'])->name('np.genLabels.getRim');

Route::get('/np-verif-menu', function () {
    return Inertia::render('NonPerekat/Verifikator/Menu');
})->name('np-verif-menu');

Route::get('/np-verif-genLabel', function () {
    return Inertia::render('NonPerekat/Verifikator/GenerateLabel');
})->name('np-verif-genLabel');


require __DIR__.'/auth.php';
