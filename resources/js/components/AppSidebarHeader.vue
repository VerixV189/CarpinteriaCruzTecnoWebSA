<script setup lang="ts">
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { BreadcrumbItemType } from '@/types';
import AppLogo from '@/components/AppLogo.vue';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    }
);

const page = usePage<any>();
const auth = computed(() => page.props.auth);
const { getInitials } = useInitials();
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2 min-w-0 flex-1">
            <SidebarTrigger class="-ml-1 shrink-0" />
            <div class="flex items-center md:hidden">
                <AppLogo />
            </div>
        </div>

        <div v-if="auth?.user" class="flex items-center gap-4 shrink-0 ml-2">
            <!-- User Info (Name and Email stacked) -->
            <div class="flex flex-col text-right max-w-[120px] sm:max-w-none">
                <span class="text-xs sm:text-sm font-semibold text-stone-950 dark:text-white leading-none mb-0.5 truncate">
                    {{ auth?.user?.name }}
                </span>
                <span class="text-[10px] sm:text-xs text-stone-500 dark:text-stone-400 leading-none truncate">
                    {{ auth?.user?.email }}
                </span>
            </div>

            <!-- Avatar & Role Badge Container -->
            <div class="flex flex-col items-center select-none min-w-10">
                <Avatar class="h-8 w-8 overflow-hidden rounded-lg border border-sidebar-border/50">
                    <AvatarImage v-if="auth?.user?.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                    <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white text-[10px]">
                        {{ getInitials(auth?.user?.name) }}
                    </AvatarFallback>
                </Avatar>
                <!-- Role Badge below Avatar -->
                <span class="mt-0.5 text-[8px] font-bold px-1 py-0.2 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 uppercase tracking-wide leading-none">
                    {{ auth?.user?.rol?.nombre || 'USER' }}
                </span>
            </div>

            <!-- Logout Icon Button -->
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-white p-2 rounded-md hover:bg-stone-100 dark:hover:bg-stone-850 transition-colors flex items-center justify-center"
                title="Cerrar Sesión"
            >
                <LogOut class="h-4.5 w-4.5" />
            </Link>
        </div>
    </header>
</template>>
