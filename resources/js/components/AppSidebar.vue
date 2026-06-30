<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';

import { Sidebar, SidebarContent, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarFooter } from '@/components/ui/sidebar';
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
    Store,
    User
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
        title: 'Dashboard',
        href: route('dashboard') as string,
        icon: LayoutGrid,
    },
    {
        title: 'Cotizaciones',
        href: route('cotizaciones.index') as string,
        icon: FileText,
    },
    {
        title: 'Ventas',
        href: route('ventas.index') as string,
        icon: ShoppingBag,
    },
    {
        title: 'Pagos',
        href: route('pagos.index') as string,
        icon: CreditCard,
    },
    {
        title: 'Clientes',
        href: route('clientes.index') as string,
        icon: Users,
    },
    {
        title: 'Usuarios',
        href: route('usuarios.index') as string,
        icon: Users,
    },
    {
        title: 'Roles',
        href: route('roles.index') as string,
        icon: Shield,
    },
    {
        title: 'Permisos',
        href: route('permisos.index') as string,
        icon: Key,
    },
    {
        title: 'Bitácora',
        href: route('bitacoras.index') as string,
        icon: History,
    },
];

const fabricaItems: NavItem[] = [
    {
        title: 'Pedidos',
        href: route('pedidos.index') as string,
        icon: ClipboardList,
    },
    {
        title: 'Productos',
        href: route('productos.index') as string,
        icon: Package,
    },
    {
        title: 'Tipos de Mueble',
        href: route('tipos.index') as string,
        icon: Tags,
    },
    {
        title: 'Insumos',
        href: route('insumos.index') as string,
        icon: Boxes,
    },
    {
        title: 'Carpinteros',
        href: route('carpinteros.index') as string,
        icon: Hammer,
    },
    {
        title: 'Proveedores',
        href: route('proveedores.index') as string,
        icon: Truck,
    },
];

const personalItems: NavItem[] = [
    {
        title: 'Mi Perfil',
        href: route('profile.edit') as string,
        icon: User,
    }
];

import { computed } from 'vue';

const currentUserRole = computed(() => page.props.auth.user?.rol_id);

const filteredNegociosItems = computed(() => {
    const roleId = currentUserRole.value;
    return negociosItems.filter(item => {
        // Admin ve todo
        if (roleId === 1) return true;
        
        // Cliente (2)
        if (roleId === 2) {
            return ['Marketplace', 'Cotizaciones', 'Ventas', 'Pagos'].includes(item.title);
        }
        
        // Carpintero (3)
        if (roleId === 3) {
            return ['Dashboard', 'Cotizaciones'].includes(item.title);
        }
        
        return false;
    });
});

const filteredFabricaItems = computed(() => {
    const roleId = currentUserRole.value;
    return fabricaItems.filter(item => {
        // Admin ve todo
        if (roleId === 1) return true;
        
        // Cliente (2)
        if (roleId === 2) {
            return ['Pedidos'].includes(item.title);
        }
        
        // Carpintero (3)
        if (roleId === 3) {
            return ['Insumos', 'Productos', 'Tipos de Mueble', 'Pedidos', 'Proveedores'].includes(item.title);
        }
        
        return false;
    });
});

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" class="hover:bg-transparent cursor-default">
                        <AppLogo />
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="space-y-4">
            <NavMain v-if="filteredNegociosItems.length > 0" title="Negocios y Control" :items="filteredNegociosItems" />
            <NavMain v-if="filteredFabricaItems.length > 0" title="Paquete Fábrica" :items="filteredFabricaItems" />
            <NavMain title="Personal" :items="personalItems" />
        </SidebarContent>
    </Sidebar>
    <slot />
</template>
