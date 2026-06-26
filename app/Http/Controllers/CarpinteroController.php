<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Carpintero;
use Inertia\Inertia;

class CarpinteroController extends Controller
{
    public function index(Request $request)
    {
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
}
