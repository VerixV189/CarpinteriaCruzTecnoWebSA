<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Venta;
use Inertia\Inertia;

class VentaController extends Controller
{
    public function index()
    {
        return Inertia::render('Ventas/Index', [
            'ventas' => Venta::with(['pedido.cotizacion.cliente.usuario', 'pagos'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        
    }
}
