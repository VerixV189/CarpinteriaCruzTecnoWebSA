<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Permiso;
use Inertia\Inertia;

class PermisoController extends Controller
{
    public function index()
    {
        return Inertia::render('Permisos/Index', [
            'permisos' => Permiso::all()
        ]);
    }
}
