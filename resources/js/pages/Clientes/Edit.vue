<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Usuario {
    id: number;
    nombre: string;
    apellido: string;
    email: string;
    telefono: string | null;
}

interface Cliente {
    id: number;
    nit_facturacion: string;
    razon_social: string;
    direccion_envio: string;
    usuario: Usuario;
}

const props = defineProps<{
    cliente: Cliente;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Clientes',
        href: route('clientes.index'),
    },
    {
        title: 'Editar',
        href: `/clientes/${props.cliente.id}/edit`,
    },
];

const form = useForm({
    nombre: props.cliente.usuario.nombre,
    apellido: props.cliente.usuario.apellido,
    email: props.cliente.usuario.email,
    telefono: props.cliente.usuario.telefono || '',
    nit_facturacion: props.cliente.nit_facturacion,
    razon_social: props.cliente.razon_social,
    direccion_envio: props.cliente.direccion_envio,
});

const submit = () => {
    form.put(route('clientes.update', props.cliente.id));
};
</script>

<template>
    <Head title="Editar Cliente" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-2xl mx-auto space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Editar Cliente</h1>
                <p class="text-sm text-muted-foreground">Actualiza los datos personales y de facturación del cliente.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 bg-card text-card-foreground border border-sidebar-border rounded-xl p-6 shadow-sm">
                <!-- Datos Personales -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold border-b pb-2 border-sidebar-border">Datos Personales</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="nombre" class="text-sm font-medium">Nombre</label>
                            <input
                                id="nombre"
                                v-model="form.nombre"
                                type="text"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                required
                            />
                            <div v-if="form.errors.nombre" class="text-xs text-destructive mt-1">{{ form.errors.nombre }}</div>
                        </div>

                        <div class="space-y-1">
                            <label for="apellido" class="text-sm font-medium">Apellido</label>
                            <input
                                id="apellido"
                                v-model="form.apellido"
                                type="text"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                required
                            />
                            <div v-if="form.errors.apellido" class="text-xs text-destructive mt-1">{{ form.errors.apellido }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="email" class="text-sm font-medium">Correo Electrónico</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                required
                            />
                            <div v-if="form.errors.email" class="text-xs text-destructive mt-1">{{ form.errors.email }}</div>
                        </div>

                        <div class="space-y-1">
                            <label for="telefono" class="text-sm font-medium">Teléfono</label>
                            <input
                                id="telefono"
                                v-model="form.telefono"
                                type="text"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            />
                            <div v-if="form.errors.telefono" class="text-xs text-destructive mt-1">{{ form.errors.telefono }}</div>
                        </div>
                    </div>
                </div>

                <!-- Datos de Facturación / Envío -->
                <div class="space-y-4 pt-4">
                    <h2 class="text-lg font-semibold border-b pb-2 border-sidebar-border">Datos del Cliente</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="nit_facturacion" class="text-sm font-medium">NIT de Facturación</label>
                            <input
                                id="nit_facturacion"
                                v-model="form.nit_facturacion"
                                type="text"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                required
                            />
                            <div v-if="form.errors.nit_facturacion" class="text-xs text-destructive mt-1">{{ form.errors.nit_facturacion }}</div>
                        </div>

                        <div class="space-y-1">
                            <label for="razon_social" class="text-sm font-medium">Razón Social</label>
                            <input
                                id="razon_social"
                                v-model="form.razon_social"
                                type="text"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                required
                            />
                            <div v-if="form.errors.razon_social" class="text-xs text-destructive mt-1">{{ form.errors.razon_social }}</div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="direccion_envio" class="text-sm font-medium">Dirección de Envío</label>
                        <input
                            id="direccion_envio"
                            v-model="form.direccion_envio"
                            type="text"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            required
                        />
                        <div v-if="form.errors.direccion_envio" class="text-xs text-destructive mt-1">{{ form.errors.direccion_envio }}</div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-3 pt-4">
                    <Link
                        :href="route('clientes.index')"
                        class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:opacity-50"
                    >
                        Actualizar Cliente
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
