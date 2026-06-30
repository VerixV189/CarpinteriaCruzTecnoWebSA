<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use App\Models\Bitacora;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (Login $event) {
            Bitacora::create([
                'usuario_id' => $event->user->id,
                'accion' => 'Login exitoso',
                'modelo_tipo' => 'Auth',
                'modelo_id' => $event->user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        Event::listen(function (Failed $event) {
            $usuario_id = $event->user ? $event->user->id : null;
            Bitacora::create([
                'usuario_id' => $usuario_id,
                'accion' => 'Login fallido',
                'modelo_tipo' => 'Auth',
                'modelo_id' => $usuario_id ?? '0',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}
