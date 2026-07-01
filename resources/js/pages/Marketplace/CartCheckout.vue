<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft, Trash2, Plus, Minus, CreditCard, Wallet, ShoppingBag } from 'lucide-vue-next';

interface Imagen {
    id: number;
    URL: string;
}

interface Producto {
    id: number;
    nombre: string;
    precio: string;
    cantidad: number;
    imagenes: Imagen[];
}

interface DetalleCarrito {
    id: number;
    producto_id: number;
    cantidad: number;
    precio_unitario: string;
    producto: Producto;
}

interface Carrito {
    id: number;
    cliente_id: number;
    detalle_carritos: DetalleCarrito[];
}

const props = defineProps<{
    carrito: Carrito;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Marketplace', href: route('marketplace.index') },
    { title: 'Carrito', href: route('marketplace.cart') },
];

const checkoutForm = useForm({
    tipo_pago: 'Contado',
    cuotas: 1,
});

const subtotal = computed(() => {
    if (!props.carrito?.detalle_carritos) return 0;
    return props.carrito.detalle_carritos.reduce((sum, item) => {
        return sum + (parseFloat(item.precio_unitario) * item.cantidad);
    }, 0);
});

const updateQuantity = (detalleId: number, currentQty: number, change: number, maxStock: number) => {
    const newQty = currentQty + change;
    if (newQty < 1 || newQty > maxStock) return;
    
    router.put(route('marketplace.updateCart', detalleId), { cantidad: newQty }, {
        preserveScroll: true,
    });
};

const removeItem = (detalleId: number) => {
    router.delete(route('marketplace.removeFromCart', detalleId), {
        preserveScroll: true,
    });
};

