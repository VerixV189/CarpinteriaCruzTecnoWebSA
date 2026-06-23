<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { RefreshCw } from 'lucide-vue-next';

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
    monto: string;
    fecha_pago: string;
    venta: Venta;
}

const props = defineProps<{
    pagos: Pago[];
}>();

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
                                <th class="p-4">Cliente</th>
                                <th class="p-4">Fecha Pago</th>
                                <th class="p-4">Monto Transacción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="filteredPagos.length === 0">
                                <td colspan="4" class="p-4 text-center text-muted-foreground">
                                    No se encontraron pagos registrados.
                                </td>
                            </tr>
                            <tr v-for="pago in filteredPagos" :key="pago.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#REC-{{ pago.id }}</td>
                                <td class="p-4 font-medium">{{ pago.venta.pedido.cotizacion.cliente.usuario.nombre }} {{ pago.venta.pedido.cotizacion.cliente.usuario.apellido }}</td>
                                <td class="p-4">{{ new Date(pago.fecha_pago).toLocaleDateString() }}</td>
                                <td class="p-4 font-bold text-green-600 dark:text-green-500">
                                    Bs. {{ parseFloat(pago.monto).toFixed(2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
