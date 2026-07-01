<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { ArrowLeft, Save, LoaderCircle, Image as ImageIcon } from 'lucide-vue-next';

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
    imagenes?: Imagen[];
}

const props = defineProps<{
    producto: Producto;
    tipos: Tipo[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Productos', href: route('productos.index') },
    { title: 'Editar', href: `/productos/${props.producto.id}/edit` },
];

const form = useForm({
    _method: 'put',
    tipo_id: props.producto.tipo_id,
    nombre: props.producto.nombre,
    cantidad: props.producto.cantidad,
    precio: parseFloat(props.producto.precio),
    descripcion: props.producto.descripcion,
    estado: props.producto.estado,
    imagen: null as File | null,
});

const page = usePage<any>();

const imagePreview = ref<string | null>(null);

onMounted(() => {
    if (props.producto.imagenes && props.producto.imagenes.length > 0) {
        imagePreview.value = (page.props.app_url || '') + props.producto.imagenes[0].URL;
    }
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        
        if (file.type !== 'image/png' && file.type !== 'image/jpeg' && file.type !== 'image/jpg') {
            alert(`El archivo "${file.name}" no es admitido. Solo se permiten imágenes JPG o PNG.`);
            target.value = '';
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

const submit = () => {
    form.post(route('productos.update', props.producto.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Editar Producto" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-4xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('productos.index')" class="p-2 rounded-md hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Editar Producto: {{ producto.nombre }}</h1>
                    <p class="text-sm text-muted-foreground">Modifica los detalles del producto en el catálogo.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Columna Izquierda: Datos principales -->
                <div class="md:col-span-2 space-y-6">
                    <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Información General</h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="col-span-1 sm:col-span-2">
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

                            <div class="col-span-1 sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-foreground">Descripción</label>
                                <textarea v-model="form.descripcion" rows="4" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required></textarea>
                                <span v-if="form.errors.descripcion" class="text-xs text-red-500">{{ form.errors.descripcion }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Imagen y Guardar -->
                <div class="md:col-span-1 space-y-6">
                    <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Imagen del Producto</h2>
                        
                        <div class="space-y-4">
                            <div class="border-2 border-dashed border-muted-foreground/30 hover:border-primary/50 transition-colors rounded-lg p-5 flex flex-col items-center justify-center gap-2 cursor-pointer relative bg-muted/20">
                                <input
                                    type="file"
                                    id="imagen-producto-input"
                                    accept="image/png, image/jpeg, image/jpg"
                                    @change="handleFileChange"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                />
                                <ImageIcon class="text-muted-foreground h-8 w-8" />
                                <div class="text-xs font-medium text-foreground text-center">
                                    Haz clic o arrastra para subir una imagen
                                </div>
                                <div class="text-[10px] text-muted-foreground text-center">
                                    Formatos admitidos: PNG, JPG
                                </div>
                            </div>

                            <!-- Previsualización -->
                            <div v-if="imagePreview" class="relative mt-2 w-full aspect-square rounded-md overflow-hidden border bg-muted group">
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
                            <p class="text-[11px] text-muted-foreground text-center mt-2">Si subes una nueva imagen, reemplazará a la actual.</p>
                        </div>
                    </div>

                    <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6">
                        <button type="submit" :disabled="form.processing" class="w-full inline-flex items-center justify-center rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-stone-800 disabled:opacity-50 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200 transition-colors">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Actualizar Producto
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
