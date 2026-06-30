<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LogOut, Menu, ChevronsUpDown, ShieldCheck, Home, Store, FileText, Package, CreditCard, User } from 'lucide-vue-next';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const page = usePage<any>();
const { getInitials } = useInitials();
</script>

<template>
    <!-- Navigation Header -->
    <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between border-b border-stone-200 dark:border-stone-800">
        
        <!-- LEFT SIDE: Mobile Menu & Logo -->
        <div class="flex items-center gap-2 sm:gap-6">
            
            <!-- Mobile/Desktop Navigation Menu Toggle -->
            <div class="flex items-center">
                <Sheet>
                    <SheetTrigger :as-child="true">
                        <button class="text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-white p-2 rounded-md hover:bg-stone-100 dark:hover:bg-stone-850 transition-colors">
                            <Menu class="h-6 w-6" />
                        </button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-[300px] p-6 bg-white dark:bg-stone-900 border-r border-stone-200 dark:border-stone-800 flex flex-col justify-between">
                        <div class="space-y-6">
                            <SheetHeader class="flex justify-start text-left border-b border-stone-100 dark:border-stone-800 pb-4">
                                <div class="flex items-center gap-2">
                                    <img :src="($page.props.app_url || '') + '/images/logo.png'" alt="Mueblería Cruz Logo" class="h-6 w-6 object-contain rounded-md" />
                                    <SheetTitle class="text-lg font-bold text-stone-900 dark:text-white">Mueblería Cruz</SheetTitle>
                                </div>
                            </SheetHeader>
                            
                            <!-- Links when user is logged in -->
                            <nav v-if="page.props.auth.user" class="flex flex-col gap-4">
                                <Link :href="route('home')" class="flex items-center gap-2.5 text-sm font-semibold text-stone-600 dark:text-stone-300 hover:text-amber-600 dark:hover:text-amber-500 transition-colors py-2 border-b border-stone-100 dark:border-stone-800/50">
                                    <Home class="h-4.5 w-4.5 text-stone-400" />
                                    <span>Inicio</span>
                                </Link>
                                <Link :href="route('marketplace.index')" class="flex items-center gap-2.5 text-sm font-semibold text-stone-600 dark:text-stone-300 hover:text-amber-600 dark:hover:text-amber-500 transition-colors py-2 border-b border-stone-100 dark:border-stone-800/50">
                                    <Store class="h-4.5 w-4.5 text-stone-400" />
                                    <span>Catálogo Productos</span>
                                </Link>
                                <Link :href="route('cotizaciones.index')" class="flex items-center gap-2.5 text-sm font-semibold text-stone-600 dark:text-stone-300 hover:text-amber-600 dark:hover:text-amber-500 transition-colors py-2 border-b border-stone-100 dark:border-stone-800/50">
                                    <FileText class="h-4.5 w-4.5 text-stone-400" />
                                    <span>Cotizaciones</span>
                                </Link>
                                <Link :href="route('pedidos.index')" class="flex items-center gap-2.5 text-sm font-semibold text-stone-600 dark:text-stone-300 hover:text-amber-600 dark:hover:text-amber-500 transition-colors py-2 border-b border-stone-100 dark:border-stone-800/50">
                                    <Package class="h-4.5 w-4.5 text-stone-400" />
                                    <span>Mi Pedido</span>
                                </Link>
                                <Link :href="route('pagos.index')" class="flex items-center gap-2.5 text-sm font-semibold text-stone-600 dark:text-stone-300 hover:text-amber-600 dark:hover:text-amber-500 transition-colors py-2 border-b border-stone-100 dark:border-stone-800/50">
                                    <CreditCard class="h-4.5 w-4.5 text-stone-400" />
                                    <span>Mis Pagos</span>
                                </Link>
                                <Link :href="route('profile.edit')" class="flex items-center gap-2.5 text-sm font-semibold text-stone-600 dark:text-stone-300 hover:text-amber-600 dark:hover:text-amber-500 transition-colors py-2 border-b border-stone-100 dark:border-stone-800/50">
                                    <User class="h-4.5 w-4.5 text-stone-400" />
                                    <span>Mi Perfil</span>
                                </Link>
                            </nav>
                            <nav v-else class="flex flex-col gap-4">
                                <Link :href="route('login')" class="inline-flex items-center justify-center rounded-lg border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 px-4 py-2 text-sm font-semibold hover:bg-stone-50 dark:hover:bg-stone-700 transition-all shadow-sm w-full text-center">
                                    Iniciar Sesión
                                </Link>
                                <Link :href="route('register')" class="inline-flex items-center justify-center rounded-lg bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow transition-all w-full text-center">
                                    Registrarse
                                </Link>
                            </nav>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>

            <!-- Dropdown para cambiar de espacio (solo Admin o Carpintero) -->
            <DropdownMenu v-if="page.props.auth.user && page.props.auth.user.rol?.nombre !== 'Cliente'">
                <DropdownMenuTrigger class="flex items-center gap-1 sm:gap-2 outline-none select-none cursor-pointer group">
                    <img :src="($page.props.app_url || '') + '/images/logo.png'" alt="Mueblería Cruz Logo" class="h-7 w-7 sm:h-8 sm:w-8 object-contain rounded-md" />
                    <span class="text-sm sm:text-xl font-bold tracking-tight text-stone-900 dark:text-white group-hover:text-amber-600 transition-colors leading-tight max-w-[80px] sm:max-w-none whitespace-normal text-left">Mueblería Cruz</span>
                    <ChevronsUpDown class="h-4 w-4 text-stone-400 group-hover:text-stone-600 dark:group-hover:text-stone-200 transition-colors" />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="start" class="w-56 mt-1 bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-800 shadow-md">
                    <DropdownMenuLabel class="text-[10px] font-bold tracking-wider text-stone-400 dark:text-stone-500 uppercase px-3 py-1.5">
                        Cambiar Espacio
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator class="bg-stone-100 dark:bg-stone-850" />
                    <DropdownMenuItem :as-child="true">
                        <Link :href="route('dashboard')" class="flex items-center gap-2.5 px-3 py-2 text-sm text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-850 cursor-pointer">
                            <ShieldCheck class="h-4.5 w-4.5 text-amber-500" />
                            <span class="font-medium">Panel Administrativo</span>
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <!-- Logo y marca estáticos para Clientes e Invitados -->
            <div v-else class="flex items-center gap-1 sm:gap-2 select-none">
                <img :src="($page.props.app_url || '') + '/images/logo.png'" alt="Mueblería Cruz Logo" class="h-7 w-7 sm:h-8 sm:w-8 object-contain rounded-md" />
                <span class="text-sm sm:text-xl font-bold tracking-tight text-stone-900 dark:text-white leading-tight max-w-[80px] sm:max-w-none whitespace-normal">Mueblería Cruz</span>
            </div>
        </div>


        <!-- Desktop & Mobile Navigation Actions -->
        <nav class="flex items-center gap-2 sm:gap-4">
            <template v-if="page.props.auth.user">

                <!-- User Info (Name and Email stacked) -->
                <div class="hidden sm:flex flex-col text-right max-w-[90px] sm:max-w-none overflow-hidden">
                    <span class="text-[10px] sm:text-sm font-semibold text-stone-950 dark:text-white leading-none mb-0.5 truncate">
                        {{ page.props.auth.user.name }}
                    </span>
                    <span class="text-[8px] sm:text-xs text-stone-500 dark:text-stone-400 leading-none truncate">
                        {{ page.props.auth.user.email }}
                    </span>
                </div>

                <!-- Avatar & Role Badge Container -->
                <div class="flex flex-col items-center select-none min-w-10">
                    <Avatar class="h-8 w-8 overflow-hidden rounded-lg border border-stone-200 dark:border-stone-850">
                        <AvatarImage v-if="page.props.auth.user.avatar" :src="page.props.auth.user.avatar" :alt="page.props.auth.user.name" />
                        <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white text-[10px]">
                            {{ getInitials(page.props.auth.user.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <!-- Role Badge below Avatar -->
                    <span class="mt-0.5 text-[8px] font-bold px-1 py-0.2 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 uppercase tracking-wide leading-none">
                        {{ page.props.auth.user.rol?.nombre || 'USER' }}
                    </span>
                </div>

                <!-- Logout Icon Button -->
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-stone-500 hover:text-stone-900 dark:text-stone-400 dark:hover:text-white p-2 rounded-md hover:bg-stone-100 dark:hover:bg-stone-850 transition-colors flex items-center justify-center h-9 w-9"
                    title="Cerrar Sesión"
                >
                    <LogOut class="h-4.5 w-4.5" />
                </Link>
            </template>
            <template v-else>
                <Link
                    :href="route('login')"
                    class="text-sm font-medium text-stone-600 dark:text-stone-300 hover:text-stone-900 dark:hover:text-white transition-colors"
                >
                    Iniciar Sesión
                </Link>
                <Link
                    :href="route('register')"
                    class="inline-flex items-center justify-center rounded-lg border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 px-4 py-2 text-sm font-medium hover:bg-stone-50 dark:hover:bg-stone-700 transition-all shadow-sm"
                >
                    Registrarse
                </Link>
            </template>
        </nav>
    </header>
</template>
