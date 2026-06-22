<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraBitacora;

class Venta extends Model
{
    use RegistraBitacora;
    protected $fillable = [
        'pedido_id',
        'codigo',
        'total_costo',
        'fecha_entregado',
        'tipo'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'venta_id');
    }
}
