<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property float $subtotal
 * @property string $estado
 */
class Pago extends Model
{
    protected $fillable = [
        'venta_id',
        'subtotal',
        'interes',
        'estado',
        'fecha_vencimiento'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
