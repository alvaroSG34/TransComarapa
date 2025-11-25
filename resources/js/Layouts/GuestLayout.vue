<script setup>
import { onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import VisitaCounter from '@/Components/VisitaCounter.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';

const { initializeTheme } = useTheme();

onMounted(() => {
    const page = usePage();
    const serverTimeMode = page.props.timeMode;
    
    // Guest users don't have saved preferences, use default theme with server time mode
    initializeTheme(null, serverTimeMode);
});
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-background pt-6 sm:pt-0 relative overflow-hidden"
    >
        <!-- Background Pattern -->
        <div
            class="absolute inset-0 opacity-5 dark:opacity-10"
            style="background-image: radial-gradient(circle at 2px 2px, rgb(var(--primary)) 1px, transparent 0); background-size: 40px 40px;"
        ></div>

        <!-- Theme Switcher for Guest -->
        <div class="absolute top-4 right-4 z-10">
            <ThemeSwitcher />
        </div>

        <!-- Logo -->
        <div class="relative z-10 mb-6 sm:mb-8">
            <Link href="/" class="block transition-transform hover:scale-105">
                <ApplicationLogo
                    class="h-16 w-16 sm:h-20 sm:w-20 md:h-24 md:w-24 fill-current text-primary drop-shadow-lg transition-all duration-200"
                />
            </Link>
        </div>

        <!-- Login Card -->
        <div
            class="relative z-10 w-full overflow-hidden bg-card px-8 py-10 shadow-2xl sm:max-w-md sm:rounded-2xl border border-border/50 backdrop-blur-sm"
        >
            <slot />
        </div>

        <!-- Decorative Elements -->
        <div
            class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-primary to-transparent opacity-20"
        ></div>

        <!-- Footer con contador de visitas -->
        <footer class="relative z-10 mt-8 pb-4">
            <div class="flex flex-col items-center gap-2">
                <VisitaCounter :mostrar-ruta="false" :mostrar-total="true" />
                <p class="text-xs" style="color: var(--text-secondary);">
                    © {{ new Date().getFullYear() }} TransComarapa. Todos los derechos reservados.
                </p>
            </div>
        </footer>
    </div>
</template>
