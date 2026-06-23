<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Proveedor;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function index()
    {
        return Inertia::render('Proveedores/Index', [
            'proveedores' => Proveedor::all()
        ]);
    }
}
