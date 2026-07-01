<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Save, LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    insumo: { id: number; nombre: string; proveedor_id: number };
    proveedores: Array<{ id: number, nombre_empresa: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Insumos', href: route('insumos.index') },
    { title: 'Editar', href: `/insumos/${props.insumo.id}/edit` },
];

const form = useForm({
    nombre: props.insumo.nombre,
    proveedor_id: props.insumo.proveedor_id,
});

const submit = () => {
    form.put(route('insumos.update', props.insumo.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Editar Insumo" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('insumos.index')" class="p-2 rounded-md hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Editar Insumo</h1>
                    <p class="text-sm text-muted-foreground">Modifica los datos del insumo o material registrado.</p>
                </div>
            </div>

            <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Nombre del Insumo / Material</label>
                            <input v-model="form.nombre" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                            <div v-if="form.errors.nombre" class="text-xs text-red-500">{{ form.errors.nombre }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Proveedor</label>
                            <select v-model="form.proveedor_id" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm">
                                <option value="" disabled>Seleccione un proveedor...</option>
                                <option v-for="prov in proveedores" :key="prov.id" :value="prov.id">
                                    {{ prov.nombre_empresa }}
                                </option>
                            </select>
                            <div v-if="form.errors.proveedor_id" class="text-xs text-red-500">{{ form.errors.proveedor_id }}</div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <Link :href="route('insumos.index')" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 transition-colors">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
