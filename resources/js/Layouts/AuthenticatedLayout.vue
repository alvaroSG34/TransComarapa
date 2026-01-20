<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import ThemeSwitcher from '@/Components/ThemeSwitcher.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
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
            
            ...(role === 'Admin' ? [
                { name: 'Vehículos', route: 'vehiculos.index' },
            ] : [])
        ];
    }
    
    // Cliente (navegación diferente, se manejará en otro layout)
    return [
        { name: 'Inicio', route: 'dashboard' }
    ];
});

// Verificar si el usuario es Admin
const isAdmin = computed(() => userRole.value === 'Admin');

// Buscador de menús
const mostrarBuscador = ref(false);
const busqueda = ref('');
const menuItems = computed(() => {
    const role = userRole.value;
    const items = [];
    
    if (role === 'Admin' || role === 'Secretaria') {
        items.push(
            { name: 'Dashboard', route: 'dashboard', icon: '📊' },
            { name: 'Rutas', route: 'rutas.index', icon: '🛣️' },
            { name: 'Viajes', route: 'viajes.index', icon: '🚌' },
            { name: 'Boletos', route: 'boletos.index', icon: '🎫' },
            { name: 'Encomiendas', route: 'encomiendas.index', icon: '📦' }
        );
        
        if (role === 'Admin') {
            items.push(
                { name: 'Vehículos', route: 'vehiculos.index', icon: '🚗' },
                { name: 'Conductores', route: 'conductores.index', icon: '👨‍✈️' },
                { name: 'Secretarias', route: 'secretarias.index', icon: '👩‍💼' },
                { name: 'Clientes', route: 'clientes.index', icon: '👥' },
                { name: 'Ingresos', route: 'estadisticas.index', icon: '💰' },
                { name: 'Métricas', route: 'metricas.index', icon: '📈' }
            );
        }
    }
    
    return items;
});

const resultadosBusqueda = computed(() => {
    if (!busqueda.value || busqueda.value.length < 2) {
        return [];
    }
    
    const query = busqueda.value.toLowerCase().trim();
    return menuItems.value.filter(item => 
        item.name.toLowerCase().includes(query)
    ).slice(0, 8); // Limitar a 8 resultados
});

const navegarARuta = (routeName) => {
    router.visit(route(routeName));
    busqueda.value = '';
    mostrarBuscador.value = false;
};

// Cerrar buscador al hacer clic fuera
const cerrarBuscador = () => {
    mostrarBuscador.value = false;
    busqueda.value = '';
};

// Atajo de teclado Ctrl+K para abrir buscador
const handleKeyDown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        if (userRole.value === 'Admin' || userRole.value === 'Secretaria') {
            mostrarBuscador.value = !mostrarBuscador.value;
            if (mostrarBuscador.value) {
                // Focus en el input después de que se renderice
                setTimeout(() => {
                    const input = document.querySelector('input[placeholder*="Buscar menú"]');
                    if (input) input.focus();
                }, 50);
            }
        }
    }
};

onMounted(() => {
    const userTheme = page.props.auth?.user?.tema_preferido;
    const serverTimeMode = page.props.timeMode;
    
    initializeTheme(userTheme, serverTimeMode);
    
    // Agregar atajo de teclado Ctrl+K
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
});

</script>

