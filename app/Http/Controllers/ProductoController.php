<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Producto;
use App\Models\Tipo;
use App\Models\Imagen;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Producto::with(['tipo', 'imagenes', 'insumos'])->latest();

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('tipo', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
        }

        return Inertia::render('Productos/Index', [
            'productos' => $query->paginate(10)->withQueryString(),
            'tipos' => Tipo::select('id', 'nombre')->get(),
            'insumos' => \App\Models\Insumo::select('id', 'nombre')->get(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_id' => 'required|exists:tipos,id',
            'nombre' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'required|string',
            'estado' => 'required|string',
            'imagen' => 'nullable|image|max:2048'
        ]);

        $producto = Producto::create($request->except('imagen'));

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('productos', 'public');
            Imagen::create([
                'producto_id' => $producto->id,
                'URL' => '/storage/' . $path
            ]);
        }

        return redirect()->back()->with('success', 'Producto creado exitosamente.');
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'tipo_id' => 'required|exists:tipos,id',
            'nombre' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'precio' => 'required|numeric|min:0',
            'descripcion' => 'required|string',
            'estado' => 'required|string',
            'imagen' => 'nullable|image|max:2048'
        ]);

        $producto->update($request->except('imagen'));

        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagenes()->exists()) {
                $oldImage = $producto->imagenes->first();
                $oldPath = str_replace('/storage/', '', $oldImage->URL);
                Storage::disk('public')->delete($oldPath);
                $oldImage->delete();
            }

            $path = $request->file('imagen')->store('productos', 'public');
            Imagen::create([
                'producto_id' => $producto->id,
                'URL' => '/storage/' . $path
            ]);
        }

        return redirect()->back()->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Producto $producto)
    {
        // Delete related images from storage
        foreach ($producto->imagenes as $imagen) {
            $path = str_replace('/storage/', '', $imagen->URL);
            Storage::disk('public')->delete($path);
        }
        $producto->delete();
        
        return redirect()->back()->with('success', 'Producto eliminado exitosamente.');
    }

    public function updateInsumos(Request $request, Producto $producto)
    {
        $request->validate([
            'insumos' => 'array',
            'insumos.*' => 'exists:insumos,id'
        ]);

        $producto->insumos()->sync($request->input('insumos', []));

        return redirect()->back()->with('success', 'Insumos asociados exitosamente al producto.');
    }
}
