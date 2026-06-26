<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Proveedor;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Proveedor::latest();

        if ($search) {
            $query->where('nombre_empresa', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%");
        }

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }
}
