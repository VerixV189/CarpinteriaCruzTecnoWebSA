<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Permiso;

use App\Models\Rol;
use Inertia\Inertia;

class RolController extends Controller
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
        $this->verificarPermiso('LISROL');
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

    public function show(Rol $role)
    {
        $this->verificarPermiso('LISROL');
        $role->load('permisos');
        
        return Inertia::render('Roles/Show', [
            'role' => $role
        ]);
    }

    public function create()
    {
        $this->verificarPermiso('REGROL');
        return Inertia::render('Roles/Create', [
            'permisos_disponibles' => Permiso::orderBy('nombre')->get()
        ]);
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('REGROL');

        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        $rol = Rol::create([
            'nombre' => $request->nombre,
            'estado' => 1
        ]);

        if ($request->has('permisos')) {
            $rol->permisos()->sync($request->permisos);
        }

        return redirect()->route('roles.index')->with('success', 'Rol registrado con éxito.');
    }

    public function edit(Rol $role)
    {
        $this->verificarPermiso('ACTROL');
        $role->load('permisos');
        
        return Inertia::render('Roles/Edit', [
            'role' => $role,
            'permisos_disponibles' => Permiso::orderBy('nombre')->get()
        ]);
    }

    public function update(Request $request, Rol $role)
    {
        $this->verificarPermiso('ACTROL');

        $request->validate([
            'nombre' => 'required|string|max:255|unique:roles,nombre,' . $role->id,
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permisos,id',
        ]);

        $role->update([
            'nombre' => $request->nombre
        ]);

        if ($request->has('permisos')) {
            $role->permisos()->sync($request->permisos);
        } else {
            $role->permisos()->detach();
        }

        return redirect()->route('roles.index')->with('success', 'Rol actualizado con éxito.');
    }

    public function destroy(Rol $role)
    {
        $this->verificarPermiso('ELIMROL');
        
        // Evitar eliminar el rol de administrador (asumiendo id 1 es admin)
        if ($role->id === 1) {
            return back()->with('error', 'No puedes eliminar el rol de Administrador.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado con éxito.');
    }
}
