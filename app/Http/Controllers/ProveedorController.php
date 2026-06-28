<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Proveedor;
use Inertia\Inertia;

class ProveedorController extends Controller
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
        $this->verificarPermiso('LISPROV');
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

    public function create()
    {
        $this->verificarPermiso('REGPROV');
        return Inertia::render('Proveedores/Create');
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('REGPROV');

        $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'telefono' => 'required|string|max:50',
            'direccion' => 'required|string|max:255',
        ]);

        Proveedor::create($request->only('nombre_empresa', 'telefono', 'direccion'));

        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado con éxito.');
    }

    public function edit(Proveedor $proveedor)
    {
        $this->verificarPermiso('ACTPROV');
        return Inertia::render('Proveedores/Edit', [
            'proveedor' => $proveedor
        ]);
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $this->verificarPermiso('ACTPROV');

        $request->validate([
            'nombre_empresa' => 'required|string|max:255',
            'telefono' => 'required|string|max:50',
            'direccion' => 'required|string|max:255',
        ]);

        $proveedor->update($request->only('nombre_empresa', 'telefono', 'direccion'));

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado con éxito.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $this->verificarPermiso('ELIMPROV');
        
        $proveedor->delete();
        
        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado con éxito.');
    }
}
