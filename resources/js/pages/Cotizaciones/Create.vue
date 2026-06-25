<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle, ArrowLeft } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Cotizaciones', href: '/cotizaciones' },
    { title: 'Solicitar', href: '/cotizaciones/create' },
];

const form = useForm({
    descripcion: '',
});

const submit = () => {
    form.post('/cotizaciones', {
        onSuccess: () => {
            // Vue redirigirá o limpiará
        }
    });
};
</script>

<template>
    <Head title="Solicitar Cotización" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 w-full max-w-2xl mx-auto space-y-6">
            <div class="flex items-center gap-4">
                <Link href="/cotizaciones" class="p-2 rounded-md hover:bg-muted transition-colors">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Solicitar Cotización</h1>
                    <p class="text-sm text-muted-foreground">Describe detalladamente el trabajo de carpintería que necesitas.</p>
                </div>
            </div>

            <div class="rounded-md border border-sidebar-border bg-card text-card-foreground shadow p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Descripción del Proyecto</label>
                        <textarea
                            v-model="form.descripcion"
                            required
                            rows="6"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            placeholder="Ej: Necesito un ropero de madera de pino de 2 metros de alto por 1.5 metros de ancho, con 3 cajones y espacio para colgar ropa. Color caoba oscuro..."
                        ></textarea>
                        <div v-if="form.errors.descripcion" class="text-xs text-red-500">{{ form.errors.descripcion }}</div>
                    </div>

                    <div class="pt-4 flex justify-end gap-2">
                        <Link href="/cotizaciones" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing" class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow hover:bg-zinc-800 disabled:opacity-50 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200 transition-colors">
                            <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Enviar Solicitud
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
