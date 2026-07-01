<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps<{
    role: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Roles', href: route('roles.index') },
    { title: 'Ver Detalles', href: `/roles/${props.role.id}` },
];
</script>

<template>
    <Head title="Detalles del Rol" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-4xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('roles.index')" class="p-2 rounded-md hover:bg-muted transition-colors">
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-foreground">Detalles del Rol: {{ role.nombre }}</h1>
                        <p class="text-sm text-muted-foreground">Listado de los permisos asignados actualmente a este rol.</p>
                    </div>
                </div>
                <Link
                    :href="`/roles/${role.id}/edit`"
                    class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors"
                >
                    Editar Rol
                </Link>
            </div>

            <div class="rounded-md border border-sidebar-border bg-card shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-6 border-b pb-4">Permisos Asignados ({{ role.permisos?.length || 0 }})</h2>
                
                <div v-if="role.permisos && role.permisos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="permiso in role.permisos" :key="permiso.id" class="flex items-start gap-3 p-4 rounded-lg border border-sidebar-border bg-muted/20">
                        <CheckCircle2 class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" />
                        <div>
                            <p class="text-sm font-semibold text-foreground">{{ permiso.nombre }}</p>
                            <p class="text-xs text-muted-foreground mt-1">{{ permiso.descripcion }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12 text-muted-foreground">
                    <p>Este rol no tiene ningún permiso asignado.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
