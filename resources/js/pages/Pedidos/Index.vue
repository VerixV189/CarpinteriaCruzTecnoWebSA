<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { RefreshCw, Eye, X, Hammer } from 'lucide-vue-next';
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
    descripcion: string;
    cliente: Cliente;
}

interface Imagen {
    id: number;
    URL: string;
}

interface Producto {
    id: number;
    nombre: string;
    imagenes?: Imagen[];
}

interface DetallePedido {
    id: number;
    cantidad: number;
    precio: string;
    estado: string;
    descripcion: string;
    producto: Producto | null;
}

interface Pedido {
    id: number;
    codigo: string;
    precio: string;
    fecha_inicio: string;
    fecha_fin_estimada: string;
    estado: string;
    cotizacion: Cotizacion | null;
    cliente_real: Cliente;
    detalle_pedidos: DetallePedido[];
}

interface PaginatedPedidos {
    data: Pedido[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    pedidos: PaginatedPedidos;
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Pedidos', href: route('pedidos.index') },
];

const searchQuery = ref(props.filters?.search || '');
const isRefreshing = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/pedidos', { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['pedidos'],
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

// Modal state
const selectedPedido = ref<Pedido | null>(null);
const isModalOpen = ref(false);

const openDetails = (pedido: Pedido) => {
    selectedPedido.value = pedido;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedPedido.value = null;
    }, 200);
};

const page = usePage<any>();
const currentUserRole = computed(() => page.props.auth.user?.rol_id);
</script>

