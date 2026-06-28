<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun, Baby, Users, Briefcase, Type, Eye, EyeOff, Clock, Palette } from 'lucide-vue-next';

const {
    appearance,
    theme,
    themeColor,
    fontSize,
    contrast,
    autoDayNight,
    updateAppearance,
    updateThemePreference,
    updateThemeColor,
    updateFontSize,
    updateContrast,
    updateAutoDayNight
} = useAppearance();

const themes = [
    { value: 'ninos', label: 'Niños', Icon: Baby, desc: 'Pasteles divertidos y bordes redondeados' },
    { value: 'jovenes', label: 'Jóvenes', Icon: Users, desc: 'Gamer neon y esquinas modernas' },
    { value: 'adultos', label: 'Adultos', Icon: Briefcase, desc: 'Caoba elegante, cantos rectos y sobrios' }
] as const;

const colorOptionsMap = {
    ninos: [
        { value: 'color-ninos-rosa', label: 'Rosa', hex: '#ff007f' },
        { value: 'color-ninos-celeste', label: 'Celeste', hex: '#00a8ff' },
        { value: 'color-ninos-amarillo', label: 'Amarillo', hex: '#ffd200' },
        { value: 'color-ninos-verde', label: 'Verde', hex: '#4cd137' }
    ],
    jovenes: [
        { value: 'color-jovenes-morado', label: 'Morado', hex: '#b39ddb' },
        { value: 'color-jovenes-verde', label: 'Verde', hex: '#aed581' },
        { value: 'color-jovenes-azul', label: 'Azul', hex: '#80deea' },
        { value: 'color-jovenes-rosa', label: 'Rosa', hex: '#f48fb1' },
        { value: 'color-jovenes-naranja', label: 'Naranja', hex: '#ffcc80' }
    ],
    adultos: [
        { value: 'color-adultos-caoba', label: 'Caoba', hex: '#78350f' },
        { value: 'color-adultos-bosque', label: 'Bosque', hex: '#0f766e' },
        { value: 'color-adultos-marino', label: 'Marino', hex: '#1e3a8a' },
        { value: 'color-adultos-ceniza', label: 'Ceniza', hex: '#4b5563' },
        { value: 'color-adultos-vino', label: 'Vino', hex: '#7f1d1d' }
    ]
} as const;

const fontSizes = [
    { value: 'normal', label: 'Normal' },
    { value: 'grande', label: 'Grande' },
    { value: 'muy-grande', label: 'Muy Grande' }
] as const;

const contrastOptions = [
    { value: 'normal', label: 'Normal', Icon: Eye },
    { value: 'alto', label: 'Alto Contraste', Icon: EyeOff }
] as const;

const appearanceTabs = [
    { value: 'light', Icon: Sun, label: 'Día (Claro)' },
    { value: 'dark', Icon: Moon, label: 'Noche (Oscuro)' },
    { value: 'system', Icon: Monitor, label: 'Sistema' }
] as const;
</script>

