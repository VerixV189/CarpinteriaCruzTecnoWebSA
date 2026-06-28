<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { RefreshCw, Plus, Edit, Trash2 } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

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

const page = usePage();
const currentUserRole = computed(() => page.props.auth.user.rol_id);

const confirmOpen = ref(false);
const pendingDeleteId = ref<number | null>(null);

const confirmDelete = () => {
    if (pendingDeleteId.value !== null) {
        router.delete(`/permisos/${pendingDeleteId.value}`, { preserveScroll: true });
        confirmOpen.value = false;
        pendingDeleteId.value = null;
    }
};

const deletePermiso = (id: number) => {
    pendingDeleteId.value = id;
    confirmOpen.value = true;
};
</script>

<template>
    <Head title="Permisos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Gestión de Permisos</h1>
                    <p class="text-sm text-muted-foreground">Listado de permisos del sistema que pueden ser asignados a los roles.</p>
                </div>
                <Link
                    v-if="currentUserRole === 1"
                    href="/permisos/create"
                    class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 transition-colors"
                >
                    <Plus class="mr-2 h-4 w-4" /> Nuevo Permiso
                </Link>
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
                                <th class="p-4">Nombre (Código)</th>
                                <th class="p-4">Descripción</th>
                                <th class="p-4 text-right" v-if="currentUserRole === 1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="permisos.data.length === 0">
                                <td colspan="4" class="p-4 text-center text-muted-foreground">
                                    No se encontraron permisos.
                                </td>
                            </tr>
                            <tr v-for="permiso in permisos.data" :key="permiso.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#{{ permiso.id }}</td>
                                <td class="p-4">
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-400/20">
                                        {{ permiso.nombre }}
                                    </span>
                                </td>
                                <td class="p-4">{{ permiso.descripcion }}</td>
                                <td class="p-4 text-right" v-if="currentUserRole === 1">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="`/permisos/${permiso.id}/edit`" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-3 py-1.5 text-xs font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground">
                                            <Edit class="h-3.5 w-3.5" />
                                        </Link>
                                        <button @click="deletePermiso(permiso.id)" class="inline-flex items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-600 px-3 py-1.5 text-xs font-medium shadow-sm transition-colors hover:bg-red-100">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="permisos.links" />
            </div>
        </div>
        
        <ConfirmDialog 
            v-model:open="confirmOpen" 
            title="Eliminar Permiso"
            message="¿Estás seguro de eliminar este permiso? Puede afectar el sistema. Esta acción no se puede deshacer."
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
