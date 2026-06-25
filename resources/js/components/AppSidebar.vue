<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';

import { Sidebar, SidebarContent, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarFooter } from '@/components/ui/sidebar';
import AppearanceDropdown from './AppearanceDropdown.vue';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Users,
    Shield,
    Key,
    Hammer,
    Boxes,
    Package,
    Truck,
    Tags,
    FileText,
    ClipboardList,
    ShoppingBag,
    CreditCard,
    History,
    ChevronsUpDown,
    Eye,
    ShieldCheck,
    Store
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePage } from '@inertiajs/vue3';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const page = usePage<any>();

const negociosItems: NavItem[] = [
    {
        title: 'Marketplace',
        href: '/marketplace',
        icon: Store,
    },
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Usuarios',
        href: '/usuarios',
        icon: Users,
    },
    {
        title: 'Roles',
        href: '/roles',
        icon: Shield,
    },
    {
        title: 'Permisos',
        href: '/permisos',
        icon: Key,
    },
    {
        title: 'Clientes',
        href: '/clientes',
        icon: Users,
    },
    {
        title: 'Cotizaciones',
        href: '/cotizaciones',
        icon: FileText,
    },
    {
        title: 'Ventas',
        href: '/ventas',
        icon: ShoppingBag,
    },
    {
        title: 'Pagos',
        href: '/pagos',
        icon: CreditCard,
    },
    {
        title: 'Bitácora',
        href: '/bitacoras',
        icon: History,
    },
];

const fabricaItems: NavItem[] = [
    {
        title: 'Insumos',
        href: '/insumos',
        icon: Boxes,
    },
    {
        title: 'Productos',
        href: '/productos',
        icon: Package,
    },
    {
        title: 'Tipos de Mueble',
        href: '/tipos',
        icon: Tags,
    },
    {
        title: 'Pedidos',
        href: '/pedidos',
        icon: ClipboardList,
    },
    {
        title: 'Carpinteros',
        href: '/carpinteros',
        icon: Hammer,
    },
    {
        title: 'Proveedores',
        href: '/proveedores',
        icon: Truck,
    },
];

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <SidebarMenuButton size="lg" class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground cursor-pointer">
                                <AppLogo />
                                <ChevronsUpDown class="ml-auto size-4 text-stone-400" />
                            </SidebarMenuButton>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-56 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 shadow-md align-start ml-2 mt-1">
                            <DropdownMenuLabel class="text-[10px] font-bold tracking-wider text-stone-400 dark:text-stone-500 uppercase px-3 py-1.5">
                                Cambiar Espacio
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator class="bg-stone-100 dark:bg-stone-850" />
                            <DropdownMenuItem :as-child="true">
                                <Link :href="route('home')" class="flex items-center gap-2.5 px-3 py-2 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-850 cursor-pointer">
                                    <Eye class="h-4.5 w-4.5 text-emerald-500" />
                                    <span class="font-medium">Portal Público</span>
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="space-y-4">
            <NavMain title="Negocios y Control" :items="negociosItems" />
            <NavMain title="Paquete Fábrica" :items="fabricaItems" />
        </SidebarContent>

        <SidebarFooter class="p-4 border-t border-sidebar-border/50 flex justify-center group-data-[collapsible=icon]:hidden">
            <AppearanceDropdown />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
