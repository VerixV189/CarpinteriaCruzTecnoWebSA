<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: { url: string | null; label: string; active: boolean }[];
}>();
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap items-center justify-end gap-1 px-4 py-3 bg-card border-t border-sidebar-border">
        <template v-for="(link, key) in links" :key="key">
            <div 
                v-if="link.url === null" 
                class="px-3 py-1.5 text-sm text-muted-foreground border border-border rounded-md bg-muted/30 cursor-not-allowed" 
                v-html="link.label"
            ></div>
            <Link 
                v-else 
                :href="link.url" 
                class="px-3 py-1.5 text-sm border rounded-md transition-colors"
                :class="link.active ? 'bg-stone-900 text-white dark:bg-stone-100 dark:text-stone-900 border-stone-900 dark:border-stone-100 font-bold shadow-sm' : 'bg-background hover:bg-muted text-foreground border-input'"
                v-html="link.label"
                preserve-scroll
            />
        </template>
    </div>
</template>
