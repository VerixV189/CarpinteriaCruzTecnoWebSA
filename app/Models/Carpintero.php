<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carpintero extends Model
{
    protected $fillable = [
        'usuario_id',
        'especialidad',
        'costo_hora'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function detalleCotizaciones()
    {
        return $this->hasMany(DetalleCotizacion::class, 'carpintero_id');
    }
}
