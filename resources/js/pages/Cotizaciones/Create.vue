<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { LoaderCircle, ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Cotizaciones', href: route('cotizaciones.index') },
    { title: 'Solicitar', href: route('cotizaciones.create') },
];

const form = useForm({
    descripcion: '',
    imagenes: [] as File[],
});

const imagePreviews = ref<string[]>([]);

const handleFileChange = (e: Event) => {
    const files = (e.target as HTMLInputElement).files;
    if (!files) return;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        form.imagenes.push(file);
        
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target?.result as string);
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = (index: number) => {
    form.imagenes.splice(index, 1);
    imagePreviews.value.splice(index, 1);
};

const submit = () => {
    form.post(route('cotizaciones.store'), {
        forceFormData: true,
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
                <Link :href="route('cotizaciones.index')" class="p-2 rounded-md hover:bg-muted transition-colors">
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

                    <div class="space-y-2">
                        <label class="text-sm font-medium">Imágenes de Referencia (Opcional)</label>
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            @change="handleFileChange"
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm file:border-0 file:bg-transparent file:text-sm file:font-medium hover:file:cursor-pointer"
                        />
                        <div v-if="imagePreviews.length > 0" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            <div v-for="(preview, index) in imagePreviews" :key="index" class="relative group">
                                <img :src="preview" class="w-full h-24 object-cover rounded-md border" />
                                <button type="button" @click="removeImage(index)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end gap-2">
                        <Link :href="route('cotizaciones.index')" class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors">
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
