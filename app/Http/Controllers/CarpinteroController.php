<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Carpintero;
use Inertia\Inertia;

class CarpinteroController extends Controller
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
        $this->verificarPermiso('LISCAR');
        $search = $request->input('search');
        $query = Carpintero::with('usuario')->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('especialidad', 'like', "%{$search}%")
                  ->orWhereHas('usuario', function($qUser) use ($search) {
                      $qUser->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                  });
            });
        }

        return Inertia::render('Carpinteros/Index', [
            'carpinteros' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        $this->verificarPermiso('REGCAR');
        return Inertia::render('Carpinteros/Create');
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('REGCAR');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'telefono' => 'nullable|string|max:20',
            'especialidad' => 'required|string|max:255',
            'costo_hora' => 'required|numeric|min:0',
        ]);

        $user = \App\Models\User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => bcrypt('password123'),
            'rol_id' => 3, // Rol de Carpintero
            'estado' => 'Activo',
        ]);

        $user->carpintero()->create([
            'especialidad' => $request->especialidad,
            'costo_hora' => $request->costo_hora,
        ]);

        return redirect()->route('carpinteros.index')->with('success', 'Carpintero registrado con éxito.');
    }

    public function edit(Carpintero $carpintero)
    {
        $this->verificarPermiso('ACTCAR');
        return Inertia::render('Carpinteros/Edit', [
            'carpintero' => $carpintero->load('usuario')
        ]);
    }

    public function update(Request $request, Carpintero $carpintero)
    {
        $this->verificarPermiso('ACTCAR');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $carpintero->usuario_id,
            'telefono' => 'nullable|string|max:20',
            'especialidad' => 'required|string|max:255',
            'costo_hora' => 'required|numeric|min:0',
        ]);

        $carpintero->usuario->update([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'telefono' => $request->telefono,
        ]);

        $carpintero->update([
            'especialidad' => $request->especialidad,
            'costo_hora' => $request->costo_hora,
        ]);

        return redirect()->route('carpinteros.index')->with('success', 'Datos de carpintero actualizados con éxito.');
    }

    public function destroy(Carpintero $carpintero)
    {
        $this->verificarPermiso('ELIMCAR');

        // Delete user deletes cascade the carpintero profile depending on DB constraints,
        // or we manually delete both.
        $carpintero->usuario->delete();
        
        return redirect()->route('carpinteros.index')->with('success', 'Carpintero eliminado con éxito.');
    }
}
