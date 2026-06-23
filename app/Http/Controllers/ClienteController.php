<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Cliente;
use App\Models\User;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::with('usuario')->get();
        return Inertia::render('Clientes/Index', [
            'clientes' => $clientes
        ]);
    }

    public function create()
    {
        return Inertia::render('Clientes/Create');
    }

    public function store(Request $request)
    {
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
        return Inertia::render('Clientes/Edit', [
            'cliente' => $cliente->load('usuario')
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
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
        $cliente->usuario->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado con éxito.');
    }
}
