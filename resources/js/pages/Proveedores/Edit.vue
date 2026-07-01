<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Save, LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    proveedor: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Proveedores', href: route('proveedores.index') },
    { title: 'Editar', href: `/proveedores/${props.proveedor.id}/edit` },
];

const form = useForm({
    nombre_empresa: props.proveedor.nombre_empresa,
    telefono: props.proveedor.telefono,
    direccion: props.proveedor.direccion,
});

const submit = () => {
    form.put(`/proveedores/${props.proveedor.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Editar Proveedor" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('proveedores.index')" class="p-2 rounded-md hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Editar Proveedor</h1>
                    <p class="text-sm text-muted-foreground">Actualiza los datos de la empresa proveedora.</p>
                </div>
            </div>

            <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Nombre de la Empresa</label>
                            <input v-model="form.nombre_empresa" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                            <div v-if="form.errors.nombre_empresa" class="text-xs text-red-500">{{ form.errors.nombre_empresa }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Teléfono de Contacto</label>
                            <input v-model="form.telefono" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                            <div v-if="form.errors.telefono" class="text-xs text-red-500">{{ form.errors.telefono }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Dirección</label>
                            <input v-model="form.direccion" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                            <div v-if="form.errors.direccion" class="text-xs text-red-500">{{ form.errors.direccion }}</div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <Link :href="route('proveedores.index')" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 transition-colors">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Actualizar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
