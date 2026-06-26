<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
            
            <footer class="mt-auto py-6 border-t border-sidebar-border bg-sidebar text-sidebar-foreground text-center">
                <p class="text-sm">
                    Visitas a esta página: <span class="font-bold">{{ $page.props.current_page_visits }}</span>
                </p>
                <p class="text-xs text-muted-foreground mt-1">
                    &copy; {{ new Date().getFullYear() }} Carpintería Cruz S.A. Todos los derechos reservados.
                </p>
            </footer>
        </AppContent>
    </AppShell>
</template>
