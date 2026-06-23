<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard', [
        'stats' => [
            'clientes' => App\Models\Cliente::count(),
            'productos' => App\Models\Producto::count(),
            'pedidos' => App\Models\Pedido::count(),
            'ingresos' => floatval(App\Models\Venta::sum('total_costo') ?? 0),
        ]
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('clientes', App\Http\Controllers\ClienteController::class);
    Route::resource('usuarios', App\Http\Controllers\UsuarioController::class);
    Route::resource('roles', App\Http\Controllers\RolController::class);
    Route::resource('permisos', App\Http\Controllers\PermisoController::class);
    Route::resource('carpinteros', App\Http\Controllers\CarpinteroController::class);
    Route::resource('insumos', App\Http\Controllers\InsumoController::class);
    Route::resource('productos', App\Http\Controllers\ProductoController::class);
    Route::resource('proveedores', App\Http\Controllers\ProveedorController::class);
    Route::resource('tipos', App\Http\Controllers\TipoController::class);
    Route::resource('cotizaciones', App\Http\Controllers\CotizacionController::class);
    Route::resource('pedidos', App\Http\Controllers\PedidoController::class);
    Route::resource('ventas', App\Http\Controllers\VentaController::class);
    Route::resource('pagos', App\Http\Controllers\PagoController::class);
    Route::get('bitacoras', [App\Http\Controllers\BitacoraController::class, 'index'])->name('bitacoras.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
