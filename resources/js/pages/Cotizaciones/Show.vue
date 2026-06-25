<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, Plus, Check, X, LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    cotizacion: any;
}>();

const page = usePage();
const currentUserRole = computed(() => page.props.auth.user.rol_id);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cotizaciones', href: '/cotizaciones' },
    { title: 'Detalles', href: `/cotizaciones/${props.cotizacion.id}` },
];

const total = computed(() => {
    if (!props.cotizacion.detalle_cotizaciones) return 0;
    return props.cotizacion.detalle_cotizaciones.reduce((acc: number, d: any) => acc + parseFloat(d.precio), 0);
});

// Formulario para el Carpintero
const detalleForm = useForm({
    descripcion: '',
    precio: '',
});

const submitDetalle = () => {
    detalleForm.post(`/cotizaciones/${props.cotizacion.id}/detalles`, {
        preserveScroll: true,
        onSuccess: () => detalleForm.reset(),
    });
};

// Formulario de aprobación (Cliente)
const estadoForm = useForm({
    estado: '',
    tipo_pago: 'Contado',
    cuotas: 1,
});

const showPaymentDialog = ref(false);

const cambiarEstado = (nuevoEstado: string) => {
    if (nuevoEstado === 'Rechazada') {
        estadoForm.estado = 'Rechazada';
        estadoForm.put(`/cotizaciones/${props.cotizacion.id}/estado`, {
            preserveScroll: true,
        });
    } else {
        showPaymentDialog.value = true;
    }
};

const confirmarAprobacion = () => {
    estadoForm.estado = 'Aprobada';
    estadoForm.put(`/cotizaciones/${props.cotizacion.id}/estado`, {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentDialog.value = false;
        }
    });
};
</script>

