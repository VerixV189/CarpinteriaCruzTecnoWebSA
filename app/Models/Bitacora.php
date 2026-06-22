<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacoras';

    protected $fillable = [
        'usuario_id',
        'accion',
        'modelo_tipo',
        'modelo_id',
        'valores_viejos',
        'valores_nuevos',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'valores_viejos' => 'array',
            'valores_nuevos' => 'array',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
