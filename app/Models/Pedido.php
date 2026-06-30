<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraBitacora;

class Pedido extends Model
{
    use RegistraBitacora;
    protected $fillable = [
        'cliente_id',
        'cotizacion_id',
        'codigo',
        'precio',
        'fecha_entrega'
    ];

    protected $appends = ['estado', 'fecha_inicio', 'fecha_fin_estimada', 'cliente_real'];

    public function getEstadoAttribute()
    {
        $detalles = $this->detallePedidos;
        if ($detalles->isEmpty()) {
            return 'Pendiente';
        }

        $estados = $detalles->pluck('estado')->map(function($e) {
            return strtolower($e);
        })->toArray();

        // Si todos están entregados, finalizados o aprobados
        $todosFinalizados = true;
        foreach ($estados as $est) {
            if ($est !== 'finalizado' && $est !== 'entregado' && $est !== 'aprobado' && $est !== 'aprobada') {
                $todosFinalizados = false;
                break;
            }
        }
        if ($todosFinalizados) {
            return 'entregado';
        }

        // Si alguno está en proceso
        foreach ($estados as $est) {
            if ($est === 'en proceso' || $est === 'proceso') {
                return 'proceso';
            }
        }

        return 'pendiente';
    }

    public function getFechaInicioAttribute()
    {
        return $this->created_at ? $this->created_at->toIso8601String() : null;
    }

    public function getFechaFinEstimadaAttribute()
    {
        return $this->fecha_entrega;
    }

    public function getClienteRealAttribute()
    {
        return $this->cliente ?? ($this->cotizacion ? $this->cotizacion->cliente : null);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

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
