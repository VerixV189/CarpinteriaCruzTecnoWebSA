<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'nombre_empresa',
        'telefono',
        'direccion'
    ];

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'proveedor_id');
    }
}
