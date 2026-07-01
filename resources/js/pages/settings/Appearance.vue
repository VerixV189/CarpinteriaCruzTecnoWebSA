<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Apariencia',
        href: route('appearance'),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Configuración de Apariencia" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Configuración de Apariencia" description="Actualiza la configuración de apariencia de tu cuenta y el tema visual." />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
