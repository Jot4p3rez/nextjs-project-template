<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MercaderiaController;
use App\Http\Controllers\Api\AlmacenamientoController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\DespachoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Mercadería routes
    Route::apiResource('mercaderias', MercaderiaController::class);
    Route::get('mercaderias/proveedor/{proveedor}', [MercaderiaController::class, 'byProveedor']);
    Route::get('mercaderias/fecha/{fecha}', [MercaderiaController::class, 'byFecha']);

    // Almacenamiento routes
    Route::apiResource('almacenamientos', AlmacenamientoController::class);
    Route::get('almacenamientos/disponibles', [AlmacenamientoController::class, 'disponibles']);
    Route::get('almacenamientos/tipo/{tipo}', [AlmacenamientoController::class, 'byTipo']);

    // Inventario routes
    Route::apiResource('inventarios', InventarioController::class);
    Route::get('inventarios/stock-bajo', [InventarioController::class, 'stockBajo']);
    Route::get('inventarios/categoria/{categoria}', [InventarioController::class, 'byCategoria']);
    Route::get('inventarios/movimientos/{id}', [InventarioController::class, 'movimientos']);
    Route::get('inventarios/reportes', [InventarioController::class, 'reportes']);

    // Despacho routes
    Route::apiResource('despachos', DespachoController::class);
    Route::get('despachos/pendientes', [DespachoController::class, 'pendientes']);
    Route::get('despachos/cliente/{cliente}', [DespachoController::class, 'byCliente']);
    Route::patch('despachos/{despacho}/estado', [DespachoController::class, 'updateEstado']);
    Route::get('despachos/programados/hoy', [DespachoController::class, 'programadosHoy']);
    Route::get('despachos/retrasados', [DespachoController::class, 'retrasados']);

    // Dashboard statistics
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/actividades-recientes', [DashboardController::class, 'actividadesRecientes']);
    Route::get('/dashboard/tareas-pendientes', [DashboardController::class, 'tareasPendientes']);
});

// Fallback route for undefined API routes
Route::fallback(function () {
    return response()->json([
        'message' => 'Ruta no encontrada. Por favor verifique la URL.',
    ], 404);
});
