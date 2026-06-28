<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { RefreshCw, Plus, Edit, Trash2, Eye } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

interface Rol {
    id: number;
    nombre: string;
    estado: string;
    created_at: string;
}

interface PaginatedRoles {
    data: Rol[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    roles: PaginatedRoles;
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Roles', href: '/roles' },
];

const searchQuery = ref(props.filters?.search || '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/roles', { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const isRefreshing = ref(false);
const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['roles'],
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

const page = usePage();
const currentUserRole = computed(() => (page.props as any).auth.user.rol_id);

const confirmOpen = ref(false);
const pendingDeleteId = ref<number | null>(null);

const confirmDelete = () => {
    if (pendingDeleteId.value !== null) {
        router.delete(`/roles/${pendingDeleteId.value}`, { preserveScroll: true });
        confirmOpen.value = false;
        pendingDeleteId.value = null;
    }
};

const deleteRole = (id: number) => {
    pendingDeleteId.value = id;
    confirmOpen.value = true;
};
</script>

<template>
    <Head title="Roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Gestión de Roles</h1>
                    <p class="text-sm text-muted-foreground">Administra los roles del sistema y sus permisos asignados.</p>
                </div>
                <Link
                    v-if="currentUserRole === 1"
                    href="/roles/create"
                    class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 transition-colors"
                >
                    <Plus class="mr-2 h-4 w-4" /> Nuevo Rol
                </Link>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por nombre de rol..."
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
                                <th class="p-4">Nombre del Rol</th>
                                <th class="p-4">Estado</th>
                                <th class="p-4 text-right" v-if="currentUserRole === 1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="roles.data.length === 0">
                                <td colspan="4" class="p-4 text-center text-muted-foreground">
                                    No se encontraron roles.
                                </td>
                            </tr>
                            <tr v-for="rol in roles.data" :key="rol.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#{{ rol.id }}</td>
                                <td class="p-4 font-medium">{{ rol.nombre }}</td>
                                <td class="p-4">
                                    <span 
                                        class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                        :class="rol.estado === 'activo' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/30 dark:text-red-400'"
                                    >
                                        {{ rol.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="p-4 text-right" v-if="currentUserRole === 1">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="`/roles/${rol.id}`" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-3 py-1.5 text-xs font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground" title="Ver Permisos">
                                            <Eye class="h-3.5 w-3.5" />
                                        </Link>
                                        <Link :href="`/roles/${rol.id}/edit`" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-3 py-1.5 text-xs font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground">
                                            <Edit class="h-3.5 w-3.5" />
                                        </Link>
                                        <button @click="deleteRole(rol.id)" class="inline-flex items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-600 px-3 py-1.5 text-xs font-medium shadow-sm transition-colors hover:bg-red-100" :disabled="rol.id === 1" :class="{'opacity-50 cursor-not-allowed': rol.id === 1}">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="roles.links" />
            </div>
        </div>
        
        <ConfirmDialog 
            v-model:open="confirmOpen" 
            title="Eliminar Rol"
            message="¿Estás seguro de eliminar este rol? Esta acción no se puede deshacer."
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
