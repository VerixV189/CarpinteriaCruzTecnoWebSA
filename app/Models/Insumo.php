<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
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
