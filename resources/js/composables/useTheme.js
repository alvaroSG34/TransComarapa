import { computed } from 'vue';
import { useThemeStore } from '@/stores/theme';

export function useTheme() {
    const themeStore = useThemeStore();

    const currentTheme = computed(() => themeStore.currentTheme);
    const activeTheme = computed(() => themeStore.activeTheme);
    const isDarkMode = computed(() => themeStore.isDarkMode);
    const isAutoMode = computed(() => themeStore.isAutoMode);
    const effectiveMode = computed(() => themeStore.effectiveMode);
    const themeClasses = computed(() => themeStore.themeClasses);
    const availableThemes = computed(() => themeStore.availableThemes);

    const setTheme = (themeId) => {
        themeStore.setTheme(themeId);
    };

    const toggleDarkMode = () => {
        themeStore.toggleDarkMode();
    };

    const setAutoMode = (enabled) => {
        themeStore.setAutoMode(enabled);
    };

    const isThemeActive = (themeId) => {
        return themeStore.currentTheme === themeId;
    };

    const initializeTheme = (userTheme, serverTimeMode) => {
        // Set server time mode first
        if (serverTimeMode) {
            themeStore.setServerTimeMode(serverTimeMode);
        }

        // Load from localStorage
        themeStore.loadPreferences();

        // Override with user preferences from database if available
        if (userTheme) {
            themeStore.currentTheme = userTheme;
        }

        // Apply the theme
        themeStore.applyTheme();
    };

    return {
        // Estado
        currentTheme,
        activeTheme,
        isDarkMode,
        isAutoMode,
        effectiveMode,
        themeClasses,
        availableThemes,

        // Métodos
        setTheme,
        toggleDarkMode,
        setAutoMode,
        isThemeActive,
        initializeTheme
    };
}
