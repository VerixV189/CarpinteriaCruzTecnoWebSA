<?php

namespace App\Traits;

use App\Models\Bitacora;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait RegistraBitacora
{
    /**
     * El framework Laravel buscará automáticamente métodos que empiecen con "boot"
     * seguido del nombre del Trait y los ejecutará al iniciar el modelo.
     */
    public static function bootRegistraBitacora()
    {
        static::created(function ($model) {
            self::registrarAuditoria('crear', $model);
        });

        static::updated(function ($model) {
            self::registrarAuditoria('actualizar', $model);
        });

        static::deleted(function ($model) {
            self::registrarAuditoria('eliminar', $model);
        });
    }

    protected static function registrarAuditoria($accion, $model)
    {
        $valoresViejos = null;
        $valoresNuevos = null;

        if ($accion === 'crear') {
            $valoresNuevos = $model->getAttributes();
        } elseif ($accion === 'actualizar') {
            $valoresViejos = array_intersect_key($model->getOriginal(), $model->getChanges());
            $valoresNuevos = $model->getChanges();
        } elseif ($accion === 'eliminar') {
            $valoresViejos = $model->getAttributes();
        }

        Bitacora::create([
            'usuario_id' => Auth::id(), // Guardará el ID del usuario logueado, o null si no hay sesión
            'accion' => $accion,
            'modelo_tipo' => get_class($model),
            'modelo_id' => $model->id ?? '0', // Por si algún modelo no usa ID autoincremental
            'valores_viejos' => $valoresViejos,
            'valores_nuevos' => $valoresNuevos,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
