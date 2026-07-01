<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Imagen extends Model
{
    protected $table = 'imagenes';

    protected $fillable = [
        'producto_id',
        'cotizacion_id',
        'URL'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /**
     * Get the full asset URL for the image.
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                if (str_starts_with($value, '/storage/')) {
                    // Si la ruta comienza con /storage/, eliminamos la barra inicial
                    // y usamos asset() para que resuelva correctamente el subdirectorio de producción
                    return asset(ltrim($value, '/'));
                }
                return $value;
            }
        );
    }
}
