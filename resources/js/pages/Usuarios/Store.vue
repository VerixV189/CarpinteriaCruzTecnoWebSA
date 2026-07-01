<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle, ArrowLeft } from 'lucide-vue-next';

interface Rol {
    id: number;
    nombre: string;
}

const props = defineProps<{
    roles: Rol[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Usuarios', href: route('usuarios.index') },
    { title: 'Crear', href: route('usuarios.create') },
];

const form = useForm({
    nombre: '',
    apellido: '',
    email: '',
    password: '',
    telefono: '',
    rol_id: '',
    estado: 'activo',
});

const submit = () => {
    // Usamos el helper de rutas de Laravel si está disponible (Ziggy), si no apuntamos a /usuarios
    form.post('/usuarios', {
        onSuccess: () => {
            // El backend hará un redirect, no necesitamos hacer nada más aquí
        }
    });
};
</script>

<template>
    <Head title="Crear Usuario" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('usuarios.index')" class="p-2 rounded-md hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Crear Usuario</h1>
                    <p class="text-sm text-muted-foreground">Llena el formulario para registrar un nuevo usuario en el sistema.</p>
                </div>
            </div>

            <div class="rounded-md border border-sidebar-border bg-card text-card-foreground shadow p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Nombre</label>
                            <input v-model="form.nombre" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="Ej. Juan" />
                            <div v-if="form.errors.nombre" class="text-xs text-red-500">{{ form.errors.nombre }}</div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Apellido</label>
                            <input v-model="form.apellido" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="Ej. Pérez" />
                            <div v-if="form.errors.apellido" class="text-xs text-red-500">{{ form.errors.apellido }}</div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium">Email</label>
                        <input v-model="form.email" type="email" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="juan@ejemplo.com" />
                        <div v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium">Contraseña</label>
                        <input v-model="form.password" type="password" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="Mínimo 8 caracteres" />
                        <div v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Teléfono</label>
                            <input v-model="form.telefono" type="text" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring" placeholder="+591 71234567" />
                            <div v-if="form.errors.telefono" class="text-xs text-red-500">{{ form.errors.telefono }}</div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Estado</label>
                            <select v-model="form.estado" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                            <div v-if="form.errors.estado" class="text-xs text-red-500">{{ form.errors.estado }}</div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium">Rol del Usuario</label>
                        <select v-model="form.rol_id" required class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                            <option value="" disabled>Seleccione un rol...</option>
                            <option v-for="rol in roles" :key="rol.id" :value="rol.id">{{ rol.nombre }}</option>
                        </select>
                        <div v-if="form.errors.rol_id" class="text-xs text-red-500">{{ form.errors.rol_id }}</div>
                    </div>

                    <div class="pt-4 flex justify-end gap-2">
                        <Link :href="route('usuarios.index')" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 disabled:opacity-50 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 transition-colors">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>