<template>
    <div class="space-y-8 max-w-2xl bg-card text-card-foreground p-6 rounded-xl border border-border shadow-sm">
        
        <!-- 1. SELECCIÓN DE TEMAS EDAD -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-bold text-stone-900 dark:text-white flex items-center gap-2">
                    <Users class="h-4 w-4 text-amber-500" />
                    Temas Visuales por Edad
                </h3>
                <p class="text-xs text-muted-foreground mt-0.5">Elige un estilo de diseño según tu preferencia visual.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <button
                    v-for="t in themes"
                    :key="t.value"
                    @click="updateThemePreference(t.value)"
                    :class="[
                        'p-4 rounded-xl text-left border transition-all flex flex-col justify-between gap-3 cursor-pointer group',
                        theme === t.value
                            ? 'bg-amber-50/50 border-amber-500 dark:bg-amber-950/20 dark:border-amber-500'
                            : 'border-stone-250 hover:border-stone-400 dark:border-stone-800 dark:hover:border-stone-700 bg-card'
                    ]"
                >
                    <div class="flex items-center justify-between w-full">
                        <div :class="[
                            'w-8 h-8 rounded-lg flex items-center justify-center transition-colors',
                            theme === t.value ? 'bg-amber-500 text-white' : 'bg-stone-100 dark:bg-stone-800 text-stone-500 dark:text-stone-400'
                        ]">
                            <component :is="t.Icon" class="w-4.5 h-4.5" />
                        </div>
                        <span v-if="theme === t.value" class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-stone-950 dark:text-white group-hover:text-amber-600 transition-colors">{{ t.label }}</span>
                        <p class="text-[10px] text-muted-foreground mt-0.5 leading-tight">{{ t.desc }}</p>
                    </div>
                </button>
            </div>
        </div>

        <!-- 1b. OPCIONES DE COLORES DEL TEMA -->
        <div v-if="theme !== 'adultos'" class="space-y-4 pt-4 border-t border-stone-100 dark:border-stone-850">
            <div>
                <h3 class="text-sm font-bold text-stone-900 dark:text-white flex items-center gap-2">
                    <Palette class="h-4 w-4 text-amber-500" />
                    Color de Acento del Tema
                </h3>
                <p class="text-xs text-muted-foreground mt-0.5">Elige un color primario para los botones y acentos del diseño seleccionado.</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <button
                    v-for="color in colorOptionsMap[theme as 'ninos'|'jovenes'|'adultos']"
                    :key="color.value"
                    @click="updateThemeColor(color.value)"
                    :class="[
                        'flex items-center gap-2.5 px-4 py-2.5 rounded-lg border text-xs font-semibold transition-all cursor-pointer select-none',
                        themeColor === color.value
                            ? 'bg-stone-50 border-stone-900 text-stone-900 dark:bg-stone-850 dark:border-stone-100 dark:text-white ring-2 ring-amber-500/20'
                            : 'border-stone-200 text-stone-600 dark:border-stone-800 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-850'
                    ]"
                >
                    <span 
                        class="w-3.5 h-3.5 rounded-full border border-black/10 dark:border-white/10 shrink-0" 
                        :style="{ backgroundColor: color.hex }"
                    ></span>
                    <span>{{ color.label }}</span>
                </button>
            </div>
        </div>

        <!-- 2. MODO DÍA/NOCHE Y HORARIOS -->
        <div class="space-y-4 border-t border-stone-100 dark:border-stone-850 pt-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-stone-900 dark:text-white flex items-center gap-2">
                        <Clock class="h-4 w-4 text-amber-500" />
                        Modo Día / Noche
                    </h3>
                    <p class="text-xs text-muted-foreground mt-0.5">Define cómo deseas alternar entre el modo claro y oscuro.</p>
                </div>
                
                <label class="inline-flex items-center gap-2.5 bg-stone-50 dark:bg-stone-950 px-3 py-1.5 rounded-lg border border-stone-200 dark:border-stone-800 cursor-pointer text-xs font-medium">
                    <input
                        type="checkbox"
                        :checked="autoDayNight"
                        @change="updateAutoDayNight(($event.target as HTMLInputElement).checked)"
                        class="rounded border-stone-300 text-amber-600 focus:ring-amber-500 w-4 h-4"
                    />
                    <span>Cambiar automáticamente según mi horario (18:00 a 06:00)</span>
                </label>
            </div>

            <!-- Controles manuales (se desactivan si es automático) -->
            <div class="space-y-2">
                <div :class="['flex flex-col sm:flex-row gap-1 rounded-lg bg-stone-100 p-1 dark:bg-stone-850 transition-opacity', { 'opacity-50 pointer-events-none': autoDayNight }]">
                    <button
                        v-for="{ value, Icon, label } in appearanceTabs"
                        :key="value"
                        @click="updateAppearance(value)"
                        :class="[
                            'flex flex-1 items-center justify-center sm:justify-start rounded-md px-3.5 py-2 transition-colors cursor-pointer whitespace-nowrap',
                            appearance === value && !autoDayNight
                                ? 'bg-white shadow-sm dark:bg-stone-700 dark:text-stone-100 font-medium'
                                : 'text-stone-500 hover:bg-stone-200/60 hover:text-black dark:text-stone-400 dark:hover:bg-stone-800/60',
                        ]"
                    >
                        <component :is="Icon" class="-ml-1 h-4 w-4" />
                        <span class="ml-1.5 text-xs">{{ label }}</span>
                    </button>
                </div>
                <p v-if="autoDayNight" class="text-[10px] text-amber-600 dark:text-amber-500 italic flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Tema controlado automáticamente por tu horario actual.
                </p>
            </div>
        </div>

        <!-- 3. ACCESIBILIDAD: TAMAÑO DE LETRAS & CONTRASTE -->
        <div class="space-y-6 border-t border-stone-100 dark:border-stone-850 pt-6">
            <div>
                <h3 class="text-sm font-bold text-stone-900 dark:text-white flex items-center gap-2">
                    <Type class="h-4 w-4 text-amber-500" />
                    Accesibilidad Visual
                </h3>
                <p class="text-xs text-muted-foreground mt-0.5">Modifica la visualización de los elementos para mejorar la legibilidad.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tamaño de letras -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-stone-700 dark:text-stone-300 block">Tamaño del Texto</span>
                    <div class="flex flex-col sm:flex-row gap-1 rounded-lg bg-stone-100 p-1 dark:bg-stone-850 w-full">
                        <button
                            v-for="sz in fontSizes"
                            :key="sz.value"
                            @click="updateFontSize(sz.value)"
                            :class="[
                                'flex-1 text-center rounded-md py-1.5 px-2 text-xs transition-colors cursor-pointer whitespace-nowrap',
                                fontSize === sz.value
                                    ? 'bg-white shadow-sm dark:bg-stone-700 dark:text-stone-100 font-bold'
                                    : 'text-stone-500 hover:bg-stone-200/60 hover:text-black dark:text-stone-400 dark:hover:bg-stone-800/60',
                            ]"
                        >
                            {{ sz.label }}
                        </button>
                    </div>
                </div>

                <!-- Contraste -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-stone-700 dark:text-stone-300 block">Modo de Contraste</span>
                    <div class="flex flex-col sm:flex-row gap-1 rounded-lg bg-stone-100 p-1 dark:bg-stone-850 w-full">
                        <button
                            v-for="co in contrastOptions"
                            :key="co.value"
                            @click="updateContrast(co.value)"
                            :class="[
                                'flex-1 flex items-center justify-center gap-1.5 rounded-md py-1.5 px-2 text-xs transition-colors cursor-pointer whitespace-nowrap',
                                contrast === co.value
                                    ? 'bg-white shadow-sm dark:bg-stone-700 dark:text-stone-100 font-bold'
                                    : 'text-stone-500 hover:bg-stone-200/60 hover:text-black dark:text-stone-400 dark:hover:bg-stone-800/60',
                            ]"
                        >
                            <component :is="co.Icon" class="w-3.5 h-3.5" />
                            <span>{{ co.label }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
