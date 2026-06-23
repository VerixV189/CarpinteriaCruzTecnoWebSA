<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Carpintero;
use Inertia\Inertia;

class CarpinteroController extends Controller
{
    public function index()
    {
        return Inertia::render('Carpinteros/Index', [
            'carpinteros' => Carpintero::with('usuario')->get()
        ]);
    }
}
