<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraBitacora;

class Proveedor extends Model
{
    use HasFactory, RegistraBitacora;

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
