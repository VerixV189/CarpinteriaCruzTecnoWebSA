<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { RefreshCw, Edit, Trash2 } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

interface Proveedor {
    id: number;
    nombre_empresa: string;
}

interface Insumo {
    id: number;
    nombre: string;
    proveedor: Proveedor;
}

interface PaginatedInsumos {
    data: Insumo[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    insumos: PaginatedInsumos;
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Insumos', href: '/insumos' },
];

const searchQuery = ref(props.filters?.search || '');
const isRefreshing = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/insumos', { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['insumos'],
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

const confirmOpen = ref(false);
const pendingDeleteId = ref<number | null>(null);

const confirmDelete = () => {
    if (pendingDeleteId.value !== null) {
        router.delete(`/insumos/${pendingDeleteId.value}`, {
            preserveScroll: true,
        });
        confirmOpen.value = false;
        pendingDeleteId.value = null;
    }
};

const deleteInsumo = (id: number) => {
    pendingDeleteId.value = id;
    confirmOpen.value = true;
};
</script>

<template>
    <Head title="Insumos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Gestión de Insumos</h1>
                    <p class="text-sm text-muted-foreground">Listado de insumos y materiales y sus respectivos proveedores.</p>
                </div>
                <Link href="/insumos/create" class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 transition-colors">
                    Crear Insumo
                </Link>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por nombre de insumo o proveedor..."
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
                                <th class="p-4">Nombre Insumo</th>
                                <th class="p-4">Proveedor</th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="insumos.data.length === 0">
                                <td colspan="4" class="p-4 text-center text-muted-foreground">
                                    No se encontraron insumos.
                                </td>
                            </tr>
                            <tr v-for="insumo in insumos.data" :key="insumo.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#{{ insumo.id }}</td>
                                <td class="p-4 font-medium">{{ insumo.nombre }}</td>
                                <td class="p-4">{{ insumo.proveedor.nombre_empresa }}</td>
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="`/insumos/${insumo.id}/edit`" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-3 py-1.5 text-xs font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground">
                                            <Edit class="h-3.5 w-3.5" />
                                        </Link>
                                        <button @click="deleteInsumo(insumo.id)" class="inline-flex items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-600 px-3 py-1.5 text-xs font-medium shadow-sm transition-colors hover:bg-red-100">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="insumos.links" />
            </div>
        </div>
        
        <ConfirmDialog 
            v-model:open="confirmOpen" 
            title="Eliminar Insumo"
            message="¿Está seguro de que desea eliminar este insumo? Esta acción no se puede deshacer."
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
