<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { RefreshCw, Eye, X } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';

interface Usuario {
    nombre: string;
    apellido: string;
}

interface Cliente {
    usuario: Usuario;
}

interface Cotizacion {
    id: number;
    cliente: Cliente;
}

interface Pedido {
    id: number;
    codigo: string;
    cliente_real: Cliente;
    cotizacion: Cotizacion | null;
}

interface Pago {
    id: number;
    subtotal: string;
    interes: string;
    estado: string;
    fecha_vencimiento: string;
    created_at: string;
}

interface Venta {
    id: number;
    codigo: string;
    total_costo: string;
    fecha_entregado: string | null;
    tipo: string;
    pedido: Pedido;
    pagos: Pago[];
    created_at: string;
}

interface PaginatedVentas {
    data: Venta[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    ventas: PaginatedVentas;
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Ventas', href: route('ventas.index') },
];

const searchQuery = ref(props.filters?.search || '');
const isRefreshing = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/ventas', { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['ventas'],
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

// Modal state
const selectedVenta = ref<Venta | null>(null);
const isModalOpen = ref(false);

const openDetails = (venta: Venta) => {
    selectedVenta.value = venta;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedVenta.value = null;
    }, 200);
};

const totalPagado = computed(() => {
    if (!selectedVenta.value) return 0;
    return selectedVenta.value.pagos
        .filter(p => p.estado === 'Pagado')
        .reduce((sum, p) => sum + parseFloat(p.subtotal), 0);
});
</script>

<template>
    <Head title="Ventas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Registro de Ventas</h1>
                    <p class="text-sm text-muted-foreground">Historial de facturación y ventas concretadas.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por código, cliente o tipo..."
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
                                <th class="p-4">Código Venta</th>
                                <th class="p-4">Cliente</th>
                                <th class="p-4">Fecha Venta</th>
                                <th class="p-4">Tipo</th>
                                <th class="p-4">Total</th>
                                <th class="p-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="ventas.data.length === 0">
                                <td colspan="6" class="p-4 text-center text-muted-foreground">
                                    No se encontraron ventas registradas.
                                </td>
                            </tr>
                            <tr v-for="venta in ventas.data" :key="venta.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold text-muted-foreground">{{ venta.codigo }}</td>
                                <td class="p-4 font-medium">{{ venta.pedido?.cliente_real?.usuario?.nombre }} {{ venta.pedido?.cliente_real?.usuario?.apellido }}</td>
                                <td class="p-4">{{ new Date(venta.created_at).toLocaleDateString() }}</td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${venta.tipo === 'Crédito' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-stone-100 text-stone-800 dark:bg-stone-800 dark:text-stone-300'}`">
                                        {{ venta.tipo }}
                                    </span>
                                </td>
                                <td class="p-4 font-bold text-green-600 dark:text-green-500">
                                    Bs. {{ parseFloat(venta.total_costo).toFixed(2) }}
                                </td>
                                <td class="p-4 text-center">
                                    <button @click="openDetails(venta)" class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-950/50 px-3 py-1.5 rounded-md transition-colors">
                                        <Eye class="h-3.5 w-3.5" />
                                        Detalles
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="ventas.links" />
            </div>
        </div>

        <!-- Modal Detalles de Venta -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-3xl rounded-xl bg-background shadow-2xl border border-border flex flex-col max-h-[90vh]">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-border p-5 shrink-0">
                    <div>
                        <h3 class="text-xl font-bold text-foreground flex items-center gap-2">
                            Detalles de la Venta 
                            <span class="text-muted-foreground text-sm font-normal">{{ selectedVenta?.codigo }}</span>
                        </h3>
                        <p class="text-sm text-muted-foreground mt-1">Cliente: {{ selectedVenta?.pedido?.cliente_real?.usuario?.nombre }} {{ selectedVenta?.pedido?.cliente_real?.usuario?.apellido }}</p>
                    </div>
                    <button @click="closeModal" class="rounded-full p-2 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                
                <!-- Body -->
                <div class="p-5 overflow-y-auto flex-1 space-y-6" v-if="selectedVenta">
                    
                    <!-- Resumen Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="rounded-lg border bg-card p-4 shadow-sm">
                            <p class="text-xs font-medium text-muted-foreground mb-1">Monto Total</p>
                            <p class="text-2xl font-bold text-foreground">Bs. {{ parseFloat(selectedVenta.total_costo).toFixed(2) }}</p>
                        </div>
                        <div class="rounded-lg border bg-card p-4 shadow-sm">
                            <p class="text-xs font-medium text-muted-foreground mb-1">Total Pagado</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-500">Bs. {{ totalPagado.toFixed(2) }}</p>
                        </div>
                        <div class="rounded-lg border bg-card p-4 shadow-sm">
                            <p class="text-xs font-medium text-muted-foreground mb-1">Deuda Restante</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-500">Bs. {{ (parseFloat(selectedVenta.total_costo) - totalPagado).toFixed(2) }}</p>
                        </div>
                    </div>

                    <!-- Detalles -->
                    <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-sm bg-muted/30 p-4 rounded-lg">
                        <div>
                            <span class="text-muted-foreground block text-xs">Tipo de Venta</span>
                            <span class="font-medium">{{ selectedVenta.tipo }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block text-xs">Fecha de Venta</span>
                            <span class="font-medium">{{ new Date(selectedVenta.created_at).toLocaleString() }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block text-xs">ID Pedido Asociado</span>
                            <span class="font-medium">{{ selectedVenta.pedido?.codigo || 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block text-xs">Fecha de Entrega</span>
                            <span class="font-medium">{{ selectedVenta.fecha_entregado ? new Date(selectedVenta.fecha_entregado).toLocaleDateString() : 'Pendiente' }}</span>
                        </div>
                    </div>

                    <!-- Pagos -->
                    <div>
                        <h4 class="font-bold text-lg mb-3">Historial de Pagos / Cuotas</h4>
                        <div class="rounded-md border border-sidebar-border bg-background shadow-sm overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b">
                                    <tr class="text-left font-medium text-muted-foreground">
                                        <th class="p-3">Recibo ID</th>
                                        <th class="p-3">Monto</th>
                                        <th class="p-3">Vencimiento</th>
                                        <th class="p-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-sidebar-border">
                                    <tr v-if="!selectedVenta.pagos || selectedVenta.pagos.length === 0">
                                        <td colspan="4" class="p-4 text-center text-muted-foreground">
                                            No hay pagos registrados.
                                        </td>
                                    </tr>
                                    <tr v-for="pago in selectedVenta.pagos" :key="pago.id" class="hover:bg-muted/30 transition-colors">
                                        <td class="p-3 font-mono text-xs text-muted-foreground">#REC-{{ pago.id }}</td>
                                        <td class="p-3 font-semibold">Bs. {{ parseFloat(pago.subtotal).toFixed(2) }}</td>
                                        <td class="p-3">{{ new Date(pago.fecha_vencimiento).toLocaleDateString() }}</td>
                                        <td class="p-3">
                                            <span :class="`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${pago.estado === 'Pagado' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'}`">
                                                {{ pago.estado }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="border-t border-border p-4 bg-muted/20 shrink-0 flex justify-end rounded-b-xl">
                    <button @click="closeModal" class="rounded-md bg-stone-900 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-stone-800 transition-colors dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
