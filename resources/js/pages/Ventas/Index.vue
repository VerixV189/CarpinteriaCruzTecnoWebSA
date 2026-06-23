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
    id: number;
    total_costo: string;
    fecha_entregado: string;
    tipo: string;
    pedido: Pedido;
}

const props = defineProps<{
    ventas: Venta[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Ventas', href: '/ventas' },
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

const filteredVentas = computed(() => {
    return props.ventas.filter((v) => {
        const client = `${v.pedido.cotizacion.cliente.usuario.nombre} ${v.pedido.cotizacion.cliente.usuario.apellido}`.toLowerCase();
        const type = v.tipo.toLowerCase();
        const query = searchQuery.value.toLowerCase();
        return client.includes(query) || type.includes(query);
    });
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
                    placeholder="Buscar por cliente o tipo..."
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
                                <th class="p-4">Factura ID</th>
                                <th class="p-4">Cliente</th>
                                <th class="p-4">Fecha Entregado</th>
                                <th class="p-4">Tipo</th>
                                <th class="p-4">Total Costo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="filteredVentas.length === 0">
                                <td colspan="5" class="p-4 text-center text-muted-foreground">
                                    No se encontraron ventas registradas.
                                </td>
                            </tr>
                            <tr v-for="venta in filteredVentas" :key="venta.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#FAC-{{ venta.id }}</td>
                                <td class="p-4 font-medium">{{ venta.pedido.cotizacion.cliente.usuario.nombre }} {{ venta.pedido.cotizacion.cliente.usuario.apellido }}</td>
                                <td class="p-4">{{ new Date(venta.fecha_entregado).toLocaleDateString() }}</td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-stone-100 text-stone-850 dark:bg-stone-850 dark:text-stone-300">
                                        {{ venta.tipo }}
                                    </span>
                                </td>
                                <td class="p-4 font-bold text-green-600 dark:text-green-500">
                                    Bs. {{ parseFloat(venta.total_costo).toFixed(2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
