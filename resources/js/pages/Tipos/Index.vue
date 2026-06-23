<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { RefreshCw } from 'lucide-vue-next';

interface Tipo {
    id: number;
    nombre: string;
    descripcion: string;
    estado: string;
}

const props = defineProps<{
    tipos: Tipo[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tipos de Mueble', href: '/tipos' },
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

const filteredTipos = computed(() => {
    return props.tipos.filter((t) => {
        const name = t.nombre.toLowerCase();
        const desc = t.descripcion.toLowerCase();
        const query = searchQuery.value.toLowerCase();
        return name.includes(query) || desc.includes(query);
    });
});
</script>

<template>
    <Head title="Tipos de Mueble" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Tipos de Mueble</h1>
                    <p class="text-sm text-muted-foreground">Categorías de productos y tipos de trabajos de carpintería realizados.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por nombre o descripción..."
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
                                <th class="p-4">Nombre de la Categoría</th>
                                <th class="p-4">Descripción</th>
                                <th class="p-4">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="filteredTipos.length === 0">
                                <td colspan="4" class="p-4 text-center text-muted-foreground">
                                    No se encontraron tipos de mueble.
                                </td>
                            </tr>
                            <tr v-for="tipo in filteredTipos" :key="tipo.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#{{ tipo.id }}</td>
                                <td class="p-4 font-medium">{{ tipo.nombre }}</td>
                                <td class="p-4 max-w-xs truncate">{{ tipo.descripcion }}</td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${tipo.estado === 'activo' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300'}`">
                                        {{ tipo.estado }}
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
