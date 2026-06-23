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
    id: number;
    fecha_inicio: string;
    fecha_fin_estimada: string;
    estado: string;
    cotizacion: Cotizacion;
}

const props = defineProps<{
    pedidos: Pedido[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pedidos', href: '/pedidos' },
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

const filteredPedidos = computed(() => {
    return props.pedidos.filter((p) => {
        const client = `${p.cotizacion.cliente.usuario.nombre} ${p.cotizacion.cliente.usuario.apellido}`.toLowerCase();
        const status = p.estado.toLowerCase();
        const query = searchQuery.value.toLowerCase();
        return client.includes(query) || status.includes(query);
    });
});
</script>

<template>
    <Head title="Pedidos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Pedidos en Producción</h1>
                    <p class="text-sm text-muted-foreground">Listado de órdenes de fabricación activas en el taller.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por cliente o estado..."
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
                                <th class="p-4">Pedido ID</th>
                                <th class="p-4">Cliente</th>
                                <th class="p-4">Fecha Inicio</th>
                                <th class="p-4">Entrega Estimada</th>
                                <th class="p-4">Estado de Fabricación</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="filteredPedidos.length === 0">
                                <td colspan="5" class="p-4 text-center text-muted-foreground">
                                    No se encontraron pedidos.
                                </td>
                            </tr>
                            <tr v-for="pedido in filteredPedidos" :key="pedido.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#{{ pedido.id }}</td>
                                <td class="p-4 font-medium">{{ pedido.cotizacion.cliente.usuario.nombre }} {{ pedido.cotizacion.cliente.usuario.apellido }}</td>
                                <td class="p-4">{{ new Date(pedido.fecha_inicio).toLocaleDateString() }}</td>
                                <td class="p-4">{{ new Date(pedido.fecha_fin_estimada).toLocaleDateString() }}</td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${pedido.estado === 'entregado' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : pedido.estado === 'proceso' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'}`">
                                        {{ pedido.estado }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
