<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pedido;
use Inertia\Inertia;

class PedidoController extends Controller
{
    public function index()
    {
        return Inertia::render('Pedidos/Index', [
            'pedidos' => Pedido::with('cotizacion.cliente.usuario')->get()
        ]);
    }
}
