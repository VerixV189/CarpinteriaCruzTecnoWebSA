<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraBitacora;

class Producto extends Model
{
    use RegistraBitacora;
    protected $fillable = [
        'tipo_id',
        'nombre',
        'cantidad',
        'precio',
        'descripcion',
        'estado'
    ];

    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'tipo_id');
    }

    public function imagenes()
    {
        return $this->hasMany(Imagen::class, 'producto_id');
    }

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'insumo_producto', 'producto_id', 'insumo_id');
    }

    public function detallePedidos()
    {
        return $this->hasMany(DetallePedido::class, 'producto_id');
    }
}
