<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { RefreshCw, Plus, Edit, Trash2, Image as ImageIcon } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';

interface Tipo {
    id: number;
    nombre: string;
}

interface Imagen {
    id: number;
    URL: string;
}

interface Producto {
    id: number;
    nombre: string;
    cantidad: number;
    precio: string;
    descripcion: string;
    estado: string;
    tipo_id: number;
    tipo: Tipo;
    imagenes: Imagen[];
}

interface PaginatedProductos {
    data: Producto[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    productos: PaginatedProductos;
    tipos: Tipo[];
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Productos', href: '/productos' },
];

const searchQuery = ref(props.filters?.search || '');
const isRefreshing = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/productos', { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['productos'],
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
    tipo_id: '',
    nombre: '',
    cantidad: 0,
    precio: 0,
    descripcion: '',
    estado: 'disponible',
    imagen: null as File | null,
});

const openCreateModal = () => {
    isEditing.value = false;
    currentId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (producto: Producto) => {
    isEditing.value = true;
    currentId.value = producto.id;
    form.clearErrors();
    form.tipo_id = producto.tipo_id.toString();
    form.nombre = producto.nombre;
    form.cantidad = producto.cantidad;
    form.precio = parseFloat(producto.precio);
    form.descripcion = producto.descripcion;
    form.estado = producto.estado;
    form.imagen = null;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.imagen = target.files[0];
    }
};

const saveProducto = () => {
    if (isEditing.value && currentId.value) {
        // Para simular PUT con FormData en Laravel Inertia
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(`/productos/${currentId.value}`, {
            onSuccess: () => closeModal(),
            forceFormData: true,
        });
    } else {
        form.post('/productos', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteProducto = (id: number) => {
    if (confirm('¿Estás seguro de eliminar este producto?')) {
        router.delete(`/productos/${id}`);
    }
};
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
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md bg-stone-900 px-4 h-9 text-xs font-medium text-white shadow-sm transition-colors hover:bg-stone-800 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200"
                >
                    <Plus class="h-4 w-4" />
                    Nuevo Producto
                </button>
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
                                <th class="p-4 w-16">Imagen</th>
                                <th class="p-4">Producto</th>
                                <th class="p-4">Categoría</th>
                                <th class="p-4">Precio</th>
                                <th class="p-4">Stock</th>
                                <th class="p-4">Estado</th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="productos.data.length === 0">
                                <td colspan="7" class="p-4 text-center text-muted-foreground">
                                    No se encontraron productos.
                                </td>
                            </tr>
                            <tr v-for="producto in productos.data" :key="producto.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4">
                                    <div v-if="producto.imagenes && producto.imagenes.length > 0" class="h-10 w-10 rounded overflow-hidden bg-muted">
                                        <img :src="producto.imagenes[0].URL" alt="img" class="h-full w-full object-cover" />
                                    </div>
                                    <div v-else class="h-10 w-10 rounded bg-muted flex items-center justify-center text-muted-foreground">
                                        <ImageIcon class="h-4 w-4" />
                                    </div>
                                </td>
                                <td class="p-4 font-semibold">{{ producto.nombre }}</td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-400/20">
                                        {{ producto.tipo?.nombre || 'N/A' }}
                                    </span>
                                </td>
                                <td class="p-4 font-semibold text-amber-600 dark:text-amber-500">
                                    Bs. {{ parseFloat(producto.precio).toFixed(2) }}
                                </td>
                                <td class="p-4">{{ producto.cantidad }} uds.</td>
                                <td class="p-4">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${producto.estado === 'disponible' || producto.estado === 'activo' ? 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300'}`">
                                        {{ producto.estado }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <button @click="openEditModal(producto)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                        <Edit class="h-4 w-4 inline" />
                                    </button>
                                    <button @click="deleteProducto(producto.id)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <Trash2 class="h-4 w-4 inline" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="productos.links" />
            </div>
        </div>

        <!-- CRUD Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 p-4">
            <div class="relative w-full max-w-lg rounded-lg bg-background shadow-xl">
                <div class="flex items-center justify-between border-b border-border p-4">
                    <h3 class="text-lg font-semibold text-foreground">
                        {{ isEditing ? 'Editar Producto' : 'Nuevo Producto' }}
                    </h3>
                    <button @click="closeModal" class="text-muted-foreground hover:text-foreground">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="saveProducto" class="p-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="mb-1 block text-sm font-medium text-foreground">Nombre</label>
                            <input v-model="form.nombre" type="text" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required />
                            <span v-if="form.errors.nombre" class="text-xs text-red-500">{{ form.errors.nombre }}</span>
                        </div>
                        
                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Categoría (Tipo)</label>
                            <select v-model="form.tipo_id" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required>
                                <option value="" disabled>Seleccione...</option>
                                <option v-for="tipo in tipos" :key="tipo.id" :value="tipo.id">{{ tipo.nombre }}</option>
                            </select>
                            <span v-if="form.errors.tipo_id" class="text-xs text-red-500">{{ form.errors.tipo_id }}</span>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Estado</label>
                            <select v-model="form.estado" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required>
                                <option value="disponible">Disponible</option>
                                <option value="agotado">Agotado</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                            <span v-if="form.errors.estado" class="text-xs text-red-500">{{ form.errors.estado }}</span>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Precio (Bs.)</label>
                            <input v-model="form.precio" type="number" step="0.01" min="0" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required />
                            <span v-if="form.errors.precio" class="text-xs text-red-500">{{ form.errors.precio }}</span>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-foreground">Stock (Cantidad)</label>
                            <input v-model="form.cantidad" type="number" min="0" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required />
                            <span v-if="form.errors.cantidad" class="text-xs text-red-500">{{ form.errors.cantidad }}</span>
                        </div>

                        <div class="col-span-2">
                            <label class="mb-1 block text-sm font-medium text-foreground">Descripción</label>
                            <textarea v-model="form.descripcion" rows="3" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required></textarea>
                            <span v-if="form.errors.descripcion" class="text-xs text-red-500">{{ form.errors.descripcion }}</span>
                        </div>

                        <div class="col-span-2">
                            <label class="mb-1 block text-sm font-medium text-foreground">Imagen del Producto (Opcional)</label>
                            <input type="file" accept="image/*" @change="handleFileChange" class="w-full text-sm text-muted-foreground file:mr-4 file:rounded-md file:border-0 file:bg-muted file:px-4 file:py-2 file:text-sm file:font-semibold file:text-foreground hover:file:bg-muted/80" />
                            <span v-if="form.errors.imagen" class="text-xs text-red-500">{{ form.errors.imagen }}</span>
                            <p v-if="isEditing" class="text-xs text-muted-foreground mt-1">Si subes una nueva imagen, reemplazará a la existente.</p>
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
    </AppLayout>
</template>