const processCheckout = () => {
    checkoutForm.post(route('marketplace.checkout'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Carrito de Compras" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-6xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('marketplace.index')" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-muted hover:bg-muted/80 transition-colors">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-2xl font-bold text-foreground">Tu Carrito</h1>
            </div>

            <div v-if="!carrito?.detalle_carritos || carrito.detalle_carritos.length === 0" class="flex flex-col items-center justify-center py-20 bg-card rounded-xl border border-dashed text-center mt-8">
                <ShoppingBag class="h-16 w-16 text-muted-foreground/50 mb-4" />
                <h3 class="text-xl font-bold text-foreground">El carrito está vacío</h3>
                <p class="text-muted-foreground mt-2 mb-6">Parece que aún no has agregado nada.</p>
                <Link :href="route('marketplace.index')" class="rounded-md bg-stone-900 px-5 py-2.5 text-sm font-medium text-white shadow hover:bg-stone-800 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200 transition-colors">
                    Volver a la tienda
                </Link>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
                <!-- Items list -->
                <div class="lg:col-span-2 space-y-4">
                    <div v-for="item in carrito.detalle_carritos" :key="item.id" class="flex gap-4 p-4 rounded-xl border bg-card shadow-sm">
                        <div class="h-24 w-24 shrink-0 rounded-md bg-muted overflow-hidden">
                            <img v-if="item.producto.imagenes?.length > 0" :src="item.producto.imagenes[0].URL" :alt="item.producto.nombre" class="h-full w-full object-cover" />
                        </div>
                        <div class="flex flex-1 flex-col justify-between">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <h3 class="font-semibold text-lg line-clamp-1">{{ item.producto.nombre }}</h3>
                                    <p class="text-sm text-amber-600 font-medium">Bs. {{ parseFloat(item.precio_unitario).toFixed(2) }} c/u</p>
                                </div>
                                <button @click="removeItem(item.id)" class="text-muted-foreground hover:text-red-500 transition-colors p-1" title="Eliminar">
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </div>
                            
                            <div class="flex items-center justify-between mt-4">
                                <div class="flex items-center rounded-md border border-input bg-background h-9">
                                    <button @click="updateQuantity(item.id, item.cantidad, -1, item.producto.cantidad)" class="px-3 h-full flex items-center justify-center text-muted-foreground hover:text-foreground border-r border-input" :disabled="item.cantidad <= 1">
                                        <Minus class="h-3.5 w-3.5" />
                                    </button>
                                    <span class="px-4 font-medium text-sm">{{ item.cantidad }}</span>
                                    <button @click="updateQuantity(item.id, item.cantidad, 1, item.producto.cantidad)" class="px-3 h-full flex items-center justify-center text-muted-foreground hover:text-foreground border-l border-input" :disabled="item.cantidad >= item.producto.cantidad">
                                        <Plus class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <div class="font-bold text-lg">
                                    Bs. {{ (parseFloat(item.precio_unitario) * item.cantidad).toFixed(2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkout Panel -->
                <div class="space-y-6">
                    <div class="rounded-xl border bg-card p-6 shadow-sm sticky top-6">
                        <h2 class="text-xl font-bold mb-6">Resumen de Compra</h2>
                        
                        <div class="space-y-3 pb-6 border-b border-border">
                            <div class="flex justify-between text-muted-foreground">
                                <span>Subtotal ({{ carrito.detalle_carritos.length }} items)</span>
                                <span>Bs. {{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-muted-foreground">
                                <span>Envío</span>
                                <span>Por acordar</span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between font-black text-xl py-6">
                            <span>Total</span>
                            <span>Bs. {{ subtotal.toFixed(2) }}</span>
                        </div>

                        <form @submit.prevent="processCheckout" class="space-y-6">
                            <div class="space-y-3">
                                <label class="text-sm font-semibold block">Método de Pago</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex flex-col items-center justify-center rounded-lg border p-4 cursor-pointer hover:bg-muted/50 transition-colors" :class="{ 'ring-2 ring-primary border-transparent bg-primary/5': checkoutForm.tipo_pago === 'Contado' }">
                                        <input type="radio" v-model="checkoutForm.tipo_pago" value="Contado" class="sr-only" />
                                        <Wallet class="h-6 w-6 mb-2" :class="checkoutForm.tipo_pago === 'Contado' ? 'text-primary' : 'text-muted-foreground'" />
                                        <span class="text-sm font-medium">Al Contado</span>
                                    </label>
                                    
                                    <label class="flex flex-col items-center justify-center rounded-lg border p-4 cursor-pointer hover:bg-muted/50 transition-colors" :class="{ 'ring-2 ring-primary border-transparent bg-primary/5': checkoutForm.tipo_pago === 'Crédito' }">
                                        <input type="radio" v-model="checkoutForm.tipo_pago" value="Crédito" class="sr-only" />
                                        <CreditCard class="h-6 w-6 mb-2" :class="checkoutForm.tipo_pago === 'Crédito' ? 'text-primary' : 'text-muted-foreground'" />
                                        <span class="text-sm font-medium">A Crédito</span>
                                    </label>
                                </div>
                            </div>

                            <div v-if="checkoutForm.tipo_pago === 'Crédito'" class="space-y-2 animate-in fade-in slide-in-from-top-2">
                                <label class="text-sm font-semibold block">Número de Cuotas</label>
                                <select v-model="checkoutForm.cuotas" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-1 focus:ring-ring">
                                    <option v-for="n in 12" :key="n" :value="n">{{ n }} {{ n === 1 ? 'cuota' : 'cuotas' }} de Bs. {{ (subtotal / n).toFixed(2) }}</option>
                                </select>
                            </div>

                            <button 
                                type="submit" 
                                :disabled="checkoutForm.processing"
                                class="w-full rounded-md bg-stone-900 px-4 py-3 text-sm font-semibold text-white shadow-md hover:bg-stone-800 transition-colors disabled:opacity-50 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200 flex items-center justify-center gap-2"
                            >
                                <span v-if="checkoutForm.processing">Procesando...</span>
                                <template v-else>
                                    <ShoppingCart class="h-4 w-4" /> Finalizar Compra
                                </template>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
