<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Models\Carrito;
use App\Models\DetalleCarrito;
use App\Models\Cotizacion;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Venta;
use App\Models\Pago;

class MarketplaceController extends Controller
{
    private function getCliente()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Debes iniciar sesión para acceder al Marketplace.');
        }

        if ($user->rol_id !== 2 || !$user->cliente) {
            abort(403, 'Solo los clientes registrados pueden acceder al Marketplace.');
        }

        $cliente = \App\Models\Cliente::where('usuario_id', $user->id)->first();
        if (!$cliente) {
            $cliente = \App\Models\Cliente::create([
                'usuario_id' => $user->id,
                'nit_facturacion' => '123456789',
                'razon_social' => ($user->nombre ?? 'Usuario') . ' ' . ($user->apellido ?? 'Test') . ' (Simulado)',
                'direccion_envio' => 'Dirección de Prueba'
            ]);
        }
        return $cliente;
    }

    private function checkRestricted()
    {
        $user = Auth::user();
        if ($user && in_array($user->rol_id, [1, 3])) {
            return redirect()->route('dashboard');
        }
        return null;
    }

    public function index()
    {
        if ($redirect = $this->checkRestricted()) {
            return $redirect;
        }

        $user = Auth::user();
        $cliente = null;
        if ($user) {
            $cliente = $this->getCliente();
        }

        $productos = Producto::with(['imagenes', 'tipo', 'insumos'])
            ->whereIn('estado', ['disponible', 'activo', 'Disponible', 'Activo'])
            ->where('cantidad', '>', 0)
            ->get();
            
        $cartCount = 0;
        if ($cliente) {
            $carrito = Carrito::with('detalleCarritos')->firstOrCreate(
                ['cliente_id' => $cliente->id]
            );
            $cartCount = $carrito->detalleCarritos->sum('cantidad');
        }

        $tipos = \App\Models\Tipo::all();

        return Inertia::render('Marketplace/Index', [
            'productos' => $productos,
            'cartCount' => $cartCount,
            'tipos' => $tipos
        ]);
    }

    public function cart()
    {
        if ($redirect = $this->checkRestricted()) {
            return $redirect;
        }

        $cliente = $this->getCliente();

        $carrito = Carrito::with('detalleCarritos.producto.imagenes')->firstOrCreate(
            ['cliente_id' => $cliente->id]
        );

        return Inertia::render('Marketplace/CartCheckout', [
            'carrito' => $carrito
        ]);
    }

    public function addToCart(Request $request)
    {
        if ($redirect = $this->checkRestricted()) {
            return $redirect;
        }

        $cliente = $this->getCliente();

        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = Producto::findOrFail($request->producto_id);

        if ($producto->cantidad < $request->cantidad) {
            return redirect()->back()->withErrors(['cantidad' => 'No hay suficiente stock.']);
        }

        $carrito = Carrito::firstOrCreate(['cliente_id' => $cliente->id]);

        $detalle = $carrito->detalleCarritos()->where('producto_id', $producto->id)->first();

        if ($detalle) {
            $detalle->cantidad += $request->cantidad;
            $detalle->save();
        } else {
            $carrito->detalleCarritos()->create([
                'producto_id' => $producto->id,
                'cantidad' => $request->cantidad,
                'precio_unitario' => $producto->precio
            ]);
        }

        return redirect()->back()->with('success', 'Producto agregado al carrito.');
    }
    
    public function updateCart(Request $request, DetalleCarrito $detalle)
    {
        if ($redirect = $this->checkRestricted()) {
            return $redirect;
        }

        $cliente = $this->getCliente();
        if ($detalle->carrito->cliente_id !== $cliente->id) abort(403);

        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);
        
        $producto = $detalle->producto;
        if ($producto->cantidad < $request->cantidad) {
            return redirect()->back()->withErrors(['cantidad' => 'No hay suficiente stock para esa cantidad.']);
        }

        $detalle->update(['cantidad' => $request->cantidad]);

        return redirect()->back()->with('success', 'Carrito actualizado.');
    }

    public function removeFromCart(DetalleCarrito $detalle)
    {
        if ($redirect = $this->checkRestricted()) {
            return $redirect;
        }

        $cliente = $this->getCliente();
        if ($detalle->carrito->cliente_id !== $cliente->id) abort(403);

        $detalle->delete();

        return redirect()->back()->with('success', 'Producto eliminado del carrito.');
    }

    public function checkout(Request $request)
    {
        if ($redirect = $this->checkRestricted()) {
            return $redirect;
        }

        $cliente = $this->getCliente();

        $request->validate([
            'tipo_pago' => 'required|in:Contado,Crédito',
            'cuotas' => 'nullable|integer|min:1|max:12',
        ]);

        $carrito = Carrito::with('detalleCarritos.producto')->where('cliente_id', $cliente->id)->first();

        if (!$carrito || $carrito->detalleCarritos->isEmpty()) {
            return redirect()->back()->withErrors(['carrito' => 'El carrito está vacío.']);
        }

        DB::transaction(function () use ($cliente, $request, $carrito) {
            $total = 0;
            foreach ($carrito->detalleCarritos as $item) {
                $total += $item->cantidad * $item->precio_unitario;
            }

            // 1. Crear Pedido
            // ID para código (simulado o usando max ID)
            $nextId = Pedido::max('id') + 1;
            
            $pedido = Pedido::create([
                'cliente_id' => $cliente->id,
                'cotizacion_id' => null,
                'codigo' => 'PED-' . str_pad($nextId, 4, '0', STR_PAD_LEFT),
                'precio' => $total,
                'fecha_entrega' => now()->addDays(3), // Por defecto
            ]);

            // 3. Crear Detalle de Pedido por cada item y reducir stock
            foreach ($carrito->detalleCarritos as $item) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item->producto_id,
                    'cantidad' => $item->cantidad,
                    'precio' => $item->precio_unitario,
                    'estado' => 'Aprobado',
                    'descripcion' => 'Compra directa',
                ]);

                // Reducir stock del producto
                $producto = $item->producto;
                $producto->cantidad -= $item->cantidad;
                if ($producto->cantidad <= 0) {
                    $producto->estado = 'agotado';
                }
                $producto->save();
            }

            // 4. Crear Venta
            $venta = Venta::create([
                'pedido_id' => $pedido->id,
                'codigo' => 'VEN-' . str_pad($pedido->id, 4, '0', STR_PAD_LEFT),
                'total_costo' => $total,
                'fecha_entregado' => null,
                'tipo' => $request->tipo_pago,
            ]);

            // 5. Crear Pagos
            if ($request->tipo_pago === 'Crédito') {
                $cuotas = $request->cuotas ?: 1;
                $montoCuota = $total / $cuotas;
                for ($i = 1; $i <= $cuotas; $i++) {
                    Pago::create([
                        'venta_id' => $venta->id,
                        'subtotal' => $montoCuota,
                        'interes' => 0,
                        'estado' => 'Pendiente',
                        'fecha_vencimiento' => now()->addMonths($i),
                    ]);
                }
            } else {
                Pago::create([
                    'venta_id' => $venta->id,
                    'subtotal' => $total,
                    'interes' => 0,
                    'estado' => 'Pendiente',
                    'fecha_vencimiento' => now(), // Pago inmediato
                ]);
            }

            // 6. Vaciar carrito
            $carrito->detalleCarritos()->delete();
        });

        return redirect()->route('pedidos.index')->with('success', 'Compra realizada exitosamente. Puedes revisar tus pedidos aquí.');
    }
}
