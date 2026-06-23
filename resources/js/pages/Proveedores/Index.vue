<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { RefreshCw } from 'lucide-vue-next';

interface Proveedor {
    id: number;
    nombre_empresa: string;
    telefono: string;
    direccion: string;
}

const props = defineProps<{
    proveedores: Proveedor[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Proveedores', href: '/proveedores' },
];

const searchQuery = ref('');
const isRefreshing = ref(false);

const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

const filteredProveedores = computed(() => {
    return props.proveedores.filter((p) => {
        const company = p.nombre_empresa.toLowerCase();
        const address = p.direccion.toLowerCase();
        const query = searchQuery.value.toLowerCase();
        return company.includes(query) || address.includes(query);
    });
});
</script>

<template>
    <Head title="Proveedores" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Gestión de Proveedores</h1>
                    <p class="text-sm text-muted-foreground">Listado de empresas y proveedores de materias primas y herrajes.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por nombre de empresa o dirección..."
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
                                <th class="p-4">Nombre de la Empresa</th>
                                <th class="p-4">Teléfono</th>
                                <th class="p-4">Dirección</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="filteredProveedores.length === 0">
                                <td colspan="4" class="p-4 text-center text-muted-foreground">
                                    No se encontraron proveedores.
                                </td>
                            </tr>
                            <tr v-for="proveedor in filteredProveedores" :key="proveedor.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">#{{ proveedor.id }}</td>
                                <td class="p-4 font-medium">{{ proveedor.nombre_empresa }}</td>
                                <td class="p-4">{{ proveedor.telefono }}</td>
                                <td class="p-4">{{ proveedor.direccion }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
