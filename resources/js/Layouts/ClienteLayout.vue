<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

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
                                TransPorta
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
            <!-- Línea decorativa superior -->
            <div class="h-1 bg-gradient-to-r from-transparent via-primary to-transparent opacity-20"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Columna 1: Sobre Nosotros -->
                    <div>
                                            
                        <!-- Redes Sociales -->
                        <div>
                            <h4 class="font-semibold text-sm mb-2" style="color: var(--text-primary);">
                                Síguenos
                            </h4>
                            <div class="flex gap-3">
                                <a href="https://facebook.com" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center transition-all hover:opacity-70" style="background-color: var(--primary-600); color: white;" title="Facebook">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                                <a href="https://instagram.com" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center transition-all hover:opacity-70" style="background-color: #E4405F; color: white;" title="Instagram">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                                <a href="https://twitter.com" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center transition-all hover:opacity-70" style="background-color: #1DA1F2; color: white;" title="Twitter">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                                <a href="https://tiktok.com" target="_blank" class="w-9 h-9 rounded-full flex items-center justify-center transition-all hover:opacity-70" style="background-color: #000000; color: white;" title="TikTok">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Información de Contacto -->
                    <div>
                        <h3 class="font-semibold text-sm uppercase tracking-wider mb-3" style="color: var(--text-primary);">
                            Información de Contacto
                        </h3>
                        <div class="grid grid-cols-2 gap-4 text-sm" style="color: var(--text-secondary);">
                            <div class="flex items-center gap-2">
                                <span class="text-base">📞</span>
                                <div>
                                    <div class="font-medium" style="color: var(--text-primary);">Teléfono</div>
                                    <div>+591 3-123-4567</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-base">✉️</span>
                                <div>
                                    <div class="font-medium" style="color: var(--text-primary);">Email</div>
                                    <div>info@transcomarapa.bo</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-base">🕒</span>
                                <div>
                                    <div class="font-medium" style="color: var(--text-primary);">Horario</div>
                                    <div>Lun-Dom 6AM-10PM</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-base">📍</span>
                                <div>
                                    <div class="font-medium" style="color: var(--text-primary);">Ubicación</div>
                                    <div>Terminal de Buses</div>
                                </div>
                            </div>
                        </div>
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


