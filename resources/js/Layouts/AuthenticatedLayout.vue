<script setup>
import { ref, onMounted, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import VisitaCounter from '@/Components/VisitaCounter.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';

const showingNavigationDropdown = ref(false);
const { initializeTheme, themeClasses } = useTheme();
const page = usePage();

const handleHamburgerMouseEnter = (event) => {
    event.target.style.backgroundColor = 'var(--card-bg)';
    event.target.style.color = 'var(--text-primary)';
};

const handleHamburgerMouseLeave = (event) => {
    event.target.style.backgroundColor = 'transparent';
    event.target.style.color = 'var(--text-secondary)';
};

const handleHamburgerFocus = (event) => {
    event.target.style.backgroundColor = 'var(--card-bg)';
    event.target.style.color = 'var(--text-primary)';
};

const handleHamburgerBlur = (event) => {
    event.target.style.backgroundColor = 'transparent';
    event.target.style.color = 'var(--text-secondary)';
};

// Obtener el rol del usuario actual
const userRole = computed(() => page.props.auth?.user?.rol || 'Cliente');

// Menú de navegación según rol
const navigationLinks = computed(() => {
    const role = userRole.value;
    
    if (role === 'Admin' || role === 'Secretaria') {
        return [
            { name: 'Dashboard', route: 'dashboard' },
            { name: 'Rutas', route: 'rutas.index' },
            { name: 'Viajes', route: 'viajes.index' },
            { name: 'Boletos', route: 'boletos.index' },
            { name: 'Encomiendas', route: 'encomiendas.index' },
            { name: 'Ventas', route: 'ventas.index' },
            { name: 'Clientes', route: 'clientes.index' },
            ...(role === 'Admin' ? [
                { name: 'Vehículos', route: 'vehiculos.index' },
                { name: 'Conductores', route: 'conductores.index' },
                { name: 'Secretarias', route: 'secretarias.index' },
                { name: 'Estadísticas', route: 'estadisticas.index' }
            ] : [])
        ];
    }
    
    // Cliente (navegación diferente, se manejará en otro layout)
    return [
        { name: 'Inicio', route: 'dashboard' }
    ];
});

onMounted(() => {
    const userTheme = page.props.auth?.user?.tema_preferido;
    const serverTimeMode = page.props.timeMode;
    
    initializeTheme(userTheme, serverTimeMode);
});
</script>

<template>
    <div :class="themeClasses" style="background-color: var(--bg-primary); min-height: 100vh;">
        <div class="min-h-screen transition-all duration-300" style="background-color: var(--bg-primary);">
            <nav
                class="border-b transition-all duration-300"
                style="border-color: var(--border-color); background-color: var(--bg-secondary);"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex items-center">
                            <!-- Logo -->
                            <div class="hidden sm:flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="block">
                                    <ApplicationLogo
                                        class="block h-5 w-5 md:h-6 md:w-6 fill-current transition-all duration-200 hover:opacity-80"
                                        style="color: var(--text-primary);"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    v-for="link in navigationLinks"
                                    :key="link.route"
                                    :href="route(link.route)"
                                    :active="route().current(link.route)"
                                >
                                    {{ link.name }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center sm:space-x-3">
                            <!-- Theme Switcher -->
                            <ThemeSwitcher />
                            
                            <!-- Settings Dropdown -->
                            <div class="relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition-all duration-150 ease-in-out focus:outline-none hover:opacity-80"
                                                style="background-color: var(--bg-secondary); color: var(--text-secondary);"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 transition-all duration-150 ease-in-out focus:outline-none hover:opacity-80"
                                style="color: var(--text-secondary); background-color: transparent;"
                                @mouseenter="handleHamburgerMouseEnter"
                                @mouseleave="handleHamburgerMouseLeave"
                                @focus="handleHamburgerFocus"
                                @blur="handleHamburgerBlur"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t pb-1 pt-4 transition-all duration-300"
                        style="border-color: var(--border-color);"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium"
                                style="color: var(--text-primary);"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium" style="color: var(--text-secondary);">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <!-- Theme Switcher (Mobile) -->
                            <div class="px-4 py-2">
                                <ThemeSwitcher />
                            </div>
                            
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="shadow transition-all duration-300"
                style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-color);"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <!-- Footer -->
            <footer
                class="mt-auto border-t transition-all duration-300"
                style="background-color: var(--bg-secondary); border-color: var(--border-color);"
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
    </div>
</template>
