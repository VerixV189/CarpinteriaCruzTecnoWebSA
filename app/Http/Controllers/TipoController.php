<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tipo;
use Inertia\Inertia;

class TipoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Tipo::latest();

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
        }

        return Inertia::render('Tipos/Index', [
            'tipos' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Tipos/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'estado' => 'required|string|in:activo,inactivo'
        ]);

        Tipo::create($request->all());

        return redirect()->route('tipos.index')->with('success', 'Tipo de mueble creado exitosamente.');
    }

    public function edit(Tipo $tipo)
    {
        return Inertia::render('Tipos/Edit', [
            'tipo' => $tipo
        ]);
    }

    public function update(Request $request, Tipo $tipo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'estado' => 'required|string|in:activo,inactivo'
        ]);

        $tipo->update($request->all());

        return redirect()->route('tipos.index')->with('success', 'Tipo de mueble actualizado exitosamente.');
    }

    public function destroy(Tipo $tipo)
    {
        // Avoid deleting if it has related products
        if ($tipo->productos()->count() > 0) {
            return redirect()->back()->withErrors(['error' => 'No se puede eliminar porque tiene productos asociados.']);
        }

        $tipo->delete();
        
        return redirect()->back()->with('success', 'Tipo de mueble eliminado exitosamente.');
    }
}
