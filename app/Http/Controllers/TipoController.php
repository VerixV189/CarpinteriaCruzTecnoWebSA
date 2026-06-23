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
            'tipos' => Tipo::all()
        ]);
    }
}
