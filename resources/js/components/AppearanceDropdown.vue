<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun, ChevronDown } from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { computed } from 'vue';

const { appearance, updateAppearance } = useAppearance();

const currentIcon = computed(() => {
    if (appearance.value === 'light') return Sun;
    if (appearance.value === 'dark') return Moon;
    return Monitor;
});

const currentLabel = computed(() => {
    if (appearance.value === 'light') return 'Claro';
    if (appearance.value === 'dark') return 'Oscuro';
    return 'Sistema';
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-850 bg-white dark:bg-stone-900 hover:bg-stone-50 dark:hover:bg-stone-850 text-stone-700 dark:text-stone-300 transition-colors cursor-pointer outline-none select-none text-sm font-medium">
            <component :is="currentIcon" class="h-4 w-4" />
            <span>{{ currentLabel }}</span>
            <ChevronDown class="h-3.5 w-3.5 opacity-60" />
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-36 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 shadow-md">
            <DropdownMenuItem @click="updateAppearance('light')" class="flex items-center gap-2 px-3 py-2 text-sm text-stone-750 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-850 cursor-pointer">
                <Sun class="h-4 w-4 text-amber-500" />
                <span class="font-medium">Claro</span>
            </DropdownMenuItem>
            <DropdownMenuItem @click="updateAppearance('dark')" class="flex items-center gap-2 px-3 py-2 text-sm text-stone-750 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-850 cursor-pointer">
                <Moon class="h-4 w-4 text-indigo-500" />
                <span class="font-medium">Oscuro</span>
            </DropdownMenuItem>
            <DropdownMenuItem @click="updateAppearance('system')" class="flex items-center gap-2 px-3 py-2 text-sm text-stone-750 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-850 cursor-pointer">
                <Monitor class="h-4 w-4 text-emerald-500" />
                <span class="font-medium">Sistema</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
