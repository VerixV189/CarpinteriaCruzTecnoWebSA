<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';
import { RefreshCw, QrCode, CreditCard, Banknote, LoaderCircle, X } from 'lucide-vue-next';

interface Usuario {
    nombre: string;
    apellido: string;
}

interface Cliente {
    usuario: Usuario;
}

interface Cotizacion {
    cliente: Cliente;
}

interface Pedido {
    cotizacion: Cotizacion;
}

interface Venta {
    pedido: Pedido;
}

interface Pago {
    id: number;
    subtotal: string;
    fecha_vencimiento: string;
    estado: string;
    venta: Venta;
}

const props = defineProps<{
    pagos: Pago[];
}>();

const page = usePage();
const currentUserRole = computed(() => page.props.auth.user.rol_id);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pagos', href: '/pagos' },
];

const searchQuery = ref('');
const isRefreshing = ref(false);

const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

const filteredPagos = computed(() => {
    return props.pagos.filter((p) => {
        const client = `${p.venta.pedido.cotizacion.cliente.usuario.nombre} ${p.venta.pedido.cotizacion.cliente.usuario.apellido}`.toLowerCase();
        const query = searchQuery.value.toLowerCase();
        return client.includes(query);
    });
});

// Modal de Pago Fácil (Cliente)
const showPagoModal = ref(false);
const pagoSeleccionado = ref<Pago | null>(null);

const pagoFacilForm = useForm({
    metodo: 'QR'
});

const abrirPagoFacil = (pago: Pago) => {
    pagoSeleccionado.value = pago;
    showPagoModal.value = true;
};

const isProcessingPagoFacil = ref(false);
const qrImageBase64 = ref<string | null>(null);

const procesarPagoFacil = async () => {
    if (!pagoSeleccionado.value) return;
    
    isProcessingPagoFacil.value = true;
    qrImageBase64.value = null; // Reiniciar QR anterior
    
    try {
        const response = await axios.post(`/pagos/${pagoSeleccionado.value.id}/pagofacil`, {
            metodo: pagoFacilForm.metodo
        });
        
        // Guardamos la imagen QR devuelta por la API Directa
        if (response.data.qrBase64) {
            qrImageBase64.value = response.data.qrBase64;
        } else {
            throw new Error("No se recibió la imagen QR");
        }
        
    } catch (error) {
        console.error("Error al generar QR de Pago Fácil", error);
        alert("Ocurrió un error al contactar con la API de Pago Fácil.");
    } finally {
        isProcessingPagoFacil.value = false;
    }
};

// Modificamos el botón cancelar para limpiar el QR
const cerrarModalPago = () => {
    showPagoModal.value = false;
    qrImageBase64.value = null;
};

// Formulario de Cobro en Efectivo (Admin/Carpintero)
const efectivoForm = useForm({});

const cobrarEfectivo = (pago: Pago) => {
    if (confirm('¿Confirmas que recibiste el pago en efectivo para esta cuota?')) {
        efectivoForm.put(`/pagos/${pago.id}/efectivo`, {
            preserveScroll: true
        });
    }
};

</script>

