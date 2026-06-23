<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BitacoraController extends Controller
{
    public function index()
    {
        $bitacoras = Bitacora::with('usuario')->orderBy('created_at', 'desc')->get();
        return Inertia::render('Bitacoras/Index', [
            'bitacoras' => $bitacoras
        ]);
    }
}
