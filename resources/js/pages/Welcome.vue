<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Hammer, Ruler, Package, Phone, Mail, MapPin, LogIn } from 'lucide-vue-next';
import NavigationHeader from '@/components/NavigationHeader.vue';
import KidEmoji from '@/components/KidEmoji.vue';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="Mueblería Cruz - Calidad para tu Hogar" />

    <div class="min-h-screen bg-background text-foreground flex flex-col font-sans transition-colors duration-300">
        <NavigationHeader />

        <!-- Hero Section -->
        <main class="flex-1">
            <section class="max-w-7xl mx-auto px-6 py-12 md:py-24 grid md:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900">
                        <Ruler class="h-3 w-3" /> Muebles a Medida & Alta Calidad
                    </span>
                    <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight text-stone-900 dark:text-white leading-tight">
                        <KidEmoji /> Transformamos la <span class="text-amber-600 dark:text-amber-500">madera</span> en arte para tu hogar <KidEmoji />
                    </h1>
                    <p class="text-lg text-stone-600 dark:text-stone-300 max-w-xl">
                        En Mueblería Cruz creamos soluciones exclusivas de mobiliario, cocinas a medida, vestidores y estructuras de madera con acabados premium y durabilidad garantizada.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center justify-center rounded-lg bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 px-6 py-3 text-base font-semibold text-white shadow-lg transition-all"
                        >
                            <KidEmoji /> Solicitar Cotización
                        </Link>
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center gap-2 justify-center rounded-lg border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-800 px-6 py-3 text-base font-semibold hover:bg-stone-50 dark:hover:bg-stone-700 transition-all shadow-sm"
                        >
                            <KidEmoji /> <LogIn class="h-4 w-4" /> Acceder
                        </Link>
                    </div>
                </div>

                <!-- Visual Mockup Card -->
                <div class="relative bg-gradient-to-tr from-amber-500 to-amber-700 dark:from-amber-600 dark:to-amber-900 rounded-3xl p-8 shadow-2xl text-white space-y-6 overflow-hidden min-h-[350px] flex flex-col justify-between">
                    <div class="absolute -right-16 -top-16 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-black/20 rounded-full blur-3xl pointer-events-none"></div>
                    
                    <div class="z-10 flex justify-between items-start">
                        <span class="text-2xl font-bold tracking-tight">Mueblería Cruz</span>
                        <Package class="h-8 w-8 opacity-80" />
                    </div>
                    
                    <div class="z-10 space-y-4">
                        <blockquote class="text-xl italic font-medium leading-relaxed">
                            "Excelente trabajo en nuestra cocina y clósets. La calidad de la madera y el acabado superaron nuestras expectativas."
                        </blockquote>
                        <div>
                            <p class="font-semibold">Familia Rodríguez</p>
                            <p class="text-xs opacity-70">Proyecto de Cocina y Vestidor Integral</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Grid -->
            <section class="bg-card border-y border-border py-16 transition-colors duration-300">
                <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-8">
                    <div class="space-y-3 p-4 rounded-2xl hover:bg-stone-50 dark:hover:bg-stone-900 transition-all">
                        <div class="h-10 w-10 rounded-lg bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-500">
                            <Ruler class="h-6 w-6" />
                        </div>
                        <h3 class="text-lg font-bold text-stone-900 dark:text-white"><KidEmoji /> Diseño 100% Personalizado</h3>
                        <p class="text-sm text-stone-600 dark:text-stone-400">
                            Nos adaptamos exactamente a tus espacios y requerimientos estéticos.
                        </p>
                    </div>

                    <div class="space-y-3 p-4 rounded-2xl hover:bg-stone-50 dark:hover:bg-stone-900 transition-all">
                        <div class="h-10 w-10 rounded-lg bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-500">
                            <Hammer class="h-6 w-6" />
                        </div>
                        <h3 class="text-lg font-bold text-stone-900 dark:text-white"><KidEmoji /> Artesanía de Excelencia</h3>
                        <p class="text-sm text-stone-600 dark:text-stone-400">
                            Madera seleccionada y mano de obra calificada para resultados impecables.
                        </p>
                    </div>

                    <div class="space-y-3 p-4 rounded-2xl hover:bg-stone-50 dark:hover:bg-stone-900 transition-all">
                        <div class="h-10 w-10 rounded-lg bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-500">
                            <Package class="h-6 w-6" />
                        </div>
                        <h3 class="text-lg font-bold text-stone-900 dark:text-white"><KidEmoji /> Cotización y Control de Pedidos</h3>
                        <p class="text-sm text-stone-600 dark:text-stone-400">
                            Regístrate para realizar y monitorear tus pedidos y cotizaciones en línea.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-muted border-t border-border py-12 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-8">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <img src="/images/logo.png" alt="Mueblería Cruz Logo" class="h-6 w-6 object-contain rounded-md" />
                        <span class="text-lg font-bold tracking-tight text-stone-900 dark:text-white">Mueblería Cruz</span>
                    </div>
                    <p class="text-xs text-stone-500 dark:text-stone-400">
                        Creando espacios únicos y duraderos con el calor y la nobleza de la madera.
                    </p>
                </div>

                <div class="space-y-3">
                    <h4 class="text-sm font-semibold tracking-wide uppercase text-stone-950 dark:text-white">Contacto</h4>
                    <ul class="space-y-2 text-sm text-stone-600 dark:text-stone-400">
                        <li class="flex items-center gap-2">
                            <Phone class="h-4 w-4 text-amber-600" /> +591 76543210
                        </li>
                        <li class="flex items-center gap-2">
                            <Mail class="h-4 w-4 text-amber-600" /> contacto@muebleriacruz.com
                        </li>
                        <li class="flex items-center gap-2">
                            <MapPin class="h-4 w-4 text-amber-600" /> Santa Cruz, Bolivia
                        </li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <h4 class="text-sm font-semibold tracking-wide uppercase text-stone-950 dark:text-white">Horarios</h4>
                    <p class="text-sm text-stone-600 dark:text-stone-400">
                        Lunes a Viernes: 8:00 AM - 6:00 PM<br />
                        Sábado: 9:00 AM - 1:00 PM
                    </p>
                </div>
            </div>
            <div class="max-w-7xl mx-auto px-6 pt-8 mt-8 border-t border-stone-200 dark:border-stone-800 text-center text-xs text-stone-500 dark:text-stone-400">
                &copy; {{ new Date().getFullYear() }} Mueblería Cruz S.A. Todos los derechos reservados.
            </div>
        </footer>
    </div>
</template>
