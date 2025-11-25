<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import VisitaCounter from '@/Components/VisitaCounter.vue';

const showingMobileMenu = ref(false);
const showingUserDropdown = ref(false);
const { initializeTheme, themeClasses } = useTheme();
const page = usePage();

// Obtener datos del usuario
const user = computed(() => page.props.auth?.user);
const userName = computed(() => user.value?.name || 'Usuario');

// Navegación para clientes
const navigationLinks = [
    { name: 'Inicio', route: 'dashboard', icon: '🏠' },
    { name: 'Comprar Boleto', route: 'cliente.boletos.comprar', icon: '🎫' },
    { name: 'Enviar Encomienda', route: 'cliente.encomiendas.enviar', icon: '📦' },
    { name: 'Mis Compras', route: 'cliente.historial', icon: '📋' },
];

const logout = () => {
    router.post(route('logout'));
};

onMounted(() => {
    const userTheme = page.props.auth?.user?.tema_preferido;
    const serverTimeMode = page.props.timeMode;
    initializeTheme(userTheme, serverTimeMode);
});
</script>

<template>
    <div :class="themeClasses" class="min-h-screen flex flex-col" style="background-color: var(--bg-primary);">
        <!-- Navigation Bar Moderno -->
        <nav 
            class="fixed top-0 left-0 w-full z-50 shadow-lg border-b transition-all duration-300"
            style="
                background-color: var(--bg-secondary);
                border-color: var(--border-primary);
                backdrop-filter: blur(12px);
            "
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 relative">
                    <!-- Spacer para balancear el layout -->
                    <div class="flex-1"></div>

                    <!-- Nombre image.png-->
                    <Link :href="route('dashboard')" class="absolute left-1/2 transform -translate-x-1/2 group">
                        <div class="text-center">
                            <span class="font-extrabold text-2xl sm:text-3xl tracking-wide transition-all duration-300 group-hover:scale-105 brand-font" 
                                  style="
                                      color: var(--primary-600);
                                      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                      letter-spacing: 0.05em;
                                  ">
                                TransComarapa
                            </span>
                        
                        </div>
                    </Link>

                    <!-- Right Side: Theme Switcher + User Menu -->
                    <div class="flex items-center gap-3 flex-1 justify-end">
                        <!-- Theme Switcher -->
                        <div class="hidden sm:block">
                            <ThemeSwitcher />
                        </div>

                        <!-- User Dropdown -->
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="flex items-center gap-2 px-3 py-2 rounded-lg font-medium transition-all duration-200 hover:opacity-90"
                                        style="
                                            background-color: var(--primary-600);
                                            color: white;
                                        "
                                    >
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold"
                                            style="background-color: rgba(255, 255, 255, 0.2);">
                                            {{ userName.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="hidden sm:inline">{{ userName }}</span>
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 9l-7 7-7-7"
                                            />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        👤 Mi Perfil
                                    </DropdownLink>
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                    >
                                        🚪 Cerrar Sesión
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>

                       
                    </div>
                </div>
            </div>

          
        </nav>

        <!-- Page Content -->
        <main class="flex-1" style="padding-top: 64px;">
            <!-- Page Header (Slot) -->
            <header
                v-if="$slots.header"
                class="border-b"
                style="
                    background-color: var(--bg-secondary);
                    border-color: var(--border-primary);
                "
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <slot name="header" />
                </div>
            </header>

            <!-- Main Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <slot />
            </div>
        </main>

        <!-- Footer Moderno -->
        <footer
            class="mt-auto border-t"
            style="
                background-color: var(--bg-secondary);
                border-color: var(--border-primary);
            "
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <span class="font-bold text-lg" style="color: var(--text-primary);">
                            TransComarapa
                        </span>
                    </div>
                    <div class="flex flex-col md:flex-row items-center gap-4">
                        <VisitaCounter :mostrar-ruta="true" />
                        <p class="text-sm" style="color: var(--text-secondary);">
                            © {{ new Date().getFullYear() }} TransComarapa. Todos los derechos reservados.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Animaciones suaves */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

nav {
    animation: fadeIn 0.3s ease-out;
}

/* Fuente personalizada para el logo */
.brand-font {
    font-family: 'Montserrat', 'Poppins', sans-serif;
    font-weight: 800;
}
</style>


