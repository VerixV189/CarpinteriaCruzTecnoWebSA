<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    /**
     * Función privada para comprobar si el usuario autenticado tiene el permiso.
     */
    private function verificarPermiso($nombrePermiso)
    {
        $user = Auth::user();
        if (!$user || !$user->rol) {
            abort(403, 'No tienes un rol asignado.');
        }

        // Si el rol_id 1 es Propietario, le damos acceso total siempre
        if ($user->rol_id === 1) {
            return true;
        }

        // Verificamos si en la tabla pivote este rol tiene el permiso solicitado
        $tienePermiso = $user->rol->permisos->contains('nombre', $nombrePermiso);
        if (!$tienePermiso) {
            abort(403, 'Acceso Denegado: No tienes el permiso necesario (' . $nombrePermiso . ').');
        }
    }

    public function index(Request $request)
    {
        $this->verificarPermiso('LISUSU');
        $search = $request->input('search');

        $query = User::with('rol')->latest();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('rol', function($qRol) use ($search) {
                      $qRol->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        // 1. Verificamos permiso
        // $this->verificarPermiso('REGUSU');

        // Traemos todos los roles de la base de datos para pasarlos al select del formulario
        $roles = \App\Models\Rol::all();

        // En Inertia es común usar 'Store' o 'Create' como nombre de archivo para el formulario
        return Inertia::render('Usuarios/Store', [
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $this->verificarPermiso('REGUSU');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', Rules\Password::defaults()],
            'telefono' => 'required|string|max:255',
            'rol_id' => 'required|exists:roles,id',
            'estado' => 'required|string',
        ]);

        // Al usar User::create, el Trait 'RegistraBitacora' hará el log automáticamente
        User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'rol_id' => $request->rol_id,
            'estado' => $request->estado,
        ]);

        return redirect()->back()->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $usuario)
    {
        $this->verificarPermiso('ACTUSU');

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            // Ignoramos el email del usuario actual para la validación unique
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class.',email,'.$usuario->id,
            'telefono' => 'required|string|max:255',
            'rol_id' => 'required|exists:roles,id',
            'estado' => 'required|string',
        ]);

        $data = $request->only('nombre', 'apellido', 'email', 'telefono', 'rol_id', 'estado');
        
        // Si mandaron una nueva contraseña, la encriptamos y la actualizamos
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Al usar update(), el Trait 'RegistraBitacora' comparará qué cambió y lo guardará
        $usuario->update($data);

        return redirect()->back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        $this->verificarPermiso('ELIMUSU');

        // Al usar delete(), el Trait 'RegistraBitacora' guardará el registro eliminado
        $usuario->delete();

        return redirect()->back()->with('success', 'Usuario eliminado correctamente.');
    }
}
