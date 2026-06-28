import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

export function updateTheme(value: Appearance) {
    if (value === 'system') {
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

const handleSystemThemeChange = () => {
    const autoDayNight = localStorage.getItem('auto_day_night') !== 'false';
    if (!autoDayNight) {
        const currentAppearance = localStorage.getItem('appearance') as Appearance | null;
        updateTheme(currentAppearance || 'system');
    }
};

export function applyGlobalPreferences() {
    // 1. Tema del sitio
    const savedTheme = localStorage.getItem('theme') || 'adultos';
    document.documentElement.classList.remove('theme-ninos', 'theme-jovenes', 'theme-adultos');
    document.documentElement.classList.add(`theme-${savedTheme}`);

    // 1b. Color del tema
    const defaultColor = savedTheme === 'ninos' ? 'color-ninos-rosa' : (savedTheme === 'jovenes' ? 'color-jovenes-morado' : 'color-adultos-caoba');
    const savedColor = localStorage.getItem('theme_color') || defaultColor;
    
    // Remover todas las posibles clases de color antes de inyectar la activa
    document.documentElement.classList.forEach(className => {
        if (className.startsWith('color-')) {
            document.documentElement.classList.remove(className);
        }
    });
    document.documentElement.classList.add(savedColor);

    // 2. Tamaño de letra (Accesibilidad)
    const savedFontSize = localStorage.getItem('font_size') || 'normal';
    document.documentElement.classList.remove('font-size-normal', 'font-size-grande', 'font-size-muy-grande');
    document.documentElement.classList.add(`font-size-${savedFontSize}`);

    // 3. Contraste (Accesibilidad)
    const savedContrast = localStorage.getItem('contrast') || 'normal';
    document.documentElement.classList.remove('contrast-normal', 'contrast-alto');
    document.documentElement.classList.add(`contrast-${savedContrast}`);

    // 4. Modo Día/Noche según horario
    const autoDayNight = localStorage.getItem('auto_day_night') !== 'false';
    if (autoDayNight) {
        const hour = new Date().getHours();
        // Noche entre 18:00 (6 PM) y 06:00 (6 AM)
        const isNight = hour >= 18 || hour < 6;
        document.documentElement.classList.toggle('dark', isNight);
    } else {
        const savedAppearance = localStorage.getItem('appearance') as Appearance | null;
        updateTheme(savedAppearance || 'system');
    }
}

export function initializeTheme() {
    applyGlobalPreferences();
    mediaQuery.addEventListener('change', handleSystemThemeChange);
}

export function useAppearance() {
    const appearance = ref<Appearance>('system');
    const theme = ref<string>('adultos');
    const themeColor = ref<string>('color-adultos-caoba');
    const fontSize = ref<string>('normal');
    const contrast = ref<string>('normal');
    const autoDayNight = ref<boolean>(true);

    onMounted(() => {
        applyGlobalPreferences();
        appearance.value = (localStorage.getItem('appearance') as Appearance) || 'system';
        theme.value = localStorage.getItem('theme') || 'adultos';
        
        const defaultColor = theme.value === 'ninos' ? 'color-ninos-rosa' : (theme.value === 'jovenes' ? 'color-jovenes-morado' : 'color-adultos-caoba');
        themeColor.value = localStorage.getItem('theme_color') || defaultColor;
        
        fontSize.value = localStorage.getItem('font_size') || 'normal';
        contrast.value = localStorage.getItem('contrast') || 'normal';
        autoDayNight.value = localStorage.getItem('auto_day_night') !== 'false';
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;
        localStorage.setItem('appearance', value);
        applyGlobalPreferences();
    }

    function updateThemePreference(value: string) {
        theme.value = value;
        localStorage.setItem('theme', value);
        
        // Auto default color on age-theme switch
        const defaultColor = value === 'ninos' ? 'color-ninos-rosa' : (value === 'jovenes' ? 'color-jovenes-morado' : 'color-adultos-caoba');
        themeColor.value = defaultColor;
        localStorage.setItem('theme_color', defaultColor);
        
        applyGlobalPreferences();
    }

    function updateThemeColor(value: string) {
        themeColor.value = value;
        localStorage.setItem('theme_color', value);
        applyGlobalPreferences();
    }

    function updateFontSize(value: string) {
        fontSize.value = value;
        localStorage.setItem('font_size', value);
        applyGlobalPreferences();
    }

    function updateContrast(value: string) {
        contrast.value = value;
        localStorage.setItem('contrast', value);
        applyGlobalPreferences();
    }

    function updateAutoDayNight(value: boolean) {
        autoDayNight.value = value;
        localStorage.setItem('auto_day_night', value ? 'true' : 'false');
        applyGlobalPreferences();
    }

    return {
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
        updateAutoDayNight,
        applyGlobalPreferences
    };
}
