<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraBitacora;

class Tipo extends Model
{
    use HasFactory, RegistraBitacora;

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado'
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'tipo_id');
    }
}
