<script setup lang="ts">

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md overflow-hidden bg-transparent">
        <img :src="($page.props.app_url || '') + '/images/logo.png'" alt="Mueblería Cruz Logo" class="size-full object-cover" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate font-semibold leading-none text-stone-900 dark:text-white">Mueblería Cruz</span>
    </div>
</template>