<template>
    <div :class="themeClasses" class="min-h-screen flex flex-col transition-all duration-300" style="background-color: var(--bg-primary);">
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
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center"
                            >
                                <NavLink
                                    v-for="link in navigationLinks"
                                    :key="link.route"
                                    :href="route(link.route)"
                                    :active="route().current(link.route)"
                                >
                                    {{ link.name }}
                                </NavLink>
                                
                                <!-- Dropdown Usuarios (solo Admin) -->
                                <div v-if="isAdmin" class="relative">
                                    <Dropdown align="left" width="48">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 transition-all duration-150 ease-in-out focus:outline-none hover:opacity-80"
                                                :class="{
                                                    'border-b-2': route().current('conductores.index') || route().current('secretarias.index') || route().current('clientes.index'),
                                                }"
                                                style="color: var(--text-primary); border-color: var(--accent-color);"
                                            >
                                                Usuarios
                                                <svg
                                                    class="-me-0.5 ms-1 h-4 w-4"
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
                                        </template>

                                        <template #content>
                                            <DropdownLink
                                                :href="route('conductores.index')"
                                            >
                                                Conductores
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('secretarias.index')"
                                            >
                                                Secretarias
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('clientes.index')"
                                            >
                                                Clientes
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>

                                <!-- Dropdown Estadísticas (solo Admin) -->
                                <div v-if="isAdmin" class="relative">
                                    <Dropdown align="left" width="48">
                                        <template #trigger>
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 transition-all duration-150 ease-in-out focus:outline-none hover:opacity-80"
                                                :class="{
                                                    'border-b-2': route().current('estadisticas.index') || route().current('metricas.index'),
                                                }"
                                                style="color: var(--text-primary); border-color: var(--accent-color);"
                                            >
                                                Estadísticas
                                                <svg
                                                    class="-me-0.5 ms-1 h-4 w-4"
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
                                        </template>

                                        <template #content>
                                            <DropdownLink
                                                :href="route('estadisticas.index')"
                                            >
                                                💰 Ingresos
                                            </DropdownLink>
                                            <DropdownLink
                                                :href="route('metricas.index')"
                                            >
                                                📈 Métricas
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center sm:space-x-3">
                            <!-- Buscador de Menús (solo Admin y Secretaria) -->
                            <div v-if="userRole === 'Admin' || userRole === 'Secretaria'" class="relative mr-3">
                                <button
                                    @click="mostrarBuscador = !mostrarBuscador"
                                    type="button"
                                    class="inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 transition-all duration-150 ease-in-out focus:outline-none hover:opacity-80"
                                    style="background-color: var(--bg-secondary); color: var(--text-secondary);"
                                    title="Buscar menú (Ctrl+K)"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                                
                                <!-- Overlay para cerrar al hacer clic fuera -->
                                <div
                                    v-if="mostrarBuscador"
                                    class="fixed inset-0 z-40"
                                    @click="cerrarBuscador"
                                ></div>
                                
                                <!-- Dropdown del Buscador -->
                                <div
                                    v-if="mostrarBuscador"
                                    class="absolute right-0 mt-2 w-80 rounded-md shadow-lg z-50"
                                    style="background-color: var(--card-bg); border: 1px solid var(--border-color);"
                                >
                                    <div class="p-3">
                                        <input
                                            v-model="busqueda"
                                            type="text"
                                            placeholder="Buscar menú... (ej: Cond, Bolet, Enco)"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm"
                                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);"
                                            autofocus
                                            @keydown.esc="cerrarBuscador"
                                            @keydown.enter="resultadosBusqueda.length > 0 && navegarARuta(resultadosBusqueda[0].route)"
                                        />
                                        
                                        <!-- Resultados -->
                                        <div v-if="resultadosBusqueda.length > 0" class="mt-2 max-h-64 overflow-y-auto">
                                            <button
                                                v-for="item in resultadosBusqueda"
                                                :key="item.route"
                                                @click="navegarARuta(item.route)"
                                                class="w-full text-left px-3 py-2 text-sm rounded-md hover:opacity-80 transition-all duration-150 flex items-center gap-2"
                                                style="color: var(--text-primary); background-color: var(--header-bg);"
                                                :class="{ 'bg-opacity-50': route().current(item.route) }"
                                            >
                                                <span class="text-lg">{{ item.icon }}</span>
                                                <span>{{ item.name }}</span>
                                            </button>
                                        </div>
                                        
                                        <div v-else-if="busqueda.length >= 2" class="mt-2 text-sm text-center py-2" style="color: var(--text-secondary);">
                                            No se encontraron resultados
                                        </div>
                                        
                                        <div v-else-if="busqueda.length > 0 && busqueda.length < 2" class="mt-2 text-xs text-center py-2" style="color: var(--text-secondary);">
                                            Escribe al menos 2 caracteres
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
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
                            v-for="link in navigationLinks"
                            :key="link.route"
                            :href="route(link.route)"
                            :active="route().current(link.route)"
                        >
                            {{ link.name }}
                        </ResponsiveNavLink>
                        
                        <!-- Menú Usuarios (Mobile - solo Admin) -->
                        <div v-if="isAdmin" class="px-4 py-2">
                            <div class="text-sm font-medium mb-2" style="color: var(--text-secondary);">
                                Usuarios
                            </div>
                            <div class="ml-4 space-y-1">
                                <ResponsiveNavLink
                                    :href="route('conductores.index')"
                                    :active="route().current('conductores.index')"
                                >
                                    Conductores
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    :href="route('secretarias.index')"
                                    :active="route().current('secretarias.index')"
                                >
                                    Secretarias
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    :href="route('clientes.index')"
                                    :active="route().current('clientes.index')"
                                >
                                    Clientes
                                </ResponsiveNavLink>
                            </div>
                        </div>

                        <!-- Menú Estadísticas (Mobile - solo Admin) -->
                        <div v-if="isAdmin" class="px-4 py-2">
                            <div class="text-sm font-medium mb-2" style="color: var(--text-secondary);">
                                Estadísticas
                            </div>
                            <div class="ml-4 space-y-1">
                                <ResponsiveNavLink
                                    :href="route('estadisticas.index')"
                                    :active="route().current('estadisticas.index')"
                                >
                                    💰 Ingresos
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    :href="route('metricas.index')"
                                    :active="route().current('metricas.index')"
                                >
                                    📈 Métricas
                                </ResponsiveNavLink>
                            </div>
                        </div>
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
            <main class="flex-1">
                <slot />
            </main>

            <!-- Footer para Clientes -->
            <footer
                v-if="userRole === 'Cliente'"
                class="mt-auto border-t transition-all duration-300"
                style="background-color: var(--bg-secondary); border-color: var(--border-color);"
            >
                <!-- Línea decorativa superior -->
                <div class="h-1 bg-gradient-to-r from-transparent via-primary to-transparent opacity-20"></div>
                
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Columna 1: Sobre Nosotros -->
                        <div>
                            <h3 class="font-bold text-lg mb-3" style="color: var(--text-primary);">
                                🚌 Sobre Nosotros
                            </h3>
                            <p class="text-sm mb-4" style="color: var(--text-secondary);">
                                TransComarapa es tu mejor opción para viajar. Ofrecemos servicios de transporte interprovincial con la más alta calidad, seguridad y comodidad. Viaja con confianza.
                            </p>
                            <div class="flex items-center gap-2 mb-4 text-xs" style="color: var(--text-secondary);">
                                <span class="inline-flex items-center px-2 py-1 rounded-full" style="background-color: var(--primary-100); color: var(--primary-600);">
                                    ✓ Transporte Seguro
                                </span>
                            </div>
                            
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

            <!-- Footer Simple para Admin/Secretaria/Conductor -->
            <footer
                v-else
                class="mt-auto border-t transition-all duration-300"
                style="background-color: var(--bg-secondary); border-color: var(--border-color);"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <span class="font-bold text-lg" style="color: var(--text-primary);">
                                TransComarapa
                            </span>
                            <span class="text-xs ml-2 px-2 py-1 rounded-full" style="background-color: var(--primary-100); color: var(--primary-600);">
                                Panel de Administración
                            </span>
                        </div>
                        <p class="text-sm" style="color: var(--text-secondary);">
                            © {{ new Date().getFullYear() }} TransComarapa. Todos los derechos reservados.
                        </p>
                    </div>
                </div>
            </footer>
    </div>
</template>
