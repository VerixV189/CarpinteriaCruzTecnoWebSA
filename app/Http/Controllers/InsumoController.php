<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Insumo;
use Inertia\Inertia;

class InsumoController extends Controller
{
    public function index()
    {
        return Inertia::render('Insumos/Index', [
            'insumos' => Insumo::with('proveedor')->get()
        ]);
    }
}
