<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pedido;
use Inertia\Inertia;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        
        $query = Pedido::with([
            'cliente.usuario',
            'cotizacion.cliente.usuario', 
            'detallePedidos.producto.imagenes',
            'venta.pagos'
        ])->latest();

        if ($user && $user->rol_id === 2) { // Cliente
            $query->where(function($q) use ($user) {
                $q->whereHas('cliente', function($qC) use ($user) {
                    $qC->where('usuario_id', $user->id);
                })->orWhereHas('cotizacion.cliente', function($qCot) use ($user) {
                    $qCot->where('usuario_id', $user->id);
                });
            });
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('estado', 'like', "%{$search}%")
                  ->orWhereHas('cliente.usuario', function($qUser) use ($search) {
                      $qUser->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                  })
                  ->orWhereHas('cotizacion.cliente.usuario', function($qUser) use ($search) {
                      $qUser->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                  });
            });
        }

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }
}
