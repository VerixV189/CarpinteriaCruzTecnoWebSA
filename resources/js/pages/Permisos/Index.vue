<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { RefreshCw } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';

interface Permiso {
    id: number;
    nombre: string;
    created_at: string;
}

interface PaginatedPermisos {
    data: Permiso[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    permisos: PaginatedPermisos;
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Permisos', href: '/permisos' },
];

const searchQuery = ref(props.filters?.search || '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/permisos', { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const isRefreshing = ref(false);
const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['permisos'],
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};
</script>

<template>
    <Head title="Permisos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Gestión de Permisos</h1>
                    <p class="text-sm text-muted-foreground">Listado de accesos y permisos del sistema.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por nombre de permiso..."
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
                                <th class="p-4">Nombre del Permiso</th>
                                <th class="p-4">Fecha Creación</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="permisos.data.length === 0">
                                <td colspan="3" class="p-4 text-center text-muted-foreground">
                                    No se encontraron permisos.
                                </td>
                            </tr>
                            <tr v-for="permiso in permisos.data" :key="permiso.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#{{ permiso.id }}</td>
                                <td class="p-4 font-medium">
                                    <code class="px-1.5 py-0.5 rounded bg-muted text-xs font-mono">
                                        {{ permiso.nombre }}
                                    </code>
                                </td>
                                <td class="p-4">{{ new Date(permiso.created_at).toLocaleDateString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="permisos.links" />
            </div>
        </div>
    </AppLayout>
</template>
