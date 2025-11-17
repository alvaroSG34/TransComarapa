<script setup>
import { onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
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
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <!-- Theme Switcher for Guest -->
        <div class="absolute top-4 right-4">
            <ThemeSwitcher />
        </div>
        
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
