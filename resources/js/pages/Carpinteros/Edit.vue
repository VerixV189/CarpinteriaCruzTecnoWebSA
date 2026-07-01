<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Save, LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    carpintero: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Carpinteros', href: route('carpinteros.index') },
    { title: 'Editar', href: `/carpinteros/${props.carpintero.id}/edit` },
];

const form = useForm({
    nombre: props.carpintero.usuario.nombre,
    apellido: props.carpintero.usuario.apellido,
    email: props.carpintero.usuario.email,
    telefono: props.carpintero.usuario.telefono || '',
    especialidad: props.carpintero.especialidad,
    costo_hora: props.carpintero.costo_hora,
});

const submit = () => {
    form.put(route('carpinteros.update', props.carpintero.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Editar Carpintero" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('carpinteros.index')" class="p-2 rounded-md hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Editar Carpintero</h1>
                    <p class="text-sm text-muted-foreground">Actualiza los datos del carpintero.</p>
                </div>
            </div>

            <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold border-b pb-2">Datos de Usuario</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Nombre</label>
                                <input v-model="form.nombre" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                                <div v-if="form.errors.nombre" class="text-xs text-red-500">{{ form.errors.nombre }}</div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Apellido</label>
                                <input v-model="form.apellido" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                                <div v-if="form.errors.apellido" class="text-xs text-red-500">{{ form.errors.apellido }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Correo Electrónico</label>
                                <input v-model="form.email" type="email" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                                <div v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Teléfono (Opcional)</label>
                                <input v-model="form.telefono" type="text" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                                <div v-if="form.errors.telefono" class="text-xs text-red-500">{{ form.errors.telefono }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 pt-2">
                        <h2 class="text-lg font-semibold border-b pb-2">Datos Profesionales</h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Especialidad</label>
                                <input v-model="form.especialidad" type="text" placeholder="Ej: Torneado, Tallado, Barnizado" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                                <div v-if="form.errors.especialidad" class="text-xs text-red-500">{{ form.errors.especialidad }}</div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Costo por Hora (Bs)</label>
                                <input v-model="form.costo_hora" type="number" step="0.01" min="0" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                                <div v-if="form.errors.costo_hora" class="text-xs text-red-500">{{ form.errors.costo_hora }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <Link :href="route('carpinteros.index')" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 transition-colors">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            Actualizar Carpintero
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
