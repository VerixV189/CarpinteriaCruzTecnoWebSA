<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CotizacionController extends Controller
{
    private function verificarPermiso($nombrePermiso)
    {
        $user = Auth::user();
        if (!$user || !$user->rol) abort(403, 'No tienes un rol asignado.');
        if ($user->rol_id === 1) return true;
        if (!$user->rol->permisos->contains('nombre', $nombrePermiso)) {
            abort(403, 'Acceso Denegado.');
        }
    }

    public function index(Request $request)
    {
        $this->verificarPermiso('LISCOT');

        $user = Auth::user();
        
        $query = Cotizacion::with([
            'cliente.usuario', 
            'detalleCotizaciones.carpintero.usuario'
        ]);

        // Si es cliente (rol_id = 2), solo ve sus propias cotizaciones
        if ($user->rol_id === 2) {
            $query->whereHas('cliente', function ($q) use ($user) {
                $q->where('usuario_id', $user->id);
            });
        }
        // Admin (1) y Carpintero (3) ven todas las cotizaciones automáticamente

        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('descripcion', 'like', "%{$search}%")
                  ->orWhere('estado', 'like', "%{$search}%")
                  ->orWhereHas('cliente.usuario', function($qUser) use ($search) {
                      $qUser->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                  });
            });
        }

        $cotizaciones = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Cotizaciones/Index', [
            'cotizaciones' => $cotizaciones,
            'filters' => $request->only(['search'])
        ]);
    }

    // Vista para crear cotización
    public function create()
    {
        $this->verificarPermiso('REGCOT');

        $user = Auth::user();
        
        if ($user->rol_id !== 2 || !$user->cliente) {
            abort(403, 'Solo los clientes registrados pueden solicitar cotizaciones.');
        }

        return Inertia::render('Cotizaciones/Create');
    }

    // El cliente crea la cotización base
    public function store(Request $request)
    {
        $this->verificarPermiso('REGCOT');

        $user = Auth::user();
        
        if ($user->rol_id !== 2 || !$user->cliente) {
            abort(403, 'Solo los clientes registrados pueden solicitar cotizaciones.');
        }

        $request->validate([
            'descripcion' => 'required|string',
        ]);

        Cotizacion::create([
            'cliente_id' => $user->cliente->id,
            'descripcion' => $request->descripcion,
            'estado' => 'Pendiente',
        ]);

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización solicitada correctamente.');
    }

    // Vista "Maestro-Detalle" (El ojo que me sugeriste)
    public function show(Cotizacion $cotizacion)
    {
        $this->verificarPermiso('LISCOT');

        $user = Auth::user();

        $cotizacion->load([
            'cliente.usuario', 
            'detalleCotizaciones.carpintero.usuario'
        ]);

        if ($user->rol_id === 2 && (!$cotizacion->cliente || $cotizacion->cliente->usuario_id != $user->id)) {
            abort(403, 'No puedes ver esta cotización.');
        }

        return Inertia::render('Cotizaciones/Show', [
            'cotizacion' => $cotizacion
        ]);
    }

    // El carpintero agrega detalles desde la vista "Show"
    public function storeDetalle(Request $request, Cotizacion $cotizacion)
    {
        $this->verificarPermiso('ACTCOT');

        $user = Auth::user();

        if ($user->rol_id !== 3 || !$user->carpintero) {
            abort(403, 'Solo los carpinteros pueden presupuestar.');
        }

        $request->validate([
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0',
        ]);

        DetalleCotizacion::create([
            'cotizacion_id' => $cotizacion->id,
            'carpintero_id' => $user->carpintero->id,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
        ]);

        // Cambiamos el estado a Cotizado automáticamente
        if ($cotizacion->estado === 'Pendiente') {
            $cotizacion->update(['estado' => 'Cotizado']);
        }

        return redirect()->back()->with('success', 'Detalle agregado a la cotización.');
    }

    // El cliente aprueba o rechaza desde la vista "Show"
    public function updateEstado(Request $request, Cotizacion $cotizacion)
    {
        $this->verificarPermiso('ACTCOT');

        $user = Auth::user();
        
        if ($user->rol_id !== 2 || $cotizacion->cliente->usuario_id !== $user->id) {
            abort(403, 'Solo el solicitante puede aprobar esta cotización.');
        }

        $request->validate([
            'estado' => 'required|in:Aprobada,Rechazada',
            'tipo_pago' => 'nullable|in:Contado,Crédito',
            'cuotas' => 'nullable|integer|min:1|max:12',
        ]);

        if ($request->estado === 'Rechazada') {
            $cotizacion->update(['estado' => 'Rechazada']);
            return redirect()->back()->with('success', 'Cotización rechazada.');
        }

        DB::transaction(function () use ($cotizacion, $request) {
            $cotizacion->update(['estado' => 'Aprobada']);

            $total = $cotizacion->detalleCotizaciones->sum('precio');
            
            $pedido = \App\Models\Pedido::create([
                'cotizacion_id' => $cotizacion->id,
                'codigo' => 'PED-' . str_pad($cotizacion->id, 4, '0', STR_PAD_LEFT),
                'precio' => $total,
                'fecha_entrega' => now()->addMonth(),
            ]);

            \App\Models\DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => null,
                'cantidad' => 1,
                'precio' => $total,
                'estado' => 'Pendiente',
                'descripcion' => 'Trabajo a medida de la cotización #' . $cotizacion->id,
            ]);

            $venta = \App\Models\Venta::create([
                'pedido_id' => $pedido->id,
                'codigo' => 'VEN-' . str_pad($pedido->id, 4, '0', STR_PAD_LEFT),
                'total_costo' => $total,
                'fecha_entregado' => null,
                'tipo' => $request->tipo_pago ?: 'Contado',
            ]);

            if ($request->tipo_pago === 'Crédito') {
                $cuotas = $request->cuotas ?: 1;
                $montoCuota = $total / $cuotas;
                for ($i = 1; $i <= $cuotas; $i++) {
                    \App\Models\Pago::create([
                        'venta_id' => $venta->id,
                        'subtotal' => $montoCuota,
                        'interes' => 0,
                        'estado' => 'Pendiente',
                        'fecha_vencimiento' => now()->addMonths($i),
                    ]);
                }
            } else {
                \App\Models\Pago::create([
                    'venta_id' => $venta->id,
                    'subtotal' => $total,
                    'interes' => 0,
                    'estado' => 'Pendiente',
                    'fecha_vencimiento' => now(), // Pago inmediato
                ]);
            }
        });

        return redirect()->back()->with('success', 'Cotización aprobada y pedido generado automáticamente.');
    }
}