<template>
    <Head title="Pagos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Control de Pagos</h1>
                    <p class="text-sm text-muted-foreground">Listado de cobros, adelantos y transacciones de clientes.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por cliente..."
                    class="flex h-9 w-full max-w-sm rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                />
                <button
                    @click="refreshPage"
                    :disabled="isRefreshing"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md border border-input bg-background px-3 h-9 text-xs font-medium text-stone-600 dark:text-stone-300 shadow-sm transition-colors hover:bg-stone-50 hover:text-stone-900 dark:hover:bg-stone-850 dark:hover:text-white focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50"
                    title="Refrescar datos"
                >
                    <RefreshCw class="h-3.5 w-3.5" :class="{ 'animate-spin': isRefreshing }" />
                    <span>Refrescar</span>
                </button>
            </div>

            <div class="rounded-md border border-sidebar-border bg-card text-card-foreground shadow">
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="border-b border-sidebar-border bg-muted/50">
                            <tr class="text-left font-medium text-muted-foreground">
                                <th class="p-4">Recibo ID</th>
                                <th v-if="currentUserRole !== 2" class="p-4">Cliente</th>
                                <th class="p-4">Monto Transacción</th>
                                <th class="p-4">Vencimiento</th>
                                <th class="p-4">Estado</th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="filteredPagos.length === 0">
                                <td :colspan="currentUserRole !== 2 ? 6 : 5" class="p-4 text-center text-muted-foreground">
                                    No se encontraron pagos registrados.
                                </td>
                            </tr>
                            <tr v-for="pago in filteredPagos" :key="pago.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#REC-{{ pago.id }}</td>
                                <td v-if="currentUserRole !== 2" class="p-4 font-medium">{{ pago.venta.pedido.cotizacion.cliente.usuario.nombre }} {{ pago.venta.pedido.cotizacion.cliente.usuario.apellido }}</td>
                                <td class="p-4 font-bold text-green-600 dark:text-green-500">
                                    Bs. {{ parseFloat(pago.subtotal).toFixed(2) }}
                                </td>
                                <td class="p-4">{{ new Date(pago.fecha_vencimiento).toLocaleDateString() }}</td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${pago.estado.toLowerCase() === 'pagado' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'}`">
                                        {{ pago.estado }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div v-if="pago.estado.toLowerCase() === 'pendiente'" class="flex justify-end gap-2">
                                        
                                        <!-- Botón para el Cliente -->
                                        <button v-if="currentUserRole === 2" @click="abrirPagoFacil(pago)" class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-3 py-1.5 text-xs font-medium text-white shadow hover:bg-zinc-800 transition-colors">
                                            <QrCode class="w-3.5 h-3.5 mr-1" /> Pagar Cuota
                                        </button>

                                        <!-- Botón para Administrador / Carpintero -->
                                        <button v-if="currentUserRole !== 2" @click="cobrarEfectivo(pago)" :disabled="efectivoForm.processing" class="inline-flex items-center justify-center rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 shadow-sm hover:bg-green-100 transition-colors">
                                            <Banknote class="w-3.5 h-3.5 mr-1" /> Cobrar en Efectivo
                                        </button>
                                    </div>
                                    <span v-else class="text-xs text-muted-foreground italic">
                                        Transacción Completada
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL DE PAGO FÁCIL -->
        <div v-if="showPagoModal" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
            <div class="bg-card text-card-foreground rounded-lg shadow-lg w-full max-w-md p-6 relative">
                <button @click="showPagoModal = false" class="absolute top-4 right-4 text-muted-foreground hover:text-foreground">
                    <X class="w-5 h-5" />
                </button>
                <div class="flex items-center gap-3 border-b pb-4 mb-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <CreditCard class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">Pasarela Pago Fácil</h2>
                        <p class="text-sm text-muted-foreground">Procesa tu pago de manera segura.</p>
                    </div>
                </div>

                <div class="mb-6 space-y-2">
                    <p class="text-sm">Monto a cancelar: <span class="font-bold text-green-600">Bs. {{ pagoSeleccionado ? parseFloat(pagoSeleccionado.subtotal).toFixed(2) : '0.00' }}</span></p>
                    <h3 class="text-lg font-medium leading-6 text-foreground">
                        {{ qrImageBase64 ? 'Escanea el Código QR' : 'Opciones de Pago' }}
                    </h3>
                    <div class="mt-4 text-sm text-muted-foreground space-y-4">
                        <p v-if="!qrImageBase64">
                            Estás a punto de iniciar el pago para la cuota 
                            <strong class="text-foreground">Recibo #{{ pagoSeleccionado?.id }}</strong>.
                            Por favor confirma el método de pago que utilizarás en la plataforma.
                        </p>

                        <!-- Mostrar el QR si ya se generó -->
                        <div v-if="qrImageBase64" class="flex flex-col items-center justify-center p-4 border rounded-lg bg-white">
                            <img :src="'data:image/png;base64,' + qrImageBase64" alt="Código QR de Pago" class="w-64 h-64 object-contain shadow-sm border p-2 mb-4" />
                            <p class="text-center font-medium text-blue-700">
                                Escanea este código QR con la aplicación móvil de tu banco.
                            </p>
                            <p class="text-xs text-center mt-2 text-gray-500">
                                Una vez completado el pago, tu sistema se actualizará automáticamente.
                                Puedes cerrar esta ventana.
                            </p>
                        </div>
                        
                        <!-- Formulario de selección (Solo se muestra si aún no hay QR) -->
                        <form v-else @submit.prevent="procesarPagoFacil" class="space-y-4">
                            <div class="grid gap-2">
                                <label class="text-sm font-medium leading-none">Método de Pago Preferido</label>
                                <select v-model="pagoFacilForm.metodo" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                                    <option value="QR">Pago QR Simple</option>
                                    <option value="Tarjeta">Tarjeta de Crédito / Débito</option>
                                </select>
                            </div>
                            <div class="flex justify-end gap-3 pt-4">
                                <button type="button" @click="cerrarModalPago" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                                    Cancelar
                                </button>
                                <button type="submit" :disabled="isProcessingPagoFacil" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 transition-colors">
                                    <LoaderCircle v-if="isProcessingPagoFacil" class="mr-2 h-4 w-4 animate-spin" />
                                    {{ isProcessingPagoFacil ? 'Generando...' : 'Generar ' + pagoFacilForm.metodo }}
                                </button>
                            </div>
                        </form>

                        <!-- Botón cerrar si el QR ya está visible -->
                        <div v-if="qrImageBase64" class="flex justify-center pt-4">
                            <button type="button" @click="cerrarModalPago" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-6 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                                Cerrar y Esperar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
