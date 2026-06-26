<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Permiso;
use Inertia\Inertia;

class PermisoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = Permiso::latest();

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
        }

        return Inertia::render('Permisos/Index', [
            'permisos' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }
}
