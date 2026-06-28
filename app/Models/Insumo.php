<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraBitacora;

class Insumo extends Model
{
    use HasFactory, RegistraBitacora;

    protected $fillable = [
        'proveedor_id',
        'nombre'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'insumo_producto', 'insumo_id', 'producto_id');
    }
}