<template>
    <Head title="Pedidos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Pedidos en Producción</h1>
                    <p class="text-sm text-muted-foreground">Listado de órdenes de fabricación activas y finalizadas.</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por código, cliente o estado..."
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
                    <div v-if="pedidos.data.length === 0" class="col-span-full rounded-md border border-stone-200 dark:border-stone-800 bg-card p-12 text-center text-muted-foreground">
                        No tienes ningún pedido registrado actualmente.
                    </div>
                    
                    <div v-for="pedido in pedidos.data" :key="pedido.id" class="rounded-xl border border-stone-200 dark:border-stone-850 bg-card shadow-sm hover:shadow-md transition-shadow overflow-hidden flex flex-col justify-between">
                        <!-- Cabecera de la Tarjeta -->
                        <div class="p-5 border-b border-stone-100 dark:border-stone-850 flex flex-col sm:flex-row items-start sm:justify-between gap-3 sm:gap-4">
                            <div class="space-y-1">
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-500">Orden de Trabajo</span>
                                <h3 class="text-lg font-bold text-stone-900 dark:text-white">{{ pedido.codigo || `#${pedido.id}` }}</h3>
                            </div>
                            <span :class="`inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold capitalize shrink-0 self-start ${pedido.estado === 'entregado' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : pedido.estado === 'proceso' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'}`">
                                {{ pedido.estado === 'proceso' ? 'En proceso' : pedido.estado }}
                            </span>
                        </div>

                        <!-- Cuerpo de la Tarjeta -->
                        <div class="p-5 space-y-4 flex-1">
                            <!-- Fechas y Precio -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                <div class="space-y-1">
                                    <span class="text-muted-foreground block">Fecha de Registro</span>
                                    <span class="font-medium text-stone-800 dark:text-stone-200">
                                        {{ pedido.fecha_inicio ? new Date(pedido.fecha_inicio).toLocaleDateString() : 'Pendiente' }}
                                    </span>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-muted-foreground block">Entrega Estimada</span>
                                    <span class="font-medium text-stone-800 dark:text-stone-200">
                                        {{ pedido.fecha_fin_estimada ? new Date(pedido.fecha_fin_estimada).toLocaleDateString() : 'Pendiente' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Requerimiento principal -->
                            <div v-if="pedido.cotizacion?.descripcion" class="bg-stone-50 dark:bg-stone-900/50 p-3 rounded-lg text-xs border border-stone-100 dark:border-stone-850">
                                <span class="font-semibold text-stone-700 dark:text-stone-300 block mb-1">Descripción:</span>
                                <p class="text-stone-600 dark:text-stone-400 line-clamp-2">{{ pedido.cotizacion.descripcion }}</p>
                            </div>

                            <!-- Detalles / Items a Fabricar -->
                            <div class="space-y-2">
                                <span class="text-xs font-bold text-stone-700 dark:text-stone-300 block">Detalle de Fabricación:</span>
                                <ul class="divide-y divide-stone-100 dark:divide-stone-850 border border-stone-100 dark:border-stone-850 rounded-lg overflow-hidden bg-stone-50/50 dark:bg-stone-900/20">
                                    <li v-for="detalle in pedido.detalle_pedidos" :key="detalle.id" class="p-3 text-xs flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <!-- Miniatura de Imagen de Producto -->
                                            <div class="h-10 w-10 rounded bg-muted border border-stone-200 dark:border-stone-800 overflow-hidden shrink-0 flex items-center justify-center">
                                                <img 
                                                    v-if="detalle.producto && detalle.producto.imagenes && detalle.producto.imagenes.length > 0" 
                                                    :src="detalle.producto.imagenes[0].URL" 
                                                    :alt="detalle.producto.nombre" 
                                                    class="h-full w-full object-cover"
                                                />
                                                <Hammer v-else class="h-5 w-5 text-muted-foreground/60" />
                                            </div>
                                            
                                            <div class="min-w-0 flex-1">
                                                <span class="font-semibold text-stone-800 dark:text-stone-200 block truncate">
                                                    {{ detalle.cantidad }}x {{ detalle.producto ? detalle.producto.nombre : 'Mueble a Medida' }}
                                                </span>
                                                <p class="text-muted-foreground text-[10px] mt-0.5 line-clamp-1 truncate">
                                                    {{ detalle.descripcion || 'Trabajo personalizado' }}
                                                </p>
                                            </div>
                                        </div>
                                        <span :class="`inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide shrink-0 ${detalle.estado === 'entregado' || detalle.estado === 'Finalizado' ? 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-400' : 'bg-stone-100 text-stone-600 dark:bg-stone-800 dark:text-stone-400'}`">
                                            {{ detalle.estado }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Pie de la Tarjeta -->
                        <div class="p-5 border-t border-stone-100 dark:border-stone-850 bg-stone-50/40 dark:bg-stone-900/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="text-xs">
                                <span class="text-muted-foreground">Inversión Total:</span>
                                <div class="text-lg font-extrabold text-amber-600 dark:text-amber-500">Bs. {{ parseFloat(pedido.precio).toFixed(2) }}</div>
                            </div>
                            <button @click="openDetails(pedido)" class="inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-stone-700 dark:text-stone-300 bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 px-4 py-2 rounded-lg hover:bg-stone-50 dark:hover:bg-stone-750 transition-colors shadow-sm cursor-pointer whitespace-nowrap w-full sm:w-auto">
                                <Eye class="h-4 w-4" />
                                Ver Detalles
                            </button>
                        </div>
                    </div>
                </div>
                <Pagination :links="pedidos.links" />
            </div>

            <!-- VISTA DE TABLA PARA ADMIN Y CARPINTERO -->
            <div v-else class="rounded-md border border-sidebar-border bg-card text-card-foreground shadow flex flex-col">
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="border-b border-sidebar-border bg-muted/50">
                            <tr class="text-left font-medium text-muted-foreground">
                                <th class="p-4">Código</th>
                                <th class="p-4">Cliente</th>
                                <th class="p-4">Fecha Inicio</th>
                                <th class="p-4">Entrega Estimada</th>
                                <th class="p-4">Estado</th>
                                <th class="p-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="pedidos.data.length === 0">
                                <td colspan="6" class="p-4 text-center text-muted-foreground">
                                    No se encontraron pedidos.
                                </td>
                            </tr>
                            <tr v-for="pedido in pedidos.data" :key="pedido.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold text-muted-foreground">{{ pedido.codigo || `#${pedido.id}` }}</td>
                                <td class="p-4 font-medium">{{ pedido.cliente_real?.usuario?.nombre }} {{ pedido.cliente_real?.usuario?.apellido }}</td>
                                <td class="p-4">{{ pedido.fecha_inicio ? new Date(pedido.fecha_inicio).toLocaleDateString() : 'Por definir' }}</td>
                                <td class="p-4">{{ pedido.fecha_fin_estimada ? new Date(pedido.fecha_fin_estimada).toLocaleDateString() : 'Por definir' }}</td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${pedido.estado === 'entregado' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : pedido.estado === 'proceso' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'}`">
                                        {{ pedido.estado }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <button @click="openDetails(pedido)" class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-950/50 px-3 py-1.5 rounded-md transition-colors">
                                        <Eye class="h-3.5 w-3.5" />
                                        Detalles
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <Pagination :links="pedidos.links" />
            </div>
        </div>

        <!-- Modal Detalles del Pedido -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-4xl rounded-xl bg-background shadow-2xl border border-border flex flex-col max-h-[90vh]">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-border p-5 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-500">
                            <Hammer class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-foreground flex items-center gap-2">
                                Detalles de Producción
                            </h3>
                            <p class="text-sm text-muted-foreground mt-0.5">Pedido {{ selectedPedido?.codigo || `#${selectedPedido?.id}` }} - {{ selectedPedido?.cliente_real?.usuario?.nombre }} {{ selectedPedido?.cliente_real?.usuario?.apellido }}</p>
                        </div>
                    </div>
                    <button @click="closeModal" class="rounded-full p-2 text-muted-foreground hover:bg-muted hover:text-foreground transition-colors">
                        <X class="h-5 w-5" />
                    </button>
                </div>
                
                <!-- Body -->
                <div class="p-5 overflow-y-auto flex-1 space-y-6" v-if="selectedPedido">
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-muted/30 p-4 rounded-lg text-sm">
                        <div>
                            <span class="text-muted-foreground block text-xs">Estado Global</span>
                            <span :class="`font-bold ${selectedPedido.estado === 'entregado' ? 'text-green-600' : selectedPedido.estado === 'proceso' ? 'text-blue-600' : 'text-amber-600'}`">{{ selectedPedido.estado.toUpperCase() }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block text-xs">Monto Estimado</span>
                            <span class="font-medium">Bs. {{ parseFloat(selectedPedido.precio).toFixed(2) }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block text-xs">Fecha de Inicio</span>
                            <span class="font-medium">{{ selectedPedido.fecha_inicio ? new Date(selectedPedido.fecha_inicio).toLocaleDateString() : 'Pendiente' }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block text-xs">Entrega Estimada</span>
                            <span class="font-medium">{{ selectedPedido.fecha_fin_estimada ? new Date(selectedPedido.fecha_fin_estimada).toLocaleDateString() : 'Pendiente' }}</span>
                        </div>
                    </div>

                    <div v-if="selectedPedido.cotizacion?.descripcion" class="text-sm">
                        <span class="font-bold block mb-1">Notas del cliente / Cotización:</span>
                        <p class="text-muted-foreground border-l-2 border-border pl-3">{{ selectedPedido.cotizacion?.descripcion }}</p>
                    </div>

                    <!-- Detalles del Pedido -->
                    <div>
                        <h4 class="font-bold text-lg mb-3">Items a Fabricar</h4>
                        <div class="rounded-md border border-sidebar-border bg-background shadow-sm overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b">
                                    <tr class="text-left font-medium text-muted-foreground">
                                        <th class="p-3 w-16 text-center">Imagen</th>
                                        <th class="p-3 w-12 text-center">Cant.</th>
                                        <th class="p-3">Producto / Mueble</th>
                                        <th class="p-3">Descripción Técnica</th>
                                        <th class="p-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-sidebar-border">
                                    <tr v-if="!selectedPedido.detalle_pedidos || selectedPedido.detalle_pedidos.length === 0">
                                        <td colspan="5" class="p-4 text-center text-muted-foreground">
                                            No hay detalles registrados para este pedido.
                                        </td>
                                    </tr>
                                    <tr v-for="detalle in selectedPedido.detalle_pedidos" :key="detalle.id" class="hover:bg-muted/30 transition-colors">
                                        <td class="p-3 text-center">
                                            <div class="h-10 w-10 rounded bg-muted border border-stone-255 dark:border-stone-745 overflow-hidden mx-auto flex items-center justify-center">
                                                <img 
                                                    v-if="detalle.producto && detalle.producto.imagenes && detalle.producto.imagenes.length > 0" 
                                                    :src="detalle.producto.imagenes[0].URL" 
                                                    :alt="detalle.producto.nombre" 
                                                    class="h-full w-full object-cover"
                                                />
                                                <Hammer v-else class="h-4 w-4 text-muted-foreground" />
                                            </div>
                                        </td>
                                        <td class="p-3 text-center font-bold">{{ detalle.cantidad }}x</td>
                                        <td class="p-3 font-semibold">{{ detalle.producto ? detalle.producto.nombre : 'Mueble a Medida' }}</td>
                                        <td class="p-3 text-muted-foreground text-xs">{{ detalle.descripcion || 'Sin descripción adicional' }}</td>
                                        <td class="p-3">
                                            <span :class="`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide uppercase ${detalle.estado === 'entregado' || detalle.estado === 'Finalizado' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : detalle.estado === 'En proceso' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' : 'bg-stone-100 text-stone-800 dark:bg-stone-800 dark:text-stone-300'}`">
                                                {{ detalle.estado }}
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
