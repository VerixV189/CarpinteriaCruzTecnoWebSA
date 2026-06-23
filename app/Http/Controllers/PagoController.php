<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pago;
use Inertia\Inertia;

class PagoController extends Controller
{
    public function index()
    {
        return Inertia::render('Pagos/Index', [
            'pagos' => Pago::with('venta.pedido.cotizacion.cliente.usuario')->get()
        ]);
    }
}
