<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraBitacora;

/**
 * @property int $id
 * @property int $cliente_id
 * @property string $estado
 */
class Cotizacion extends Model
{
    use RegistraBitacora;
    protected $table = 'cotizaciones';

    protected $fillable = [
        'cliente_id',
        'descripcion',
        'estado'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalleCotizaciones()
    {
        return $this->hasMany(DetalleCotizacion::class, 'cotizacion_id');
    }

    public function pedido()
    {
        return $this->hasOne(Pedido::class, 'cotizacion_id');
    }

    public function imagenes()
    {
        return $this->hasMany(Imagen::class, 'cotizacion_id');
    }
}
