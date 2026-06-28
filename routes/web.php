<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Webhooks de pasarelas (Públicos)
Route::post('pagos/callback', [App\Http\Controllers\PagoController::class, 'callbackPagoFacil'])->name('pagos.callback');

Route::get('dashboard', function () {
    $user = Illuminate\Support\Facades\Auth::user();
    // 1: Propietario, 3: Carpintero. Si es cliente (2) u otro, redirigir al marketplace
    if ($user && !in_array($user->rol_id, [1, 3])) {
        return redirect()->route('marketplace.index');
    }

    return Inertia::render('Dashboard', [
        'stats' => [
            'clientes' => App\Models\Cliente::count(),
            'productos' => App\Models\Producto::count(),
            'pedidos' => App\Models\Pedido::count(),
            'ingresos' => floatval(App\Models\Venta::sum('total_costo') ?? 0),
        ],
        'recursos_populares' => App\Models\Bitacora::select('modelo_tipo', Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->whereNotNull('modelo_tipo')
            ->where('modelo_tipo', '!=', 'Auth')
            ->groupBy('modelo_tipo')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
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
    Route::post('cotizaciones/{cotizacion}/detalles', [App\Http\Controllers\CotizacionController::class, 'storeDetalle'])->name('cotizaciones.detalles.store');
    Route::put('cotizaciones/{cotizacion}/estado', [App\Http\Controllers\CotizacionController::class, 'updateEstado'])->name('cotizaciones.estado.update');
    Route::resource('cotizaciones', App\Http\Controllers\CotizacionController::class)->parameters([
        'cotizaciones' => 'cotizacion'
    ]);
    Route::resource('pedidos', App\Http\Controllers\PedidoController::class);
    Route::resource('ventas', App\Http\Controllers\VentaController::class);
    
    // Marketplace / Carrito
    Route::get('marketplace', [App\Http\Controllers\MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('carrito', [App\Http\Controllers\MarketplaceController::class, 'cart'])->name('marketplace.cart');
    Route::post('carrito/agregar', [App\Http\Controllers\MarketplaceController::class, 'addToCart'])->name('marketplace.addToCart');
    Route::put('carrito/actualizar/{detalle}', [App\Http\Controllers\MarketplaceController::class, 'updateCart'])->name('marketplace.updateCart');
    Route::delete('carrito/eliminar/{detalle}', [App\Http\Controllers\MarketplaceController::class, 'removeFromCart'])->name('marketplace.removeFromCart');
    Route::post('carrito/checkout', [App\Http\Controllers\MarketplaceController::class, 'checkout'])->name('marketplace.checkout');

    // Rutas personalizadas de Pagos
    Route::put('pagos/{pago}/efectivo', [App\Http\Controllers\PagoController::class, 'marcarEfectivo'])->name('pagos.efectivo');
    Route::post('pagos/{pago}/pagofacil', [App\Http\Controllers\PagoController::class, 'iniciarPagoFacil'])->name('pagos.pagofacil');
    
    Route::get('pagos/return', [App\Http\Controllers\PagoController::class, 'returnPagoFacil'])->name('pagos.return');

    Route::resource('pagos', App\Http\Controllers\PagoController::class);
    Route::get('bitacoras', [App\Http\Controllers\BitacoraController::class, 'index'])->name('bitacoras.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
