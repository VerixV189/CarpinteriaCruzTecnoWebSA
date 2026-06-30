<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import { RefreshCw, QrCode, CreditCard, Banknote, LoaderCircle, X } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

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
    cliente_real: Cliente;
    cotizacion: Cotizacion | null;
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

interface PaginatedPagos {
    data: Pago[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    pagos: PaginatedPagos;
    filters?: { search?: string };
}>();

const page = usePage<any>();
const currentUserRole = computed(() => page.props.auth.user.rol_id);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pagos', href: '/pagos' },
];

const searchQuery = ref(props.filters?.search || '');
const isRefreshing = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/pagos', { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['pagos'],
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

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
            startPolling(pagoSeleccionado.value.id);
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

let pollingInterval: number | null = null;

const startPolling = (pagoId: number) => {
    stopPolling();
    pollingInterval = window.setInterval(async () => {
        try {
            const { data } = await axios.get(`/pagos/${pagoId}/status`);
            if (data.estado.toLowerCase() === 'pagado') {
                cerrarModalPago();
                refreshPage();
            }
        } catch (e) {
            console.error('Error polling status', e);
        }
    }, 3000);
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
};

// Modificamos el botón cancelar para limpiar el QR y detener el polling
const cerrarModalPago = () => {
    showPagoModal.value = false;
    qrImageBase64.value = null;
    stopPolling();
};

// Formulario de Cobro en Efectivo (Admin/Carpintero)
const efectivoForm = useForm({});

const confirmOpen = ref(false);
const pendingPagoId = ref<number | null>(null);

const confirmCobro = () => {
    if (pendingPagoId.value !== null) {
        efectivoForm.put(`/pagos/${pendingPagoId.value}/efectivo`, {
            preserveScroll: true
        });
        confirmOpen.value = false;
        pendingPagoId.value = null;
    }
};

const cobrarEfectivo = (pago: Pago) => {
    pendingPagoId.value = pago.id;
    confirmOpen.value = true;
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

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por cliente..."
                    class="flex h-9 w-full sm:max-w-sm rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
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

            <!-- VISTA DE TARJETAS PARA EL CLIENTE -->
            <div v-if="currentUserRole === 2" class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <div v-if="pagos.data.length === 0" class="col-span-full rounded-md border border-stone-200 dark:border-stone-800 bg-card p-12 text-center text-muted-foreground">
                        No tienes ningún pago registrado actualmente.
                    </div>
                    
                    <div v-for="pago in pagos.data" :key="pago.id" class="rounded-xl border border-stone-200 dark:border-stone-850 bg-card shadow-sm hover:shadow-md transition-shadow overflow-hidden flex flex-col justify-between">
                        <!-- Cabecera de la Tarjeta -->
                        <div class="p-5 border-b border-stone-100 dark:border-stone-850 flex flex-col sm:flex-row items-start sm:justify-between gap-3 sm:gap-4">
                            <div class="space-y-1">
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-500">Recibo de Cuota</span>
                                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Recibo #REC-{{ pago.id }}</h3>
                            </div>
                            <span :class="`inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold capitalize shrink-0 self-start ${pago.estado.toLowerCase() === 'pagado' ? 'bg-green-100 text-green-800 dark:bg-green-950/40 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400'}`">
                                {{ pago.estado }}
                            </span>
                        </div>

                        <!-- Cuerpo de la Tarjeta -->
                        <div class="p-5 space-y-3 flex-1">
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-1 sm:gap-4 text-xs">
                                <span class="text-muted-foreground">Fecha de Vencimiento:</span>
                                <span class="font-medium text-stone-800 dark:text-stone-200">
                                    {{ new Date(pago.fecha_vencimiento).toLocaleDateString() }}
                                </span>
                            </div>
                        </div>

                        <!-- Pie de la Tarjeta -->
                        <div class="p-5 border-t border-stone-100 dark:border-stone-850 bg-stone-50/40 dark:bg-stone-900/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="text-xs">
                                <span class="text-muted-foreground">Importe Cuota:</span>
                                <div class="text-lg font-extrabold text-green-600 dark:text-green-500">Bs. {{ parseFloat(pago.subtotal).toFixed(2) }}</div>
                            </div>
                            
                            <div v-if="pago.estado.toLowerCase() === 'pendiente'" class="w-full sm:w-auto">
                                <button @click="abrirPagoFacil(pago)" class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-white bg-zinc-900 hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 px-4 py-2 rounded-lg transition-colors shadow-sm cursor-pointer whitespace-nowrap w-full sm:w-auto">
                                    <QrCode class="w-4 h-4" />
                                    Pagar Cuota
                                </button>
                            </div>
                            <span v-else class="text-xs text-muted-foreground italic flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span> Transacción Completada
                            </span>
                        </div>
                    </div>
                </div>
                <Pagination :links="pagos.links" />
            </div>

            <!-- VISTA DE TABLA PARA ADMIN Y CARPINTERO -->
            <div v-else class="rounded-md border border-sidebar-border bg-card text-card-foreground shadow flex flex-col">
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="border-b border-sidebar-border bg-muted/50">
                            <tr class="text-left font-medium text-muted-foreground">
                                <th class="p-4">Recibo ID</th>
                                <th class="p-4">Cliente</th>
                                <th class="p-4">Monto Transacción</th>
                                <th class="p-4">Vencimiento</th>
                                <th class="p-4">Estado</th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="pagos.data.length === 0">
                                <td colspan="6" class="p-4 text-center text-muted-foreground">
                                    No se encontraron pagos registrados.
                                </td>
                            </tr>
                            <tr v-for="pago in pagos.data" :key="pago.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#REC-{{ pago.id }}</td>
                                <td class="p-4 font-medium">{{ pago.venta?.pedido?.cliente_real?.usuario?.nombre }} {{ pago.venta?.pedido?.cliente_real?.usuario?.apellido }}</td>
                                <td class="p-4 font-bold text-green-600 dark:text-green-500">
                                    Bs. {{ parseFloat(pago.subtotal).toFixed(2) }}
                                </td>
                                <td class="p-4">{{ new Date(pago.fecha_vencimiento).toLocaleDateString() }}</td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${pago.estado.toLowerCase() === 'pagado' ? 'bg-green-100 text-green-800 dark:bg-green-950/50 dark:text-green-400' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-400'}`">
                                        {{ pago.estado }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div v-if="pago.estado.toLowerCase() === 'pendiente'" class="flex justify-end gap-2">
                                        <button v-if="currentUserRole === 1" @click="cobrarEfectivo(pago)" :disabled="efectivoForm.processing" class="inline-flex items-center justify-center rounded-md border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950/40 px-3 py-1.5 text-xs font-medium text-green-700 dark:text-green-400 shadow-sm hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors">
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
                <Pagination :links="pagos.links" />
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

        <ConfirmDialog 
            v-model:open="confirmOpen" 
            title="Confirmar Cobro"
            message="¿Confirmas que recibiste el pago en efectivo para esta cuota? El estado cambiará a Pagado y no se podrá revertir fácilmente."
            confirmLabel="Confirmar Pago"
            variant="info"
            @confirm="confirmCobro"
        />
    </AppLayout>
</template>
