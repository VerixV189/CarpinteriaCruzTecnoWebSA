<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import NavigationHeader from '@/components/NavigationHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

useAppearance();

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<any>();
const isClienteView = computed(() => {
    // Si el usuario es Cliente (rol_id = 2) o no está autenticado (invitado), siempre ve la interfaz pública
    if (!page.props.auth.user || page.props.auth.user.rol_id === 2) {
        return true;
    }
    
    // Los administradores (1) y carpinteros (3) siempre ven la vista de administración con barra lateral
    return false;
});
</script>

<template>
    <!-- Si es Administrador (1) o Carpintero (3), mostrar layout de administración con Sidebar -->
    <AppSidebarLayout v-if="!isClienteView" :breadcrumbs="breadcrumbs">
        <slot />
    </AppSidebarLayout>

    <!-- Si es Cliente (2) o invitado, mostrar layout público con NavigationHeader arriba -->
    <div v-else class="min-h-screen bg-background text-foreground flex flex-col font-sans transition-colors duration-300">
        <NavigationHeader />
        
        <main class="flex-1 w-full max-w-7xl mx-auto px-6 py-8">
            <slot />
        </main>

        <footer class="bg-muted border-t border-border py-8 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-6 text-center text-xs text-muted-foreground">
                <p>Visitas a esta página: <span class="font-bold">{{ $page.props.current_page_visits }}</span></p>
                <p class="mt-1">&copy; {{ new Date().getFullYear() }} Mueblería Cruz S.A. Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>
</template>
