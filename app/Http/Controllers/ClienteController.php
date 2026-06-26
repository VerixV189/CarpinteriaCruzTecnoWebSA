<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\Cliente;
use App\Models\User;
use Inertia\Inertia;

class ClienteController extends Controller
{
        private function verificarPermiso($nombrePermiso)
    {
        $user = Auth::user();
        if (!$user || !$user->rol) {
            abort(403, 'No tienes un rol asignado.');
        }

        // Si el rol_id 1 es Propietario, le damos acceso total siempre
        if ($user->rol_id === 1) {
            return true;
        }

        // Verificamos si en la tabla pivote este rol tiene el permiso solicitado
        $tienePermiso = $user->rol->permisos->contains('nombre', $nombrePermiso);
        if (!$tienePermiso) {
            abort(403, 'Acceso Denegado: No tienes el permiso necesario (' . $nombrePermiso . ').');
        }
    }

    public function index(Request $request)
    {
        $this->verificarPermiso('LISCLI');
        $search = $request->input('search');

        $query = Cliente::with('usuario')->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nit_facturacion', 'like', "%{$search}%")
                  ->orWhere('razon_social', 'like', "%{$search}%")
                  ->orWhereHas('usuario', function($qUser) use ($search) {
                      $qUser->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $clientes = $query->paginate(10)->withQueryString();

        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        $this->verificarPermiso('REGCLI');

        return Inertia::render('Clientes/Create');
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('REGCLI');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios',
            'telefono' => 'nullable|string|max:20',
            'nit_facturacion' => 'required|string|max:50',
            'razon_social' => 'required|string|max:255',
            'direccion_envio' => 'required|string|max:255',
        ]);

        $user = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => bcrypt('password123'),
        ]);

        $user->cliente()->create([
            'nit_facturacion' => $request->nit_facturacion,
            'razon_social' => $request->razon_social,
            'direccion_envio' => $request->direccion_envio,
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente creado con éxito.');
    }

    public function edit(Cliente $cliente)
    {
        $this->verificarPermiso('ACTCLI');

        return Inertia::render('Clientes/Edit', [
            'cliente' => $cliente->load('usuario')
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $this->verificarPermiso('ACTCLI');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios,email,' . $cliente->usuario_id,
            'telefono' => 'nullable|string|max:20',
            'nit_facturacion' => 'required|string|max:50',
            'razon_social' => 'required|string|max:255',
            'direccion_envio' => 'required|string|max:255',
        ]);

        $cliente->usuario->update([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'telefono' => $request->telefono,
        ]);

        $cliente->update([
            'nit_facturacion' => $request->nit_facturacion,
            'razon_social' => $request->razon_social,
            'direccion_envio' => $request->direccion_envio,
        ]);

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado con éxito.');
    }

    public function destroy(Cliente $cliente)
    {
        $this->verificarPermiso('ELIMCLI');

        $cliente->usuario->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado con éxito.');
    }
}