<template>
    <Head title="Detalle de Cotización" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-4xl mx-auto space-y-6">
            <!-- HEADER MAESTRO -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link href="/cotizaciones" class="p-2 rounded-md hover:bg-muted transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">Cotización #{{ cotizacion.id }}</h1>
                        <p class="text-sm text-muted-foreground">Cliente: {{ cotizacion.cliente?.usuario?.nombre }} {{ cotizacion.cliente?.usuario?.apellido }}</p>
                    </div>
                </div>
                <span :class="`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${(cotizacion.estado || '').toLowerCase() === 'aprobada' ? 'bg-green-100 text-green-800' : ((cotizacion.estado || '').toLowerCase() === 'pendiente' ? 'bg-zinc-100 text-zinc-800' : 'bg-amber-100 text-amber-800')}`">
                    {{ cotizacion.estado }}
                </span>
            </div>

            <!-- DESCRIPCIÓN DEL REQUERIMIENTO -->
            <div class="rounded-md border border-sidebar-border bg-card text-card-foreground shadow p-6 space-y-4">
                <h2 class="text-lg font-semibold border-b pb-2">Requerimiento del Cliente</h2>
                <p class="text-sm whitespace-pre-wrap">{{ cotizacion.descripcion }}</p>
            </div>

            <!-- TABLA DE DETALLES (PRESUPUESTO) -->
            <div class="rounded-md border border-sidebar-border bg-card text-card-foreground shadow overflow-hidden">
                <div class="p-4 border-b bg-muted/50 flex justify-between items-center">
                    <h2 class="text-lg font-semibold">Detalles del Presupuesto</h2>
                    <div class="text-lg font-bold text-amber-600">Total: Bs. {{ total.toFixed(2) }}</div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full caption-bottom text-sm">
                        <thead class="border-b bg-muted/20">
                            <tr class="text-left font-medium text-muted-foreground">
                                <th class="p-4">Descripción del Item</th>
                                <th class="p-4">Carpintero</th>
                                <th class="p-4 text-right">Precio (Bs)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-if="!cotizacion.detalle_cotizaciones || cotizacion.detalle_cotizaciones.length === 0">
                                <td colspan="3" class="p-4 text-center text-muted-foreground italic">
                                    El carpintero aún no ha agregado detalles ni precios.
                                </td>
                            </tr>
                            <tr v-for="detalle in cotizacion.detalle_cotizaciones" :key="detalle.id" class="hover:bg-muted/50">
                                <td class="p-4">{{ detalle.descripcion }}</td>
                                <td class="p-4 text-muted-foreground">{{ detalle.carpintero?.usuario?.nombre }}</td>
                                <td class="p-4 text-right font-medium">{{ parseFloat(detalle.precio).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ACCIONES CARPINTERO (Agregar Detalle) -->
            <div v-if="currentUserRole === 3 && ((cotizacion.estado || '').toLowerCase() === 'pendiente' || (cotizacion.estado || '').toLowerCase() === 'cotizado')" class="rounded-md border border-sidebar-border bg-card shadow p-6">
                <h3 class="text-md font-semibold mb-4">Agregar Item al Presupuesto</h3>
                <form @submit.prevent="submitDetalle" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px] space-y-2">
                        <label class="text-xs font-medium">Descripción (Ej: Madera Roble, Mano de Obra, Barniz)</label>
                        <input v-model="detalleForm.descripcion" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                    </div>
                    <div class="w-32 space-y-2">
                        <label class="text-xs font-medium">Costo (Bs)</label>
                        <input v-model="detalleForm.precio" type="number" step="0.01" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                    </div>
                    <button type="submit" :disabled="detalleForm.processing" class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 h-9 text-sm font-medium text-white shadow hover:bg-zinc-800 transition-colors">
                        <LoaderCircle v-if="detalleForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Plus v-else class="w-4 h-4 mr-2" /> Agregar
                    </button>
                </form>
            </div>

            <!-- ACCIONES CLIENTE (Aprobar/Rechazar) -->
            <div v-if="currentUserRole === 2 && (cotizacion.estado || '').toLowerCase() === 'cotizado'" class="rounded-md border border-sidebar-border bg-card shadow p-6 flex flex-col items-center gap-4 text-center">
                <div v-if="!showPaymentDialog">
                    <h3 class="text-lg font-semibold">¿Estás de acuerdo con este presupuesto?</h3>
                    <p class="text-sm text-muted-foreground">Revisa los detalles del carpintero antes de aprobar. Al aprobar, autorizas la creación del pedido.</p>
                    <div class="flex gap-4 mt-4">
                        <button @click="cambiarEstado('Rechazada')" :disabled="estadoForm.processing" class="inline-flex items-center justify-center rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-red-100 transition-colors">
                            <LoaderCircle v-if="estadoForm.processing && estadoForm.estado === 'Rechazada'" class="mr-2 h-4 w-4 animate-spin" />
                            <X v-else class="w-4 h-4 mr-2" /> Rechazar
                        </button>
                        <button @click="cambiarEstado('Aprobada')" type="button" class="inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-green-700 transition-colors">
                            <Check class="w-4 h-4 mr-2" /> Aprobar Presupuesto
                        </button>
                    </div>
                </div>

                <div v-else class="w-full max-w-md text-left space-y-4">
                    <h3 class="text-lg font-semibold border-b pb-2">Opciones de Pago</h3>
                    <form @submit.prevent="confirmarAprobacion" class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Forma de Pago</label>
                            <select v-model="estadoForm.tipo_pago" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm">
                                <option value="Contado">Al Contado (1 solo pago)</option>
                                <option value="Crédito">A Crédito (En cuotas)</option>
                            </select>
                        </div>
                        
                        <div v-if="estadoForm.tipo_pago === 'Crédito'" class="space-y-2">
                            <label class="text-sm font-medium">Cantidad de Cuotas</label>
                            <input v-model="estadoForm.cuotas" type="number" min="2" max="12" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                            <p class="text-xs text-muted-foreground">Monto por cuota: Bs. {{ (total / estadoForm.cuotas).toFixed(2) }}</p>
                        </div>

                        <div class="flex gap-2 justify-end pt-2">
                            <button @click="showPaymentDialog = false" type="button" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                                Volver
                            </button>
                            <button type="submit" :disabled="estadoForm.processing" class="inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-green-700 transition-colors">
                                <LoaderCircle v-if="estadoForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                                Confirmar Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
