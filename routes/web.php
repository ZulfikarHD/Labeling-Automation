<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/np-kepala-monitor', function () {
    return Inertia::render('NonPerekat/KepalaMeja/Monitor');
})->name('np-kepala-monitor');

Route::get('/np-kepala-genOrder', function () {
    return Inertia::render('NonPerekat/KepalaMeja/GeneratedOrders');
})->name('np-kepala-genOrder');

Route::get('/np-kepala-newGen', function () {
    return Inertia::render('NonPerekat/KepalaMeja/NewGenerate');
})->name('np-kepala-newGen');

require __DIR__.'/auth.php';
