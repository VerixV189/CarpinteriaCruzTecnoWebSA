<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('rol')->get();
        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios
        ]);
    }
}
