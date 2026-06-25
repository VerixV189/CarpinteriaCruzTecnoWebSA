<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { RefreshCw } from 'lucide-vue-next';

interface Usuario {
    id: number;
    nombre: string;
    apellido: string;
    email: string;
    telefono: string | null;
}

interface Cliente {
    id: number;
    nit_facturacion: string;
    razon_social: string;
    direccion_envio: string;
    usuario: Usuario;
}

const props = defineProps<{
    clientes: {
        data: Cliente[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Clientes',
        href: '/clientes',
    },
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

const filteredClientes = computed(() => {
    return props.clientes.data.filter((cliente) => {
        const fullName = `${cliente.usuario.nombre} ${cliente.usuario.apellido}`.toLowerCase();
        const email = cliente.usuario.email.toLowerCase();
        const nit = cliente.nit_facturacion.toLowerCase();
        const query = searchQuery.value.toLowerCase();
        return fullName.includes(query) || email.includes(query) || nit.includes(query);
    });
});

const deleteCliente = (id: number) => {
    if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
        router.delete(route('clientes.destroy', { cliente: id }));
    }
};
</script>

<template>
    <Head title="Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Gestión de Clientes</h1>
                    <p class="text-sm text-muted-foreground">Listado de clientes registrados en el sistema.</p>
                </div>
                <Link
                    :href="route('clientes.create')"
                    class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                >
                    Nuevo Cliente
                </Link>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por nombre, email o NIT..."
                    class="flex h-9 w-full max-w-sm rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
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
                                <th class="p-4">Nombre Completo</th>
                                <th class="p-4">Email</th>
                                <th class="p-4">Teléfono</th>
                                <th class="p-4">NIT</th>
                                <th class="p-4">Razón Social</th>
                                <th class="p-4">Dirección</th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="filteredClientes.length === 0">
                                <td colspan="7" class="p-4 text-center text-muted-foreground">
                                    No se encontraron clientes.
                                </td>
                            </tr>
                            <tr v-for="cliente in filteredClientes" :key="cliente.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-medium">
                                    {{ cliente.usuario.nombre }} {{ cliente.usuario.apellido }}
                                </td>
                                <td class="p-4">{{ cliente.usuario.email }}</td>
                                <td class="p-4">{{ cliente.usuario.telefono || '-' }}</td>
                                <td class="p-4">{{ cliente.nit_facturacion }}</td>
                                <td class="p-4">{{ cliente.razon_social }}</td>
                                <td class="p-4">{{ cliente.direccion_envio }}</td>
                                <td class="p-4 text-right space-x-2">
                                    <Link
                                        :href="route('clientes.edit', { cliente: cliente.id })"
                                        class="inline-flex items-center justify-center rounded-md border border-input bg-background px-3 py-1.5 text-xs font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground"
                                    >
                                        Editar
                                    </Link>
                                    <button
                                        @click="deleteCliente(cliente.id)"
                                        class="inline-flex items-center justify-center rounded-md bg-destructive px-3 py-1.5 text-xs font-medium text-destructive-foreground shadow-sm transition-colors hover:bg-destructive/90"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <div class="p-4 flex items-center justify-between border-t border-sidebar-border" v-if="clientes.links && clientes.links.length > 3">
                    <div class="flex flex-wrap gap-1">
                        <template v-for="(link, key) in clientes.links" :key="key">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1 text-sm rounded-md border"
                                :class="link.active ? 'bg-zinc-900 text-white border-zinc-900 dark:bg-white dark:text-zinc-900 dark:border-white' : 'bg-transparent hover:bg-zinc-100 text-zinc-600 border-zinc-200 dark:hover:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-800'"
                                preserve-scroll
                            >
                                <span v-html="link.label"></span>
                            </Link>
                            <span
                                v-else
                                class="px-3 py-1 text-sm rounded-md border border-zinc-200 text-zinc-400 opacity-50 dark:border-zinc-800 dark:text-zinc-600"
                                v-html="link.label"
                            ></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
