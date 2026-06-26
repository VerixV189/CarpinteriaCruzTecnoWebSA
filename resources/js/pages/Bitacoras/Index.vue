<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { RefreshCw } from 'lucide-vue-next';
import Pagination from '@/components/Pagination.vue';

interface Usuario {
    nombre: string;
    apellido: string;
    email: string;
}

interface Bitacora {
    id: number;
    accion: string;
    modelo_tipo: string;
    modelo_id: string;
    valores_nuevos: any;
    ip_address: string;
    user_agent: string;
    created_at: string;
    usuario: Usuario | null;
}

interface PaginatedBitacoras {
    data: Bitacora[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
}

const props = defineProps<{
    bitacoras: PaginatedBitacoras;
    filters?: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Bitácora', href: '/bitacoras' },
];

const searchQuery = ref(props.filters?.search || '');
const isRefreshing = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/bitacoras', { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const refreshPage = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['bitacoras'],
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};
</script>

<template>
    <Head title="Bitácora de Eventos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Bitácora de Auditoría</h1>
                    <p class="text-sm text-muted-foreground">Registro de acciones y cambios realizados en el sistema por los usuarios.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 py-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por usuario, acción o modelo..."
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
                                <th class="p-4">Fecha / Hora</th>
                                <th class="p-4">Usuario</th>
                                <th class="p-4">Acción</th>
                                <th class="p-4">Modelo afectado</th>
                                <th class="p-4">Dirección IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sidebar-border">
                            <tr v-if="bitacoras.data.length === 0">
                                <td colspan="5" class="p-4 text-center text-muted-foreground">
                                    No se registraron eventos en la bitácora.
                                </td>
                            </tr>
                            <tr v-for="bitacora in bitacoras.data" :key="bitacora.id" class="hover:bg-muted/50 transition-colors">
                                <td class="p-4 font-semibold">{{ new Date(bitacora.created_at).toLocaleString() }}</td>
                                <td class="p-4">
                                    <span v-if="bitacora.usuario" class="font-medium">
                                        {{ bitacora.usuario.nombre }} {{ bitacora.usuario.apellido }}
                                    </span>
                                    <span v-else class="text-muted-foreground italic">Sistema</span>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wider bg-stone-100 dark:bg-stone-850">
                                        {{ bitacora.accion }}
                                    </span>
                                </td>
                                <td class="p-4 text-xs font-mono">
                                    {{ bitacora.modelo_tipo.split('\\').pop() }} (#{{ bitacora.modelo_id }})
                                </td>
                                <td class="p-4 font-mono text-xs">{{ bitacora.ip_address }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :links="bitacoras.links" />
            </div>
        </div>
    </AppLayout>
</template>
