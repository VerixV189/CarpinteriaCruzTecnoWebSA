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

interface Carpintero {
    usuario: Usuario;
}

interface Cotizacion {
    id: number;
    descripcion: string;
    costo_total: string;
    estado: string;
    cliente: Cliente;
    carpintero: Carpintero;
}

const props = defineProps<{
    cotizaciones: Cotizacion[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cotizaciones', href: '/cotizaciones' },
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

const filteredCotizaciones = computed(() => {
    return props.cotizaciones.filter((c) => {
        const client = `${c.cliente.usuario.nombre} ${c.cliente.usuario.apellido}`.toLowerCase();
        const carpintero = `${c.carpintero.usuario.nombre} ${c.carpintero.usuario.apellido}`.toLowerCase();
        const desc = c.descripcion.toLowerCase();
        const query = searchQuery.value.toLowerCase();
        return client.includes(query) || carpintero.includes(query) || desc.includes(query);
    });
});
</script>

<template>
    <Head title="Cotizaciones" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Gestión de Cotizaciones</h1>
                    <p class="text-sm text-muted-foreground">Presupuestos y cotizaciones de proyectos de carpintería para clientes.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por cliente, carpintero o descripción..."
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
                                <th class="p-4">ID</th>
                                <th class="p-4">Cliente</th>
                                <th class="p-4">Carpintero Asignado</th>
                                <th class="p-4">Descripción</th>
                                <th class="p-4">Costo Total</th>
                                <th class="p-4">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="filteredCotizaciones.length === 0">
                                <td colspan="6" class="p-4 text-center text-muted-foreground">
                                    No se encontraron cotizaciones.
                                </td>
                            </tr>
                            <tr v-for="cotizacion in filteredCotizaciones" :key="cotizacion.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#{{ cotizacion.id }}</td>
                                <td class="p-4 font-medium">{{ cotizacion.cliente.usuario.nombre }} {{ cotizacion.cliente.usuario.apellido }}</td>
                                <td class="p-4">{{ cotizacion.carpintero.usuario.nombre }} {{ cotizacion.carpintero.usuario.apellido }}</td>
                                <td class="p-4 max-w-xs truncate">{{ cotizacion.descripcion }}</td>
                                <td class="p-4 font-semibold text-amber-600 dark:text-amber-500">
                                    Bs. {{ parseFloat(cotizacion.costo_total).toFixed(2) }}
                                </td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${cotizacion.estado === 'aprobada' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : cotizacion.estado === 'pendiente' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300'}`">
                                        {{ cotizacion.estado }}
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
