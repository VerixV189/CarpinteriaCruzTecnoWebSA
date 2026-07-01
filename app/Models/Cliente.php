<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $usuario_id
 */
class Cliente extends Model
{
    protected $fillable = [
        'usuario_id',
        'nit_facturacion',
        'razon_social',
        'direccion_envio'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class, 'cliente_id');
    }

    public function carrito()
    {
        return $this->hasOne(Carrito::class, 'cliente_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }
}
