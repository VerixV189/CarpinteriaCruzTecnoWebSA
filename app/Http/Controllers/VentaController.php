<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Venta;
use Inertia\Inertia;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = Venta::with([
            'pedido.cliente.usuario',
            'pedido.cotizacion.cliente.usuario',
            'pagos'
        ])->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('tipo', 'like', "%{$search}%")
                  ->orWhereHas('pedido.cliente.usuario', function($qUser) use ($search) {
                      $qUser->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                  })
                  ->orWhereHas('pedido.cotizacion.cliente.usuario', function($qUser) use ($search) {
                      $qUser->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                  });
            });
        }

        return Inertia::render('Ventas/Index', [
            'ventas' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        
    }
}
