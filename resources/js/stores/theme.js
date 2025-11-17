import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';
import axios from 'axios';

export const useThemeStore = defineStore('theme', () => {
    // Estado
    const currentTheme = ref('jovenes'); // ninos, jovenes, adultos
    const isDarkMode = ref(false);
    const isAutoMode = ref(true); // Auto día/noche según horario servidor
    const serverTimeMode = ref('day'); // 'day' o 'night' del servidor

    // Temas disponibles
    const availableThemes = [
        { 
            id: 'ninos', 
            name: 'Niños',
            description: 'Colores brillantes y divertidos',
            icon: '🎨'
        },
        { 
            id: 'jovenes', 
            name: 'Jóvenes',
            description: 'Moderno y dinámico',
            icon: '🚀'
        },
        { 
            id: 'adultos', 
            name: 'Adultos',
            description: 'Elegante y profesional',
            icon: '💼'
        }
    ];

    // Computed
    const activeTheme = computed(() => {
        return availableThemes.find(t => t.id === currentTheme.value);
    });

    const effectiveMode = computed(() => {
        if (isAutoMode.value) {
            return serverTimeMode.value === 'night' ? 'dark' : 'light';
        }
        return isDarkMode.value ? 'dark' : 'light';
    });

    const themeClasses = computed(() => {
        return `theme-${currentTheme.value} mode-${effectiveMode.value}`;
    });

    // Métodos
    const setTheme = (themeId) => {
        if (availableThemes.find(t => t.id === themeId)) {
            currentTheme.value = themeId;
            applyTheme();
            savePreferences();
        }
    };

    const toggleDarkMode = () => {
        isAutoMode.value = false;
        isDarkMode.value = !isDarkMode.value;
        applyTheme();
        savePreferences();
    };

    const setAutoMode = (enabled) => {
        isAutoMode.value = enabled;
        applyTheme();
        savePreferences();
    };

    const setServerTimeMode = (mode) => {
        serverTimeMode.value = mode;
        if (isAutoMode.value) {
            applyTheme();
        }
    };

    const applyTheme = () => {
        const root = document.documentElement;
        
        // Limpiar clases anteriores
        root.className = root.className
            .split(' ')
            .filter(c => !c.startsWith('theme-') && !c.startsWith('mode-'))
            .join(' ');

        // Aplicar nuevas clases
        root.classList.add(`theme-${currentTheme.value}`);
        root.classList.add(`mode-${effectiveMode.value}`);

        // Actualizar meta theme-color para móviles
        updateMetaThemeColor();
    };

    const updateMetaThemeColor = () => {
        let themeColor = '#ffffff';
        
        if (effectiveMode.value === 'dark') {
            themeColor = currentTheme.value === 'ninos' ? '#1a1a2e' :
                        currentTheme.value === 'jovenes' ? '#0f172a' :
                        '#1e1e1e';
        } else {
            themeColor = currentTheme.value === 'ninos' ? '#fef3c7' :
                        currentTheme.value === 'jovenes' ? '#f0f9ff' :
                        '#f8fafc';
        }

        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.setAttribute('name', 'theme-color');
            document.head.appendChild(metaThemeColor);
        }
        metaThemeColor.setAttribute('content', themeColor);
    };

    const savePreferences = async () => {
        // Guardar en localStorage
        localStorage.setItem('theme_preferences', JSON.stringify({
            theme: currentTheme.value,
            darkMode: isDarkMode.value,
            autoMode: isAutoMode.value
        }));

        // Guardar en base de datos si el usuario está autenticado
        try {
            await axios.post('/api/user/theme-preferences', {
                tema_preferido: currentTheme.value,
                modo_contraste: effectiveMode.value === 'dark' ? 'alto' : 'normal',
            });
        } catch (error) {
            console.log('No se pudo guardar en BD (usuario no autenticado o error):', error.message);
        }
    };

    const loadPreferences = () => {
        // Cargar desde localStorage
        const stored = localStorage.getItem('theme_preferences');
        if (stored) {
            try {
                const preferences = JSON.parse(stored);
                currentTheme.value = preferences.theme || 'jovenes';
                isDarkMode.value = preferences.darkMode || false;
                isAutoMode.value = preferences.autoMode !== undefined ? preferences.autoMode : true;
            } catch (error) {
                console.error('Error cargando preferencias:', error);
            }
        }
        applyTheme();
    };

    const initializeFromUser = (user) => {
        if (user && user.tema_preferido) {
            currentTheme.value = user.tema_preferido;
        }
        if (user && user.modo_contraste) {
            isDarkMode.value = user.modo_contraste === 'alto';
        }
        applyTheme();
    };

    return {
        // Estado
        currentTheme,
        isDarkMode,
        isAutoMode,
        serverTimeMode,
        availableThemes,
        
        // Computed
        activeTheme,
        effectiveMode,
        themeClasses,
        
        // Métodos
        setTheme,
        toggleDarkMode,
        setAutoMode,
        setServerTimeMode,
        applyTheme,
        savePreferences,
        loadPreferences,
        initializeFromUser
    };
});
