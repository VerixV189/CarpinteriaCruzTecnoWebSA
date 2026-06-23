<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Cotizacion;
use Inertia\Inertia;

class CotizacionController extends Controller
{
    public function index()
    {
        return Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => Cotizacion::with(['cliente.usuario', 'carpintero.usuario'])->get()
        ]);
    }
}
