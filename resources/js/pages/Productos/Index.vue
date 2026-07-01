<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { RefreshCw, Plus, Edit, Trash2, Image as ImageIcon, Boxes, X } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ReportExportButton from '@/components/ReportExportButton.vue';

interface Tipo {
    id: number;
    nombre: string;
}

interface Imagen {
    id: number;
    URL: string;
}

interface Insumo {
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
    tipo_id: number;
    tipo: Tipo;
    imagenes: Imagen[];
    insumos?: Insumo[];
}

interface PaginatedProductos {
    data: Producto[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    productos: PaginatedProductos;
    tipos: Tipo[];
    insumos: Insumo[];
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Productos', href: route('productos.index') },
];

const searchQuery = ref(props.filters?.search || '');
const isRefreshing = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('productos.index'), { search: value }, {
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
const imagePreview = ref<string | null>(null);

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
    imagePreview.value = null;
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
    imagePreview.value = producto.imagenes && producto.imagenes.length > 0 ? producto.imagenes[0].URL : null;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        
        // Validar tipo de archivo (sólo png o jpg/jpeg)
        if (file.type !== 'image/png' && file.type !== 'image/jpeg' && file.type !== 'image/jpg') {
            alert(`El archivo "${file.name}" no es admitido. Solo se permiten imágenes JPG o PNG.`);
            target.value = ''; // Limpiar input
            return;
        }

        form.imagen = file;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.imagen = null;
    imagePreview.value = null;
    const input = document.getElementById('imagen-producto-input') as HTMLInputElement;
    if (input) input.value = '';
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
        form.post(route('productos.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmOpen = ref(false);
const pendingDeleteId = ref<number | null>(null);

const confirmDelete = () => {
    if (pendingDeleteId.value !== null) {
        router.delete(route('productos.destroy', pendingDeleteId.value));
        confirmOpen.value = false;
        pendingDeleteId.value = null;
    }
};

const deleteProducto = (id: number) => {
    pendingDeleteId.value = id;
    confirmOpen.value = true;
};

// Insumos Modal State and Form
const isInsumosModalOpen = ref(false);
const selectedProductoForInsumos = ref<Producto | null>(null);
const selectedInsumoIds = ref<number[]>([]);
const insumosSearchQuery = ref('');

const checkedInsumos = computed(() => {
    return props.insumos.filter(insumo => {
        const isChecked = selectedInsumoIds.value.includes(insumo.id);
        if (!isChecked) return false;
        if (insumosSearchQuery.value) {
            return insumo.nombre.toLowerCase().includes(insumosSearchQuery.value.toLowerCase());
        }
        return true;
    });
});

const uncheckedInsumos = computed(() => {
    return props.insumos.filter(insumo => {
        const isChecked = selectedInsumoIds.value.includes(insumo.id);
        if (isChecked) return false;
        if (insumosSearchQuery.value) {
            return insumo.nombre.toLowerCase().includes(insumosSearchQuery.value.toLowerCase());
        }
        return true;
    });
});

const insumosForm = useForm({
    insumos: [] as number[]
});

const openInsumosModal = (producto: Producto) => {
    selectedProductoForInsumos.value = producto;
    selectedInsumoIds.value = producto.insumos ? producto.insumos.map(i => i.id) : [];
    insumosSearchQuery.value = '';
    insumosForm.clearErrors();
    isInsumosModalOpen.value = true;
};

const closeInsumosModal = () => {
    isInsumosModalOpen.value = false;
    selectedProductoForInsumos.value = null;
    insumosSearchQuery.value = '';
};

const saveInsumos = () => {
    if (!selectedProductoForInsumos.value) return;
    insumosForm.insumos = selectedInsumoIds.value;
    insumosForm.post(route('productos.insumos.update', { producto: selectedProductoForInsumos.value.id }), {
        preserveScroll: true,
        onSuccess: () => closeInsumosModal(),
    });
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
                <ReportExportButton
                    :data="productos.data"
                    :headers="['ID', 'Nombre', 'Precio', 'Stock', 'Categoría/Tipo', 'Estado']"
                    :keys="[
                        'id',
                        'nombre',
                        (item) => `Bs. ${parseFloat(item.precio).toFixed(2)}`,
                        'cantidad',
                        (item) => item.tipo?.nombre || 'General',
                        'estado'
                    ]"
                    filename="reporte-productos"
                    title="Reporte de Productos en Catálogo"
                />
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
                                    <button @click="openInsumosModal(producto)" class="text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 mr-3 cursor-pointer" title="Asociar Insumos">
                                        <Boxes class="h-4 w-4 inline" />
                                    </button>
                                    <button @click="openEditModal(producto)" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 mr-3 cursor-pointer">
                                        <Edit class="h-4 w-4 inline" />
                                    </button>
                                    <button @click="deleteProducto(producto.id)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 cursor-pointer">
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

                        <div class="col-span-2 space-y-2">
                            <label class="block text-sm font-medium text-foreground">Imagen del Producto (Opcional)</label>
                            
                            <!-- Caja de carga con diseño moderno -->
                            <div class="border-2 border-dashed border-muted-foreground/30 hover:border-primary/50 transition-colors rounded-lg p-5 flex flex-col items-center justify-center gap-2 cursor-pointer relative bg-muted/20">
                                <input
                                    type="file"
                                    id="imagen-producto-input"
                                    accept="image/png, image/jpeg, image/jpg"
                                    @change="handleFileChange"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                />
                                
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                
                                <div class="text-xs font-medium text-foreground text-center">
                                    Haz clic o arrastra para subir una imagen
                                </div>
                                <div class="text-[10px] text-muted-foreground text-center">
                                    Solo se admiten formatos <span class="font-semibold text-foreground">PNG</span> o <span class="font-semibold text-foreground">JPG</span>
                                </div>
                            </div>

                            <!-- Previsualización de la Imagen -->
                            <div v-if="imagePreview" class="relative mt-2 w-32 h-32 rounded-md overflow-hidden border bg-muted group">
                                <img :src="imagePreview" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-black/45 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button 
                                        type="button" 
                                        @click="removeImage" 
                                        class="bg-red-600 hover:bg-red-700 text-white rounded-full p-2 shadow-lg transition-transform hover:scale-110"
                                        title="Eliminar imagen"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                    </button>
                                </div>
                            </div>
                            
                            <span v-if="form.errors.imagen" class="text-xs text-red-500 block">{{ form.errors.imagen }}</span>
                            <p v-if="isEditing" class="text-[11px] text-muted-foreground">Si subes una nueva imagen, reemplazará a la existente.</p>
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

        <!-- Modal: Asociar Insumos -->
        <div v-if="isInsumosModalOpen && selectedProductoForInsumos" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
            <div class="relative w-full max-w-lg rounded-xl bg-card text-card-foreground shadow-xl border border-stone-200 dark:border-stone-800 overflow-hidden">
                <div class="flex items-center justify-between border-b border-border p-4">
                    <div>
                        <h3 class="text-md font-bold text-foreground">
                            Asociar Insumos
                        </h3>
                        <p class="text-xs text-muted-foreground mt-0.5">{{ selectedProductoForInsumos.nombre }}</p>
                    </div>
                    <button @click="closeInsumosModal" class="text-muted-foreground hover:text-foreground cursor-pointer">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="saveInsumos" class="p-6 space-y-4">
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-stone-700 dark:text-stone-300 block">
                            Selecciona los insumos y materiales que se requieren para este producto:
                        </label>

                        <!-- Buscador de Insumos -->
                        <div class="relative">
                            <input
                                v-model="insumosSearchQuery"
                                type="text"
                                placeholder="Buscar insumos por nombre..."
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 placeholder-stone-400 dark:border-stone-700 dark:text-white"
                            />
                        </div>

                        <div v-if="insumos.length > 0" class="max-h-80 overflow-y-auto space-y-4 pr-2 border rounded-lg p-3 bg-stone-50/50 dark:bg-stone-950/20 border-stone-200 dark:border-stone-850">
                            <!-- SECCIÓN: SELECCIONADOS -->
                            <div class="space-y-1">
                                <span class="text-[10px] uppercase font-bold text-amber-600 dark:text-amber-500 tracking-wider block mb-2 px-1">
                                    Seleccionados ({{ checkedInsumos.length }})
                                </span>
                                <template v-if="checkedInsumos.length > 0">
                                    <label 
                                        v-for="insumo in checkedInsumos" 
                                        :key="insumo.id" 
                                        class="flex items-center gap-3 p-2 rounded-md hover:bg-stone-105 dark:hover:bg-stone-800 transition-colors cursor-pointer select-none bg-stone-100/50 dark:bg-stone-800/40 border border-stone-200/40 dark:border-stone-800/40"
                                    >
                                        <input 
                                            type="checkbox" 
                                            :value="insumo.id" 
                                            v-model="selectedInsumoIds"
                                            class="rounded border-stone-300 text-amber-600 focus:ring-amber-500 w-4.5 h-4.5"
                                        />
                                        <span class="text-xs font-semibold text-stone-900 dark:text-stone-200">{{ insumo.nombre }}</span>
                                    </label>
                                </template>
                                <p v-else class="text-[11px] text-stone-500 italic p-2 pl-3">Ningún insumo seleccionado actualmente.</p>
                            </div>

                            <!-- SECCIÓN: DISPONIBLES (NO SELECCIONADOS) -->
                            <div class="space-y-1 pt-2 border-t border-stone-200/40 dark:border-stone-800/40">
                                <span class="text-[10px] uppercase font-bold text-stone-500 dark:text-stone-400 tracking-wider block mb-2 px-1">
                                    Disponibles / No seleccionados ({{ uncheckedInsumos.length }})
                                </span>
                                <template v-if="uncheckedInsumos.length > 0">
                                    <label 
                                        v-for="insumo in uncheckedInsumos" 
                                        :key="insumo.id" 
                                        class="flex items-center gap-3 p-2 rounded-md hover:bg-stone-100 dark:hover:bg-stone-800 transition-colors cursor-pointer select-none"
                                    >
                                        <input 
                                            type="checkbox" 
                                            :value="insumo.id" 
                                            v-model="selectedInsumoIds"
                                            class="rounded border-stone-300 text-amber-600 focus:ring-amber-500 w-4.5 h-4.5"
                                        />
                                        <span class="text-xs font-semibold text-stone-900 dark:text-stone-200">{{ insumo.nombre }}</span>
                                    </label>
                                </template>
                                <p v-else class="text-[11px] text-stone-500 italic p-2 pl-3">No hay más insumos disponibles para asociar.</p>
                            </div>
                        </div>
                        <p v-else class="text-xs text-stone-500 italic">No hay insumos registrados en el sistema. Ve a la sección Insumos para agregarlos.</p>
                        <span v-if="insumosForm.errors.insumos" class="text-xs text-red-500 block">{{ insumosForm.errors.insumos }}</span>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3 pt-4 border-t border-border">
                        <button type="button" @click="closeInsumosModal" class="rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-muted cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="insumosForm.processing" class="rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-stone-800 disabled:opacity-50 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200 cursor-pointer">
                            {{ insumosForm.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <ConfirmDialog 
            v-model:open="confirmOpen" 
            title="Eliminar Producto"
            message="¿Estás seguro de eliminar este producto? Esta acción no se puede deshacer."
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
