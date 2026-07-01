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
        
        // Validar tipo de archivo (sólo png o jpg/jpeg)
        if (file.type !== 'image/png' && file.type !== 'image/jpeg' && file.type !== 'image/jpg') {
            alert(`El archivo "${file.name}" no es admitido. Solo se permiten imágenes JPG o PNG.`);
            continue;
        }
        
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
                <form @submit.prevent="submit" class="space-y-6">
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

                    <div class="space-y-3">
                        <label class="text-sm font-medium block">Imágenes de Referencia (Opcional)</label>
                        
                        <!-- Caja de carga con diseño moderno -->
                        <div class="border-2 border-dashed border-muted-foreground/30 hover:border-primary/50 transition-colors rounded-lg p-6 flex flex-col items-center justify-center gap-2 cursor-pointer relative bg-muted/20">
                            <input
                                type="file"
                                id="imagenes-input"
                                multiple
                                accept="image/png, image/jpeg, image/jpg"
                                @change="handleFileChange"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            />
                            
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                            
                            <div class="text-sm font-medium text-foreground text-center">
                                Arrastra o selecciona tus imágenes aquí
                            </div>
                            <div class="text-xs text-muted-foreground text-center">
                                Solo se admiten formatos <span class="font-semibold text-foreground">PNG</span> o <span class="font-semibold text-foreground">JPG</span>
                            </div>
                        </div>
                        
                        <!-- Contenedor de previsualizaciones -->
                        <div v-if="imagePreviews.length > 0" class="mt-4">
                            <div class="text-xs font-semibold text-muted-foreground mb-2">Imágenes seleccionadas ({{ imagePreviews.length }}):</div>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                <div v-for="(preview, index) in imagePreviews" :key="index" class="relative group aspect-square rounded-md overflow-hidden border bg-muted">
                                    <img :src="preview" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-black/45 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button 
                                            type="button" 
                                            @click="removeImage(index)" 
                                            class="bg-red-600 hover:bg-red-700 text-white rounded-full p-2 shadow-lg transition-transform hover:scale-110"
                                            title="Eliminar imagen"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                        </button>
                                    </div>
                                </div>
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
