<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Users, Package, ClipboardList, Banknote, ArrowUpRight } from 'lucide-vue-next';

interface Stats {
    clientes: number;
    productos: number;
    pedidos: number;
    ingresos: number;
}

interface RecursoPopular {
    modelo_tipo: string;
    total: number;
}

defineProps<{
    stats: Stats;
    recursos_populares: RecursoPopular[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <!-- Header Section -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-stone-900 dark:text-white">Panel de Control</h1>
                <p class="text-sm text-stone-500 dark:text-stone-400">Resumen y estado actual de Mueblería Cruz.</p>
            </div>

            <!-- Statistics Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Clientes Card -->
                <div class="rounded-xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <h3 class="text-sm font-medium text-muted-foreground">Clientes</h3>
                        <Users class="h-4 w-4 text-amber-600 dark:text-amber-500" />
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-2xl font-bold">{{ stats.clientes }}</div>
                        <span class="text-xs text-muted-foreground">Registrados</span>
                    </div>
                </div>

                <!-- Productos Card -->
                <div class="rounded-xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <h3 class="text-sm font-medium text-muted-foreground">Productos</h3>
                        <Package class="h-4 w-4 text-amber-600 dark:text-amber-500" />
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-2xl font-bold">{{ stats.productos }}</div>
                        <span class="text-xs text-muted-foreground">En catálogo</span>
                    </div>
                </div>

                <!-- Pedidos Card -->
                <div class="rounded-xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <h3 class="text-sm font-medium text-muted-foreground">Pedidos Activos</h3>
                        <ClipboardList class="h-4 w-4 text-amber-600 dark:text-amber-500" />
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-2xl font-bold">{{ stats.pedidos }}</div>
                        <span class="text-xs text-muted-foreground">En producción</span>
                    </div>
                </div>

                <!-- Ingresos Card -->
                <div class="rounded-xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                    <div class="flex items-center justify-between space-y-0 pb-2">
                        <h3 class="text-sm font-medium text-muted-foreground">Ingresos Totales</h3>
                        <Banknote class="h-4 w-4 text-emerald-600 dark:text-emerald-500" />
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="text-2xl font-bold">Bs. {{ stats.ingresos.toFixed(2) }}</div>
                        <span class="text-xs text-muted-foreground">Por ventas</span>
                    </div>
                </div>
            </div>

            <!-- Welcome Panel / Shortcuts -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Welcome Box -->
                <div class="relative overflow-hidden rounded-2xl border border-amber-200 dark:border-amber-900 bg-gradient-to-br from-amber-500/10 to-amber-600/5 dark:from-amber-950/20 p-6 flex flex-col justify-between min-h-[220px]">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <img src="/images/logo.png" alt="Mueblería Cruz Logo" class="h-5 w-5 object-contain rounded-md" />
                            <span class="font-semibold text-sm text-stone-900 dark:text-white">Mueblería Cruz</span>
                        </div>
                        <h2 class="text-xl font-bold text-stone-900 dark:text-white">¡Bienvenido al Sistema de Gestión!</h2>
                        <p class="text-sm text-stone-600 dark:text-stone-400">
                            Desde este panel de administración puedes controlar todo el flujo del taller: cotizaciones, órdenes de producción, catálogo de muebles e insumos.
                        </p>
                    </div>
                    <div class="pt-4">
                        <Link
                            :href="route('clientes.index')"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-600 dark:text-amber-500 hover:underline"
                        >
                            Comenzar gestionando Clientes <ArrowUpRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>

                <!-- Quick Action List -->
                <div class="rounded-2xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                    <h3 class="font-bold text-stone-900 dark:text-white mb-4">Accesos Rápidos</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <Link
                            :href="route('productos.index')"
                            class="flex flex-col items-center justify-center p-4 rounded-xl border border-sidebar-border hover:bg-muted/50 transition-all text-center space-y-2"
                        >
                            <Package class="h-6 w-6 text-amber-600 dark:text-amber-500" />
                            <span class="text-xs font-medium">Ver Catálogo</span>
                        </Link>
                        <Link
                            :href="route('cotizaciones.index')"
                            class="flex flex-col items-center justify-center p-4 rounded-xl border border-sidebar-border hover:bg-muted/50 transition-all text-center space-y-2"
                        >
                            <ClipboardList class="h-6 w-6 text-amber-600 dark:text-amber-500" />
                            <span class="text-xs font-medium">Cotizaciones</span>
                        </Link>
                        <Link
                            :href="route('insumos.index')"
                            class="flex flex-col items-center justify-center p-4 rounded-xl border border-sidebar-border hover:bg-muted/50 transition-all text-center space-y-2"
                        >
                            <Hammer class="h-6 w-6 text-amber-600 dark:text-amber-500" />
                            <span class="text-xs font-medium">Insumos Taller</span>
                        </Link>
                        <Link
                            :href="route('bitacoras.index')"
                            class="flex flex-col items-center justify-center p-4 rounded-xl border border-sidebar-border hover:bg-muted/50 transition-all text-center space-y-2"
                        >
                            <Users class="h-6 w-6 text-amber-600 dark:text-amber-500" />
                            <span class="text-xs font-medium">Auditoría / Logs</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Recursos Más Populares -->
            <div class="rounded-2xl border border-sidebar-border bg-card p-6 shadow-sm text-card-foreground">
                <h3 class="font-bold text-stone-900 dark:text-white mb-4">Recursos Más Accedidos (Top 5)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="(recurso, index) in recursos_populares" :key="index" class="flex items-center justify-between p-4 rounded-xl border border-sidebar-border hover:bg-muted/30 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400 font-bold text-sm">
                                #{{ index + 1 }}
                            </div>
                            <span class="text-sm font-semibold">{{ recurso.modelo_tipo }}</span>
                        </div>
                        <span class="text-sm font-medium text-muted-foreground">{{ recurso.total }} visitas</span>
                    </div>
                    <div v-if="!recursos_populares || recursos_populares.length === 0" class="col-span-full text-sm text-muted-foreground text-center py-6 border border-dashed rounded-xl">
                        Aún no hay suficientes registros en la bitácora.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
