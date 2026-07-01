<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Save, LoaderCircle } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Tipos de Mueble', href: route('tipos.index') },
    { title: 'Nuevo', href: route('tipos.create') },
];

const form = useForm({
    nombre: '',
    estado: 'activo',
    descripcion: '',
});

const submit = () => {
    form.post(route('tipos.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nuevo Tipo de Mueble" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('tipos.index')" class="p-2 rounded-md hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Crear Tipo de Mueble</h1>
                    <p class="text-sm text-muted-foreground">Registra una nueva categoría de muebles.</p>
                </div>
            </div>

            <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-foreground">Nombre de Categoría</label>
                        <input v-model="form.nombre" type="text" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required />
                        <span v-if="form.errors.nombre" class="text-xs text-red-500">{{ form.errors.nombre }}</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-foreground">Estado</label>
                        <select v-model="form.estado" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                        <span v-if="form.errors.estado" class="text-xs text-red-500">{{ form.errors.estado }}</span>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-foreground">Descripción</label>
                        <textarea v-model="form.descripcion" rows="4" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring" required></textarea>
                        <span v-if="form.errors.descripcion" class="text-xs text-red-500">{{ form.errors.descripcion }}</span>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3 pt-4 border-t border-border">
                        <Link :href="route('tipos.index')" class="rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-muted inline-flex items-center justify-center">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-stone-800 disabled:opacity-50 dark:bg-stone-100 dark:text-stone-900 dark:hover:bg-stone-200">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
