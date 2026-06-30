<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserConfigController extends Controller
{
    /**
     * Update the user's appearance configuration.
     */
    public function updateAppearance(Request $request)
    {
        $request->validate([
            'appearance' => 'nullable|string',
            'theme' => 'nullable|string',
            'theme_color' => 'nullable|string',
            'font_size' => 'nullable|string',
            'contrast' => 'nullable|string',
            'auto_day_night' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        // Get existing config or empty array
        $currentConfig = $user->configuracion_tema ?? [];

        // Merge new config with existing
        $newConfig = array_merge($currentConfig, $request->only([
            'appearance',
            'theme',
            'theme_color',
            'font_size',
            'contrast',
            'auto_day_night',
        ]));

        // Update the user
        $user->update([
            'configuracion_tema' => $newConfig
        ]);

        return redirect()->back();
    }
}
