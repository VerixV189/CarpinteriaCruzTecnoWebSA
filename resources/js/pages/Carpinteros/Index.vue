<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { RefreshCw, Plus, Edit, Trash2 } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ReportExportButton from '@/components/ReportExportButton.vue';

interface Usuario {
    id: number;
    nombre: string;
    apellido: string;
    email: string;
}

interface Carpintero {
    id: number;
    especialidad: string;
    costo_hora: string;
    usuario: Usuario;
}

interface PaginatedCarpinteros {
    data: Carpintero[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    carpinteros: PaginatedCarpinteros;
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Carpinteros', href: route('carpinteros.index') },
];

const searchQuery = ref(props.filters?.search || '');

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('carpinteros.index'), { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const isRefreshing = ref(false);
const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['carpinteros'],
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
        router.delete(route('carpinteros.destroy', pendingDeleteId.value), { preserveScroll: true });
        confirmOpen.value = false;
        pendingDeleteId.value = null;
    }
};

const deleteCarpintero = (id: number) => {
    pendingDeleteId.value = id;
    confirmOpen.value = true;
};
</script>

<template>
    <Head title="Carpinteros" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Gestión de Carpinteros</h1>
                    <p class="text-sm text-muted-foreground">Listado de carpinteros del taller, especialidad y tarifas.</p>
                </div>
                <Link
                    v-if="currentUserRole === 1"
                    :href="route('carpinteros.create')"
                    class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 transition-colors"
                >
                    <Plus class="mr-2 h-4 w-4" /> Nuevo Carpintero
                </Link>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por nombre o especialidad..."
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
                <ReportExportButton
                    :data="carpinteros.data"
                    :headers="['ID', 'Nombre Completo', 'Especialidad', 'Costo Hora']"
                    :keys="[
                        'id',
                        (item) => `${item.usuario.nombre} ${item.usuario.apellido}`,
                        'especialidad',
                        (item) => `Bs. ${parseFloat(item.costo_hora).toFixed(2)}`
                    ]"
                    filename="reporte-carpinteros"
                    title="Reporte de Carpinteros"
                />
            </div>

            <div class="rounded-md border border-sidebar-border bg-card text-card-foreground shadow">
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="border-b border-sidebar-border bg-muted/50">
                            <tr class="text-left font-medium text-muted-foreground">
                                <th class="p-4">Nombre Completo</th>
                                <th class="p-4">Email</th>
                                <th class="p-4">Especialidad</th>
                                <th class="p-4">Costo por Hora</th>
                                <th class="p-4 text-right" v-if="currentUserRole === 1">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="carpinteros.data.length === 0">
                                <td colspan="4" class="p-4 text-center text-muted-foreground">
                                    No se encontraron carpinteros.
                                </td>
                            </tr>
                            <tr v-for="carpintero in carpinteros.data" :key="carpintero.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-medium">{{ carpintero.usuario.nombre }} {{ carpintero.usuario.apellido }}</td>
                                <td class="p-4">{{ carpintero.usuario.email }}</td>
                                <td class="p-4">{{ carpintero.especialidad }}</td>
                                <td class="p-4 font-semibold text-amber-600 dark:text-amber-500">
                                    Bs. {{ parseFloat(carpintero.costo_hora).toFixed(2) }}
                                </td>
                                <td class="p-4 text-right whitespace-nowrap" v-if="currentUserRole === 1">
                                    <Link :href="route('carpinteros.edit', carpintero.id)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                        <Edit class="h-4 w-4 inline" />
                                    </Link>
                                        <button @click="deleteCarpintero(carpintero.id)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <Trash2 class="h-4 w-4 inline" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="carpinteros.links" />
            </div>
        </div>
        
        <ConfirmDialog 
            v-model:open="confirmOpen" 
            title="Eliminar Carpintero"
            message="¿Estás seguro de eliminar este carpintero? Esta acción no se puede deshacer."
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
