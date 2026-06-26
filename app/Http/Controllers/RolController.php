<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Rol;
use Inertia\Inertia;

class RolController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = Rol::latest();

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        return Inertia::render('Roles/Index', [
            'roles' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }
}
