<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { RefreshCw, Plus, Edit, Trash2 } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ReportExportButton from '@/components/ReportExportButton.vue';

interface Tipo {
    id: number;
    nombre: string;
    descripcion: string;
    estado: string;
}

interface PaginatedTipos {
    data: Tipo[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    tipos: PaginatedTipos;
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Tipos de Mueble', href: route('tipos.index') },
];

const searchQuery = ref(props.filters?.search || '');
const isRefreshing = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('tipos.index'), { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['tipos'],
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

// Modal State
const isModalOpen = ref(false);
const isEditing = ref(false);
const currentId = ref<number | null>(null);

const form = useForm({
    nombre: '',
    descripcion: '',
    estado: 'activo',
});

const openCreateModal = () => {
    isEditing.value = false;
    currentId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (tipo: Tipo) => {
    isEditing.value = true;
    currentId.value = tipo.id;
    form.clearErrors();
    form.nombre = tipo.nombre;
    form.descripcion = tipo.descripcion;
    form.estado = tipo.estado;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const saveTipo = () => {
    if (isEditing.value && currentId.value) {
        form.put(route('tipos.update', currentId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('tipos.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmOpen = ref(false);
const pendingDeleteId = ref<number | null>(null);

const confirmDelete = () => {
    if (pendingDeleteId.value !== null) {
        router.delete(route('tipos.destroy', pendingDeleteId.value), {
            preserveScroll: true
        });
        confirmOpen.value = false;
        pendingDeleteId.value = null;
    }
};

const deleteTipo = (id: number) => {
    pendingDeleteId.value = id;
    confirmOpen.value = true;
};
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
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md bg-stone-900 px-4 h-9 text-xs font-medium text-white shadow-sm transition-colors hover:bg-stone-800 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200"
                >
                    <Plus class="h-4 w-4" />
                    Nuevo Tipo
                </button>
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
                <ReportExportButton
                    :data="tipos.data"
                    :headers="['ID', 'Nombre', 'Descripción', 'Estado']"
                    :keys="['id', 'nombre', 'descripcion', 'estado']"
                    filename="reporte-tipos-mueble"
                    title="Reporte de Tipos de Mueble"
                />
            </div>

            <div class="rounded-md border border-sidebar-border bg-card text-card-foreground shadow">
                <div class="relative w-full overflow-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="border-b border-sidebar-border bg-muted/50">
                            <tr class="text-left font-medium text-muted-foreground">
                                <th class="p-4 w-16">ID</th>
                                <th class="p-4">Nombre de la Categoría</th>
                                <th class="p-4 w-1/2">Descripción</th>
                                <th class="p-4">Estado</th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="tipos.data.length === 0">
                                <td colspan="5" class="p-4 text-center text-muted-foreground">
                                    No se encontraron tipos de mueble.
                                </td>
                            </tr>
                            <tr v-for="tipo in tipos.data" :key="tipo.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold text-muted-foreground">#{{ tipo.id }}</td>
                                <td class="p-4 font-medium">{{ tipo.nombre }}</td>
                                <td class="p-4 truncate max-w-md" :title="tipo.descripcion">{{ tipo.descripcion }}</td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${tipo.estado === 'activo' || tipo.estado === 'Activo' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300'}`">
                                        {{ tipo.estado }}
                                    </span>
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <button @click="openEditModal(tipo)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                        <Edit class="h-4 w-4 inline" />
                                    </button>
                                    <button @click="deleteTipo(tipo.id)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <Trash2 class="h-4 w-4 inline" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="tipos.links" />
            </div>
        </div>

        <!-- CRUD Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 p-4">
            <div class="relative w-full max-w-md rounded-lg bg-background shadow-xl">
                <div class="flex items-center justify-between border-b border-border p-4">
                    <h3 class="text-lg font-semibold text-foreground">
                        {{ isEditing ? 'Editar Tipo de Mueble' : 'Nuevo Tipo de Mueble' }}
                    </h3>
                    <button @click="closeModal" class="text-muted-foreground hover:text-foreground">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="saveTipo" class="p-4 space-y-4">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Nombre de Categoría</label>
                            <input v-model="form.nombre" type="text" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required />
                            <span v-if="form.errors.nombre" class="text-xs text-red-500">{{ form.errors.nombre }}</span>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Estado</label>
                            <select v-model="form.estado" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                            <span v-if="form.errors.estado" class="text-xs text-red-500">{{ form.errors.estado }}</span>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Descripción</label>
                            <textarea v-model="form.descripcion" rows="4" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required></textarea>
                            <span v-if="form.errors.descripcion" class="text-xs text-red-500">{{ form.errors.descripcion }}</span>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeModal" class="rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-muted">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="form.processing" class="rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-stone-800 disabled:opacity-50 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200">
                            {{ form.processing ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <ConfirmDialog 
            v-model:open="confirmOpen" 
            title="Eliminar Tipo de Mueble"
            message="¿Estás seguro de eliminar este tipo de mueble? Esta acción no se puede deshacer."
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
