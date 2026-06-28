<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Save, LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    permiso: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Permisos', href: '/permisos' },
    { title: 'Editar', href: `/permisos/${props.permiso.id}/edit` },
];

const form = useForm({
    nombre: props.permiso.nombre,
    descripcion: props.permiso.descripcion,
});

const submit = () => {
    form.put(`/permisos/${props.permiso.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Editar Permiso" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link href="/permisos" class="p-2 rounded-md hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Editar Permiso</h1>
                    <p class="text-sm text-muted-foreground">Modifica la información del permiso seleccionado.</p>
                </div>
            </div>

            <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Nombre (Código)</label>
                            <input v-model="form.nombre" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                            <div v-if="form.errors.nombre" class="text-xs text-red-500">{{ form.errors.nombre }}</div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Descripción</label>
                            <input v-model="form.descripcion" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                            <div v-if="form.errors.descripcion" class="text-xs text-red-500">{{ form.errors.descripcion }}</div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <Link href="/permisos" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 transition-colors">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Actualizar Permiso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
