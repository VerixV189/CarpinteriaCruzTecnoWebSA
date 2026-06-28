<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ShoppingCart, PackageOpen, ImageIcon, Search, X } from 'lucide-vue-next';
import { ref } from 'vue';
import KidEmoji from '@/components/KidEmoji.vue';

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
    tipo: Tipo;
    imagenes: Imagen[];
    insumos?: Insumo[];
}

const props = defineProps<{
    productos: Producto[];
    cartCount: number;
    tipos: Tipo[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Marketplace', href: '/marketplace' },
];

const form = useForm({
    producto_id: null as number | null,
    cantidad: 1,
});

const searchQuery = ref('');
const selectedTipoId = ref<number | null>(null);
const addedProductIds = ref<number[]>([]);

// Filtrado de productos en el cliente
import { computed } from 'vue';

const filteredProductos = computed(() => {
    return props.productos.filter((producto) => {
        const matchesSearch = searchQuery.value
            ? producto.nombre.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
              producto.descripcion.toLowerCase().includes(searchQuery.value.toLowerCase())
            : true;

        const matchesTipo = selectedTipoId.value !== null
            ? producto.tipo?.id === selectedTipoId.value
            : true;

        return matchesSearch && matchesTipo;
    });
});

const addToCart = (producto: Producto) => {
    form.producto_id = producto.id;
    form.cantidad = 1;
    form.post(route('marketplace.addToCart'), {
        preserveScroll: true,
        onSuccess: () => {
            addedProductIds.value.push(producto.id);
            setTimeout(() => {
                addedProductIds.value = addedProductIds.value.filter(id => id !== producto.id);
            }, 2000);
        }
    });
};

const isDetailsModalOpen = ref(false);
const selectedProduct = ref<Producto | null>(null);
const activeImageIndex = ref(0);

const openDetails = (producto: Producto) => {
    selectedProduct.value = producto;
    activeImageIndex.value = 0;
    isDetailsModalOpen.value = true;
};

const closeDetails = () => {
    isDetailsModalOpen.value = false;
    selectedProduct.value = null;
};
</script>

<template>
    <Head title="Marketplace" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-7xl mx-auto space-y-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground"><KidEmoji /> Marketplace <KidEmoji /></h1>
                    <p class="text-muted-foreground mt-1 text-lg">Explora nuestros productos y compra en línea.</p>
                </div>
                <Link
                    :href="route('marketplace.cart')"
                    class="relative inline-flex items-center gap-2 rounded-full bg-stone-900 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-stone-800 transition-all dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200"
                >
                    <ShoppingCart class="h-5 w-5" />
                    <span>Ir al Carrito</span>
                    <span v-if="cartCount > 0" class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500 text-xs text-white shadow-sm ring-2 ring-background">
                        {{ cartCount }}
                    </span>
                </Link>
            </div>

            <!-- Filtros y Búsqueda -->
            <div class="flex flex-col gap-4 bg-card border border-stone-250/50 p-5 rounded-xl shadow-sm dark:border-stone-850">
                <!-- Fila superior: Búsqueda Manual -->
                <div class="relative w-full max-w-lg">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4.5 w-4.5 text-stone-400 dark:text-stone-500" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Buscar muebles por nombre o descripción..."
                        class="pl-10 pr-4 py-2 w-full text-sm rounded-lg border border-stone-200 bg-background focus:outline-none focus:ring-1 focus:ring-amber-500 focus:border-amber-500 transition-colors dark:border-stone-700 dark:text-white"
                    />
                </div>

                <!-- Fila inferior: Filtro por Tipo de Mueble (Pills) -->
                <div class="flex flex-wrap gap-1.5 items-center">
                    <button
                        @click="selectedTipoId = null"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-full transition-all border cursor-pointer select-none"
                        :class="selectedTipoId === null ? 'bg-amber-600 border-amber-600 text-white shadow-sm hover:bg-amber-700' : 'bg-background hover:bg-muted text-foreground border-stone-200 dark:border-stone-700'"
                    >
                        Todos
                    </button>
                    <button
                        v-for="tipo in tipos"
                        :key="tipo.id"
                        @click="selectedTipoId = tipo.id"
                        class="px-3.5 py-1.5 text-xs font-semibold rounded-full transition-all border cursor-pointer select-none"
                        :class="selectedTipoId === tipo.id ? 'bg-amber-600 border-amber-600 text-white shadow-sm hover:bg-amber-700' : 'bg-background hover:bg-muted text-foreground border-stone-200 dark:border-stone-700'"
                    >
                        {{ tipo.nombre }}
                    </button>
                </div>
            </div>

            <!-- Catálogo vacío -->
            <div v-if="filteredProductos.length === 0" class="flex flex-col items-center justify-center py-20 text-center border rounded-xl bg-card border-dashed">
                <PackageOpen class="h-16 w-16 text-muted-foreground/50 mb-4" />
                <h3 class="text-xl font-bold text-foreground">No se encontraron productos</h3>
                <p class="text-muted-foreground">Intenta cambiar el texto de búsqueda o selecciona otra categoría.</p>
            </div>

            <!-- Listado de Productos -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Product Card -->
                <div v-for="producto in filteredProductos" :key="producto.id" class="group flex flex-col overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm transition-all hover:shadow-md hover:border-border/80">
                    <!-- Image -->
                    <div class="aspect-square bg-muted relative overflow-hidden flex items-center justify-center">
                        <img v-if="producto.imagenes && producto.imagenes.length > 0" :src="producto.imagenes[0].URL" :alt="producto.nombre" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                        <ImageIcon v-else class="h-12 w-12 text-muted-foreground/50" />
                        <div class="absolute top-3 left-3 flex gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-background/90 backdrop-blur-sm text-foreground shadow-sm">
                                {{ producto.tipo?.nombre }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex flex-1 flex-col p-3 sm:p-5">
                        <div class="flex flex-col gap-1 mb-3 sm:mb-4">
                            <h3 class="font-extrabold text-stone-900 dark:text-white text-sm sm:text-base leading-tight line-clamp-3"><KidEmoji /> {{ producto.nombre }}</h3>
                            <span class="font-black text-sm sm:text-base text-amber-700 dark:text-amber-500 shrink-0">Bs. {{ parseFloat(producto.precio).toFixed(2) }}</span>
                        </div>

                        <div class="flex flex-col gap-3 mt-auto pt-3 border-t border-stone-200/50">
                            <span class="text-xs font-semibold text-stone-700 dark:text-stone-700 flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-green-600"></span>
                                {{ producto.cantidad }} en stock
                            </span>

                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-2 w-full mt-1">
                                <button
                                    @click="openDetails(producto)"
                                    class="inline-flex items-center justify-center rounded-md bg-stone-100 dark:bg-stone-200 px-2.5 py-2 text-xs font-extrabold border border-stone-300 dark:border-stone-400 text-stone-950 hover:bg-stone-200 dark:hover:bg-stone-300 transition-colors cursor-pointer"
                                >
                                    <KidEmoji /> Detalle
                                </button>
                                <button
                                    @click="addToCart(producto)"
                                    :disabled="(form.processing && form.producto_id === producto.id) || addedProductIds.includes(producto.id)"
                                    class="inline-flex items-center justify-center rounded-md bg-stone-900 dark:bg-stone-950 px-2.5 py-2 text-xs font-extrabold text-white hover:bg-stone-800 transition-colors disabled:opacity-50 cursor-pointer"
                                    :class="addedProductIds.includes(producto.id) ? 'text-green-600 font-bold bg-green-50 hover:bg-green-50' : ''"
                                >
                                    <span v-if="form.processing && form.producto_id === producto.id"><KidEmoji /> ...</span>
                                    <span v-else-if="addedProductIds.includes(producto.id)"><KidEmoji /> Si!</span>
                                    <span v-else><KidEmoji /> Agregar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE DETALLES DEL PRODUCTO -->
        <div v-if="isDetailsModalOpen && selectedProduct" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-card text-card-foreground rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden relative border border-stone-200 dark:border-stone-850 flex flex-col md:flex-row h-auto max-h-[90vh]">
                
                <!-- Botón de Cerrar -->
                <button @click="closeDetails" class="absolute top-4 right-4 z-10 text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-white bg-white/80 dark:bg-stone-900/80 p-2 rounded-full shadow-sm cursor-pointer">
                    <X class="w-5 h-5" />
                </button>

                <!-- Columna Izquierda: Galería de Imágenes -->
                <div class="w-full md:w-1/2 bg-stone-50 dark:bg-stone-950/20 flex flex-col items-center justify-center p-6 border-r border-stone-100 dark:border-stone-850">
                    <div class="w-full aspect-square bg-muted rounded-xl overflow-hidden flex items-center justify-center relative shadow-inner border border-stone-200 dark:border-stone-800">
                        <img 
                            v-if="selectedProduct.imagenes && selectedProduct.imagenes.length > 0" 
                            :src="selectedProduct.imagenes[activeImageIndex].URL" 
                            :alt="selectedProduct.nombre" 
                            class="w-full h-full object-cover"
                        />
                        <ImageIcon v-else class="h-20 w-20 text-muted-foreground/40" />
                    </div>

                    <!-- Thumbnails -->
                    <div v-if="selectedProduct.imagenes && selectedProduct.imagenes.length > 1" class="flex flex-wrap gap-2 mt-4 justify-center">
                        <button 
                            v-for="(img, idx) in selectedProduct.imagenes" 
                            :key="img.id" 
                            @click="activeImageIndex = idx"
                            :class="[
                                'w-12 h-12 rounded-lg border overflow-hidden transition-all',
                                activeImageIndex === idx ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-stone-250 dark:border-stone-750'
                            ]"
                        >
                            <img :src="img.URL" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Columna Derecha: Información y Materiales -->
                <div class="w-full md:w-1/2 p-6 flex flex-col justify-between overflow-y-auto">
                    <div class="space-y-5">
                        <div>
                            <span class="text-xs font-bold text-amber-600 dark:text-amber-500 uppercase tracking-wider">{{ selectedProduct.tipo?.nombre }}</span>
                            <h2 class="text-xl font-black text-stone-950 dark:text-white mt-1 leading-snug">{{ selectedProduct.nombre }}</h2>
                            <div class="text-2xl font-black text-amber-600 dark:text-amber-500 mt-2">Bs. {{ parseFloat(selectedProduct.precio).toFixed(2) }}</div>
                        </div>

                        <div class="space-y-1.5">
                            <span class="text-xs font-bold text-stone-700 dark:text-stone-300 block">Descripción</span>
                            <p class="text-xs text-stone-600 dark:text-stone-400 leading-relaxed">{{ selectedProduct.descripcion || 'Sin descripción disponible.' }}</p>
                        </div>

                        <!-- Insumos / Materiales -->
                        <div class="space-y-2">
                            <span class="text-xs font-bold text-stone-700 dark:text-stone-300 block">Insumos y Materiales Utilizados</span>
                            <div v-if="selectedProduct.insumos && selectedProduct.insumos.length > 0" class="flex flex-wrap gap-1.5">
                                <span 
                                    v-for="insumo in selectedProduct.insumos" 
                                    :key="insumo.id"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-transparent text-stone-850 dark:text-stone-100 border border-stone-300 dark:border-stone-700"
                                >
                                    {{ insumo.nombre }}
                                </span>
                            </div>
                            <p v-else class="text-[11px] text-stone-500 dark:text-stone-400 italic">No se listaron insumos específicos para este producto.</p>
                        </div>
                    </div>

                    <div class="border-t border-stone-100 dark:border-stone-850 pt-5 mt-6 flex items-center justify-between gap-4">
                        <span class="text-xs text-muted-foreground flex items-center gap-1 font-medium">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            {{ selectedProduct.cantidad }} en stock
                        </span>
                        
                        <button
                            @click="addToCart(selectedProduct); closeDetails()"
                            :disabled="addedProductIds.includes(selectedProduct.id)"
                            class="inline-flex items-center gap-1.5 justify-center rounded-xl bg-stone-900 px-5 py-2.5 text-xs font-bold text-white hover:bg-stone-800 transition-colors cursor-pointer dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200 disabled:opacity-50"
                        >
                            <ShoppingCart class="w-4.5 h-4.5" />
                            <span>{{ addedProductIds.includes(selectedProduct.id) ? 'En el Carrito' : 'Agregar al Carrito' }}</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
