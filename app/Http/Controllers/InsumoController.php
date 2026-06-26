<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Insumo;
use Inertia\Inertia;

class InsumoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Insumo::with('proveedor')->latest();

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('unidad_medida', 'like', "%{$search}%")
                  ->orWhereHas('proveedor', function($q) use ($search) {
                      $q->where('nombre_empresa', 'like', "%{$search}%");
                  });
        }

        return Inertia::render('Insumos/Index', [
            'insumos' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }
}
