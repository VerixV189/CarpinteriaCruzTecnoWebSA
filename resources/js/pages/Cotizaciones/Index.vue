<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { RefreshCw } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';

interface Usuario {
    id: number;
    nombre: string;
    apellido: string;
}

interface Cliente {
    usuario: Usuario;
}

interface Carpintero {
    usuario: Usuario;
}

interface DetalleCotizacion {
    precio: string;
}

interface Cotizacion {
    id: number;
    descripcion: string;
    estado: string;
    cliente: Cliente;
    carpintero?: Carpintero | null;
    detalle_cotizaciones?: DetalleCotizacion[];
}

interface PaginatedCotizaciones {
    data: Cotizacion[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    cotizaciones: PaginatedCotizaciones;
    filters?: { search?: string };
}>();

const page = usePage<any>();
const currentUserRole = computed(() => page.props.auth.user.rol_id);

const calcularTotal = (cotizacion: Cotizacion) => {
    if (!cotizacion.detalle_cotizaciones) return 0;
    return cotizacion.detalle_cotizaciones.reduce((total, detalle) => total + parseFloat(detalle.precio), 0);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cotizaciones', href: '/cotizaciones' },
];

const searchQuery = ref(props.filters?.search || '');
const isRefreshing = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/cotizaciones', { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['cotizaciones'],
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};
</script>

<template>
    <Head title="Cotizaciones" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Gestión de Cotizaciones</h1>
                    <p class="text-sm text-muted-foreground">Presupuestos y cotizaciones de proyectos de carpintería.</p>
                </div>
                <!-- Solo el Cliente (rol 2) puede solicitar nuevas cotizaciones -->
                <Link
                    v-if="currentUserRole === 2"
                    href="/cotizaciones/create"
                    class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 transition-colors"
                >
                    Solicitar Cotización
                </Link>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por cliente, carpintero o descripción..."
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
                    <div v-if="cotizaciones.data.length === 0" class="col-span-full rounded-md border border-stone-200 dark:border-stone-800 bg-card p-12 text-center text-muted-foreground">
                        No tienes ninguna cotización solicitada actualmente.
                    </div>
                    
                    <div v-for="cotizacion in cotizaciones.data" :key="cotizacion.id" class="rounded-xl border border-stone-200 dark:border-stone-850 bg-card shadow-sm hover:shadow-md transition-shadow overflow-hidden flex flex-col justify-between">
                        <!-- Cabecera de la Tarjeta -->
                        <div class="p-5 border-b border-stone-100 dark:border-stone-850 flex flex-col sm:flex-row items-start sm:justify-between gap-3 sm:gap-4">
                            <div class="space-y-1">
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-500">Solicitud de Presupuesto</span>
                                <h3 class="text-lg font-bold text-stone-900 dark:text-white">Cotización #{{ cotizacion.id }}</h3>
                            </div>
                            <span :class="`inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold capitalize shrink-0 self-start ${(cotizacion.estado || '').toLowerCase() === 'aprobada' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : ((cotizacion.estado || '').toLowerCase() === 'pendiente' ? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300')}`">
                                {{ cotizacion.estado }}
                            </span>
                        </div>

                        <!-- Cuerpo de la Tarjeta -->
                        <div class="p-5 space-y-4 flex-1">
                            <!-- Requerimiento principal -->
                            <div class="space-y-1.5">
                                <span class="text-xs font-semibold text-stone-700 dark:text-stone-300 block">Tu Requerimiento:</span>
                                <p class="text-sm text-stone-600 dark:text-stone-400 whitespace-pre-line line-clamp-4">{{ cotizacion.descripcion }}</p>
                            </div>
                        </div>

                        <!-- Pie de la Tarjeta -->
                        <div class="p-5 border-t border-stone-100 dark:border-stone-850 bg-stone-50/40 dark:bg-stone-900/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="text-xs">
                                <span class="text-muted-foreground">Presupuesto Estimado:</span>
                                <div class="text-lg font-extrabold text-amber-600 dark:text-amber-500">Bs. {{ calcularTotal(cotizacion).toFixed(2) }}</div>
                            </div>
                            <Link
                                :href="`/cotizaciones/${cotizacion.id}`"
                                class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-stone-700 dark:text-stone-300 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 px-4 py-2 rounded-lg hover:bg-stone-50 dark:hover:bg-stone-750 transition-colors shadow-sm cursor-pointer whitespace-nowrap w-full sm:w-auto"
                            >
                                Ver Detalles
                            </Link>
                        </div>
                    </div>
                </div>
                <Pagination :links="cotizaciones.links" />
            </div>

            <!-- VISTA DE TABLA PARA ADMIN Y CARPINTERO -->
            <div v-else class="rounded-md border border-sidebar-border bg-card text-card-foreground shadow flex flex-col">
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="border-b border-sidebar-border bg-muted/50">
                            <tr class="text-left font-medium text-muted-foreground">
                                <th class="p-4">ID</th>
                                <th class="p-4">Cliente</th>
                                <th class="p-4">Descripción</th>
                                <th class="p-4">Total</th>
                                <th class="p-4">Estado</th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="cotizaciones.data.length === 0">
                                <td colspan="6" class="p-4 text-center text-muted-foreground">
                                    No se encontraron cotizaciones.
                                </td>
                            </tr>
                            <tr v-for="cotizacion in cotizaciones.data" :key="cotizacion.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#{{ cotizacion.id }}</td>
                                <td class="p-4 font-medium">{{ cotizacion.cliente?.usuario?.nombre }} {{ cotizacion.cliente?.usuario?.apellido }}</td>
                                <td class="p-4 max-w-xs truncate">{{ cotizacion.descripcion }}</td>
                                <td class="p-4 font-semibold text-amber-600 dark:text-amber-500">
                                    Bs. {{ calcularTotal(cotizacion).toFixed(2) }}
                                </td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${(cotizacion.estado || '').toLowerCase() === 'aprobada' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : ((cotizacion.estado || '').toLowerCase() === 'pendiente' ? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300')}`">
                                        {{ cotizacion.estado }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <Link
                                        :href="`/cotizaciones/${cotizacion.id}`"
                                        class="inline-flex items-center justify-center rounded-md border border-input bg-background px-3 py-1.5 text-xs font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground"
                                    >
                                        Ver Detalles
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="cotizaciones.links" />
            </div>
        </div>
    </AppLayout>
</template>
