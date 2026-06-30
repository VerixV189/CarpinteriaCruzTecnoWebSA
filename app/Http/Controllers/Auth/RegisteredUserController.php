<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Bitacora;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'telefono' => 'required|string|max:255',
            ]);

            $user = User::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono,
                'estado' => "activo",
                'rol_id' => 2,
            ]);

            // Crear el registro del cliente asociado a este nuevo usuario
            Cliente::create([
                'usuario_id' => $user->id,
                'nit_facturacion' => $request->nit_facturacion ?? 'S/N',
                'razon_social' => $request->razon_social ?? 'S/N',
                'direccion_envio' => $request->direccion_envio ?? 'S/N',
            ]);

            // ¡OJO! No es necesario hacer un Bitacora::create() aquí para el éxito.
            // El Trait 'RegistraBitacora' que le pusimos al modelo User y Cliente 
            // ya detectó los ::create() de arriba y los guardó en la bitácora automáticamente.

            event(new Registered($user));

            Auth::login($user);

            return to_route('dashboard');

        } catch (ValidationException $e) {
            // Si el usuario se equivoca al llenar el formulario (contraseña corta, email repetido, etc.)
            Bitacora::create([
                'usuario_id' => null, // Es null porque el usuario aún no existe/no está logueado
                'accion' => 'registro_fallido_validacion',
                'modelo_tipo' => User::class,
                'modelo_id' => '0',
                'valores_nuevos' => $e->errors(), // Guardamos qué errores cometió
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            throw $e;

        } catch (\Exception $e) {
            // Si hay un error grave (ej. la base de datos se cayó)
            Bitacora::create([
                'usuario_id' => null,
                'accion' => 'registro_fallido_sistema',
                'modelo_tipo' => User::class,
                'modelo_id' => '0',
                'valores_nuevos' => ['error' => $e->getMessage()], // Guardamos el error de sistema
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            throw $e;
        }
    }
}
