<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { RefreshCw } from 'lucide-vue-next';

interface Tipo {
    id: number;
    nombre: string;
}

interface Producto {
    id: number;
    nombre: string;
    cantidad: number;
    precio: string;
    descripcion: string;
    estado: string;
    tipo: Tipo;
}

const props = defineProps<{
    productos: Producto[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Productos', href: '/productos' },
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

const filteredProductos = computed(() => {
    return props.productos.filter((p) => {
        const name = p.nombre.toLowerCase();
        const desc = p.descripcion.toLowerCase();
        const type = p.tipo.nombre.toLowerCase();
        const query = searchQuery.value.toLowerCase();
        return name.includes(query) || desc.includes(query) || type.includes(query);
    });
});
</script>

<template>
    <Head title="Productos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Catálogo de Productos</h1>
                    <p class="text-sm text-muted-foreground">Productos de madera terminados en inventario y listos para venta.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por nombre, descripción o categoría..."
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
                                <th class="p-4">Producto</th>
                                <th class="p-4">Categoría</th>
                                <th class="p-4">Descripción</th>
                                <th class="p-4">Precio</th>
                                <th class="p-4">Stock</th>
                                <th class="p-4">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="filteredProductos.length === 0">
                                <td colspan="6" class="p-4 text-center text-muted-foreground">
                                    No se encontraron productos.
                                </td>
                            </tr>
                            <tr v-for="producto in filteredProductos" :key="producto.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">{{ producto.nombre }}</td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-stone-100 text-stone-800 dark:bg-stone-850 dark:text-stone-300">
                                        {{ producto.tipo.nombre }}
                                    </span>
                                </td>
                                <td class="p-4 max-w-xs truncate">{{ producto.descripcion }}</td>
                                <td class="p-4 font-semibold text-amber-600 dark:text-amber-500">
                                    Bs. {{ parseFloat(producto.precio).toFixed(2) }}
                                </td>
                                <td class="p-4">{{ producto.cantidad }} uds.</td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${producto.estado === 'disponible' || producto.estado === 'activo' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300'}`">
                                        {{ producto.estado }}
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
