<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Bitacora::with('usuario')->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('accion', 'like', "%{$search}%")
                  ->orWhere('modelo_tipo', 'like', "%{$search}%")
                  ->orWhereHas('usuario', function($qUser) use ($search) {
                      $qUser->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                  });
            });
        }

        $bitacoras = $query->paginate(10)->withQueryString();

        return Inertia::render('Bitacoras/Index', [
            'bitacoras' => $bitacoras,
            'filters' => $request->only(['search'])
        ]);
    }
}
