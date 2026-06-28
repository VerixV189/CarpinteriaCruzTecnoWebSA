<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Insumo;
use App\Models\Proveedor;
use Inertia\Inertia;

class InsumoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Insumo::with('proveedor')->latest();

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhereHas('proveedor', function($q) use ($search) {
                      $q->where('nombre_empresa', 'like', "%{$search}%");
                  });
        }

        return Inertia::render('Insumos/Index', [
            'insumos' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Insumos/Create', [
            'proveedores' => Proveedor::select('id', 'nombre_empresa')->orderBy('nombre_empresa')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'proveedor_id' => 'required|exists:proveedores,id'
        ]);

        Insumo::create($validated);

        return redirect()->route('insumos.index')->with('success', 'Insumo registrado con éxito.');
    }

    public function edit(Insumo $insumo)
    {
        return Inertia::render('Insumos/Edit', [
            'insumo' => $insumo,
            'proveedores' => Proveedor::select('id', 'nombre_empresa')->orderBy('nombre_empresa')->get()
        ]);
    }

    public function update(Request $request, Insumo $insumo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'proveedor_id' => 'required|exists:proveedores,id'
        ]);

        $insumo->update($validated);

        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado con éxito.');
    }

    public function destroy(Insumo $insumo)
    {
        $insumo->delete();

        return redirect()->route('insumos.index')->with('success', 'Insumo eliminado con éxito.');
    }
}
