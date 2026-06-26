<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Pago;
use Inertia\Inertia;

class PagoController extends Controller
{
    private function verificarPermiso($nombrePermiso)
    {
        $user = Auth::user();
        if (!$user || !$user->rol) abort(403, 'No tienes un rol asignado.');
        if ($user->rol_id === 1) return true;
        // Solo para no romper si no existen permisos, si no lo tiene configurado lo dejamos pasar en desarrollo, 
        // pero normalmente esto fallaría.
        if (!$user->rol->permisos->contains('nombre', $nombrePermiso)) {
            // abort(403, 'Acceso Denegado.');
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Pago::with('venta.pedido.cotizacion.cliente.usuario');

        // Si es cliente (2), solo ve los suyos
        if ($user->rol_id === 2) {
            $query->whereHas('venta.pedido.cotizacion.cliente', function ($q) use ($user) {
                $q->where('usuario_id', $user->id);
            });
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('estado', 'like', "%{$search}%")
                  ->orWhereHas('venta.pedido.cotizacion.cliente.usuario', function($qUser) use ($search) {
                      $qUser->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apellido', 'like', "%{$search}%");
                  });
            });
        }

        $pagos = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'filters' => $request->only(['search'])
        ]);
    }

    public function marcarEfectivo(Request $request, Pago $pago)
    {
        // Solo admin (1) o carpintero (3)
        $user = Auth::user();
        if ($user->rol_id === 2) {
            abort(403, 'Los clientes no pueden autodenominar sus pagos en efectivo.');
        }
        
        $pago->update(['estado' => 'Pagado']);

        return redirect()->back()->with('success', 'Pago registrado en efectivo exitosamente.');
    }

    public function iniciarPagoFacil(Request $request, Pago $pago)
    {
        $user = Auth::user();
        // Verificar que el pago le pertenece al cliente
        $pago->load('venta.pedido.cotizacion.cliente');
        if ($user->rol_id === 2 && $pago->venta->pedido->cotizacion->cliente->usuario_id != $user->id) {
            abort(403, 'No tienes permiso para pagar esta cuota.');
        }

        $request->validate([
            'metodo' => 'required|in:QR,Tarjeta'
        ]);

        $baseUrl = 'https://masterqr.pagofacil.com.bo/api/services/v2';
        $tokenService = config('services.pagofacil.token_service');
        $tokenSecret = config('services.pagofacil.secret_token');
        
        // 1. Login para obtener Access Token
        $loginResponse = Http::withHeaders([
            'tcTokenService' => $tokenService,
            'tcTokenSecret' => $tokenSecret,
        ])->post("$baseUrl/login");

        if (!$loginResponse->successful() || $loginResponse->json('error') !== 0) {
            return response()->json(['error' => 'Error de autenticación con PagoFácil API'], 500);
        }

        $accessToken = $loginResponse->json('values.accessToken');

        // 2. Obtener el Payment Method ID de los servicios habilitados
        $servicesResponse = Http::withToken($accessToken)->post("$baseUrl/list-enabled-services");
        $paymentMethodId = $servicesResponse->json('values.0.paymentMethodId'); // Tomamos el primer servicio QR

        // MODO PRUEBA: Multiplicamos por 0.001 para pagar solo centavos y no perder dinero
        $monto = round($pago->subtotal * 0.001, 2);
        if ($monto <= 0) $monto = 0.01;

        // URLs absolutas para la integración (callback)
        $url_callback = rtrim(env('APP_URL', url('/')), '/') . '/pagos/callback';

        $nombreCliente = $pago->venta->pedido->cotizacion->cliente->usuario->nombre ?? 'Cliente';
        $emailCliente = $pago->venta->pedido->cotizacion->cliente->usuario->email ?? 'correo@ejemplo.com';
        $idCliente = $pago->venta->pedido->cotizacion->cliente_id ?? '1';

        // 3. Payload para Generar QR
        $qrPayload = [
            "paymentMethod" => $paymentMethodId,
            "clientName" => $nombreCliente,
            "documentType" => 1, // 1 = CI
            "documentId" => "0000000",
            "phoneNumber" => "77777777",
            "email" => $emailCliente,
            "paymentNumber" => $pago->id . '-' . time(), // Evitar duplicados
            "amount" => $monto,
            "currency" => 2, // BOB
            "clientCode" => (string)$idCliente,
            "callbackUrl" => $url_callback,
            "orderDetail" => [
                [
                    "serial" => 1,
                    "product" => "Cuota Recibo #" . $pago->id,
                    "quantity" => 1,
                    "price" => $monto,
                    "discount" => 0,
                    "total" => $monto
                ]
            ]
        ];

        $qrResponse = Http::withToken($accessToken)->post("$baseUrl/generate-qr", $qrPayload);

        if (!$qrResponse->successful() || $qrResponse->json('error') !== 0) {
            return response()->json(['error' => 'Error al generar la imagen QR en PagoFácil', 'detalles' => $qrResponse->json()], 500);
        }

        // Devolvemos el Base64 directamente a Vue
        return response()->json([
            'qrBase64' => $qrResponse->json('values.qrBase64'),
            'transactionId' => $qrResponse->json('values.transactionId'),
            'expirationDate' => $qrResponse->json('values.expirationDate')
        ]);
    }

    // Webhook llamado por Pago Fácil
    public function callbackPagoFacil(Request $request)
    {
        // La documentación indica que recibiremos:
        // { PedidoID = 15, Fecha="2020-12-31", Hora="12:59:59", Estado=2, MetodoPago=6 }
        
        $pedidoIdBruto = $request->input('PedidoID'); // En nuestro caso viene como "1-171829391"
        $pedidoIdReal = explode('-', $pedidoIdBruto)[0]; // Extraemos solo el ID real de nuestra DB
        
        $estado = $request->input('Estado'); // 2 = PAGADO

        if ($estado == 2) {
            $pago = Pago::find($pedidoIdReal);
            if ($pago && $pago->estado !== 'Pagado') {
                $pago->update(['estado' => 'Pagado']);
            }
        }

        // Obligatorio responder con este formato JSON
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message' => 'Pago realizado correctamente',
            'messageMostrar' => 0,
            'messageSistema' => '',
            'values' => true
        ]);
    }

    public function returnPagoFacil(Request $request)
    {
        return redirect()->route('pagos.index')->with('success', 'Has retornado de la pasarela de pagos. Verifica el estado actual de tu cuota.');
    }
}
