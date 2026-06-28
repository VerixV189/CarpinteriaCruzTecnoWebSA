<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Permiso;
use Inertia\Inertia;

class PermisoController extends Controller
{
    private function verificarPermiso($nombrePermiso)
    {
        $user = Auth::user();
        if (!$user || !$user->rol) abort(403, 'No tienes un rol asignado.');
        if ($user->rol_id === 1) return true;
        if (!$user->rol->permisos->contains('nombre', $nombrePermiso)) {
            abort(403, 'Acceso Denegado.');
        }
    }

    public function index(Request $request)
    {
        $this->verificarPermiso('LISPERM');
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

    public function create()
    {
        $this->verificarPermiso('REGPERM');
        return Inertia::render('Permisos/Create');
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('REGPERM');

        $request->validate([
            'nombre' => 'required|string|max:255|unique:permisos,nombre',
            'descripcion' => 'required|string|max:255',
        ]);

        Permiso::create($request->only(['nombre', 'descripcion']));

        return redirect()->route('permisos.index')->with('success', 'Permiso registrado con éxito.');
    }

    public function edit(Permiso $permiso)
    {
        $this->verificarPermiso('ACTPERM');
        return Inertia::render('Permisos/Edit', [
            'permiso' => $permiso
        ]);
    }

    public function update(Request $request, Permiso $permiso)
    {
        $this->verificarPermiso('ACTPERM');

        $request->validate([
            'nombre' => 'required|string|max:255|unique:permisos,nombre,' . $permiso->id,
            'descripcion' => 'required|string|max:255',
        ]);

        $permiso->update($request->only(['nombre', 'descripcion']));

        return redirect()->route('permisos.index')->with('success', 'Permiso actualizado con éxito.');
    }

    public function destroy(Permiso $permiso)
    {
        $this->verificarPermiso('ELIMPERM');
        $permiso->delete();
        return redirect()->route('permisos.index')->with('success', 'Permiso eliminado con éxito.');
    }
}
