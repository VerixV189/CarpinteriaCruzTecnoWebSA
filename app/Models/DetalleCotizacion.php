<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCotizacion extends Model
{
    protected $table = 'detalle_cotizaciones';

    protected $fillable = [
        'cotizacion_id',
        'carpintero_id',
        'precio',
        'descripcion'
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function carpintero()
    {
        return $this->belongsTo(Carpintero::class, 'carpintero_id');
    }
}
