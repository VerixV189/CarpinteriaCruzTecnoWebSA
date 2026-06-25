<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ShoppingCart, PackageOpen, ImageIcon } from 'lucide-vue-next';
import { ref } from 'vue';

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
    tipo: Tipo;
    imagenes: Imagen[];
}

const props = defineProps<{
    productos: Producto[];
    cartCount: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Marketplace', href: '/marketplace' },
];

const form = useForm({
    producto_id: null as number | null,
    cantidad: 1,
});

const addedProductIds = ref<number[]>([]);

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
</script>

<template>
    <Head title="Marketplace" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-7xl mx-auto space-y-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-foreground">Marketplace</h1>
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

            <div v-if="productos.length === 0" class="flex flex-col items-center justify-center py-20 text-center border rounded-xl bg-card border-dashed">
                <PackageOpen class="h-16 w-16 text-muted-foreground/50 mb-4" />
                <h3 class="text-xl font-bold text-foreground">No hay productos disponibles</h3>
                <p class="text-muted-foreground">Vuelve más tarde para ver nuestras novedades.</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Product Card -->
                <div v-for="producto in productos" :key="producto.id" class="group flex flex-col overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm transition-all hover:shadow-md hover:border-border/80">
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
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <h3 class="font-bold text-lg leading-tight line-clamp-2">{{ producto.nombre }}</h3>
                            <span class="font-black text-lg text-amber-600 dark:text-amber-500 shrink-0">Bs. {{ parseFloat(producto.precio).toFixed(2) }}</span>
                        </div>

                        <p class="text-sm text-muted-foreground line-clamp-3 mb-4 flex-1">
                            {{ producto.descripcion }}
                        </p>

                        <div class="flex items-center justify-between mt-auto">
                            <span class="text-xs font-medium text-muted-foreground flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                {{ producto.cantidad }} en stock
                            </span>

                            <button
                                @click="addToCart(producto)"
                                :disabled="(form.processing && form.producto_id === producto.id) || addedProductIds.includes(producto.id)"
                                class="inline-flex items-center justify-center rounded-md bg-stone-100 dark:bg-stone-800 px-3 py-2 text-sm font-medium transition-colors hover:bg-stone-200 dark:hover:bg-stone-700 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50"
                                :class="addedProductIds.includes(producto.id) ? 'text-green-600 dark:text-green-400 font-bold bg-green-50 dark:bg-green-900/20 hover:bg-green-50 dark:hover:bg-green-900/20' : 'text-foreground'"
                            >
                                <span v-if="form.processing && form.producto_id === producto.id">Agregando...</span>
                                <span v-else-if="addedProductIds.includes(producto.id)">¡Agregado!</span>
                                <span v-else>Agregar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
