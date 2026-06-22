<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraBitacora;

class Pedido extends Model
{
    use RegistraBitacora;
    protected $fillable = [
        'cotizacion_id',
        'codigo',
        'precio',
        'fecha_entrega'
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function detallePedidos()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    public function venta()
    {
        return $this->hasOne(Venta::class, 'pedido_id');
    }
}
