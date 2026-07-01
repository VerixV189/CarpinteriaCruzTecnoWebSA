<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Save, LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    permisos_disponibles: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Roles', href: route('roles.index') },
    { title: 'Nuevo', href: route('roles.create') },
];

const form = useForm({
    nombre: '',
    estado: 'Activo',
    permisos: [] as number[],
});

const submit = () => {
    form.post(route('roles.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nuevo Rol" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-4xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('roles.index')" class="p-2 rounded-md hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Crear Nuevo Rol</h1>
                    <p class="text-sm text-muted-foreground">Configura un nuevo rol y asígnale los permisos correspondientes.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Columna Izquierda: Datos del Rol -->
                <div class="md:col-span-1 space-y-6">
                    <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6">
                        <h2 class="text-lg font-semibold mb-4">Información del Rol</h2>
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Nombre del Rol</label>
                                <input v-model="form.nombre" type="text" placeholder="Ej: Vendedor, Supervisor..." required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm" />
                                <div v-if="form.errors.nombre" class="text-xs text-red-500">{{ form.errors.nombre }}</div>
                            </div>
                            
                            <div class="space-y-2 mt-4">
                                <label class="text-sm font-medium">Estado del Rol</label>
                                <select v-model="form.estado" class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                                <div v-if="form.errors.estado" class="text-xs text-red-500">{{ form.errors.estado }}</div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t">
                            <button type="submit" :disabled="form.processing" class="w-full inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 transition-colors">
                                <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Save v-else class="mr-2 h-4 w-4" />
                                Guardar Rol
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Asignación de Permisos -->
                <div class="md:col-span-2">
                    <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6 h-full">
                        <h2 class="text-lg font-semibold mb-4">Permisos Asignados</h2>
                        <p class="text-sm text-muted-foreground mb-4">Selecciona las acciones que este rol podrá realizar dentro del sistema.</p>
                        
                        <div v-if="form.errors.permisos" class="text-xs text-red-500 mb-4">{{ form.errors.permisos }}</div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <label v-for="permiso in permisos_disponibles" :key="permiso.id" class="flex items-start space-x-3 p-3 rounded-lg border border-sidebar-border hover:bg-muted/30 cursor-pointer transition-colors">
                                <input 
                                    type="checkbox" 
                                    :value="permiso.id" 
                                    v-model="form.permisos"
                                    class="mt-1 h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-600"
                                >
                                <div>
                                    <p class="text-sm font-medium">{{ permiso.nombre }}</p>
                                    <p class="text-xs text-muted-foreground line-clamp-2">{{ permiso.descripcion }}</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
