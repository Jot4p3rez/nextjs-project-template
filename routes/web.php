<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MercaderiaController;
use App\Http\Controllers\AlmacenamientoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\DespachoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// Authentication Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Mercadería Routes
    Route::resource('mercaderia', MercaderiaController::class);
    
    // Almacenamiento Routes
    Route::resource('almacenamiento', AlmacenamientoController::class);
    
    // Inventario Routes
    Route::resource('inventario', InventarioController::class);
    
    // Despacho Routes
    Route::resource('despacho', DespachoController::class);
});
