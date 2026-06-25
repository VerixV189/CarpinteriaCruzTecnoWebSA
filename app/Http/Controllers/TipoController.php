<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tipo;
use Inertia\Inertia;

class TipoController extends Controller
{
    public function index()
    {
        return Inertia::render('Tipos/Index', [
            'tipos' => Tipo::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'estado' => 'required|string|in:activo,inactivo'
        ]);

        Tipo::create($request->all());

        return redirect()->back()->with('success', 'Tipo de mueble creado exitosamente.');
    }

    public function update(Request $request, Tipo $tipo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'estado' => 'required|string|in:activo,inactivo'
        ]);

        $tipo->update($request->all());

        return redirect()->back()->with('success', 'Tipo de mueble actualizado exitosamente.');
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
