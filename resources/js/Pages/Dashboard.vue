<script setup>
import ClienteLayout from '@/Layouts/ClienteLayout.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const userName = computed(() => page.props.auth.user?.name || 'Usuario');
const userRole = computed(() => page.props.auth.user?.rol || 'Cliente');
const isCliente = computed(() => userRole.value === 'Cliente');
</script>

<template>
    <Head title="Dashboard" />

    <!-- Layout para Clientes -->
    <ClienteLayout v-if="isCliente">
        <template #header>
            <div>
                <h1 class="text-3xl font-bold mb-2" style="color: var(--text-primary)">
                    ¡Bienvenido, {{ userName }}! 👋
                </h1>
                <p class="text-lg" style="color: var(--text-secondary)">
                    Gestiona tus pasajes de forma fácil y rápida
                </p>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Quick Actions -->
            <div>
                <h2 class="text-2xl font-bold mb-6" style="color: var(--text-primary);">
                    Acciones Rápidas
                </h2>
                
                <!-- Cliente -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Link 
                                :href="route('cliente.boletos.comprar')" 
                                class="group relative overflow-hidden rounded-2xl p-6 text-left transition-all duration-300 hover:scale-105 hover:shadow-2xl"
                                style="background: linear-gradient(135deg, var(--primary-600), var(--primary-500)); color: white;"
                            >
                                <div class="relative z-10">
                                    <div class="text-4xl mb-3 transform group-hover:scale-110 transition-transform duration-300">🎫</div>
                                    <div class="font-bold text-xl mb-2">Comprar Boleto</div>
                                    <div class="text-sm opacity-90">Adquiere tu pasaje de forma rápida y segura</div>
                                </div>
                                <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-10" style="background-color: white; transform: translate(30%, -30%);"></div>
                            </Link>
                            
                            <Link 
                                :href="route('cliente.historial')" 
                                class="group relative overflow-hidden rounded-2xl p-6 text-left transition-all duration-300 hover:scale-105 hover:shadow-2xl"
                                style="background: linear-gradient(135deg, var(--accent-600), var(--accent-500)); color: white;"
                            >
                                <div class="relative z-10">
                                    <div class="text-4xl mb-3 transform group-hover:scale-110 transition-transform duration-300">📋</div>
                                    <div class="font-bold text-xl mb-2">Mis Compras</div>
                                    <div class="text-sm opacity-90">Consulta tu historial de compras y envíos</div>
                                </div>
                                <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-10" style="background-color: white; transform: translate(30%, -30%);"></div>
                            </Link>
                        </div>
                    </div>
                </div>
    </ClienteLayout>

    <!-- Layout para Admin y Secretaria -->
    <AuthenticatedLayout v-else>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight" style="color: var(--text-primary)">
                Dashboard
            </h2>
        </template>

        <div class="py-8" style="background-color: var(--bg-primary); min-height: calc(100vh - 4rem);">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                <!-- Banner de Bienvenida con Tema -->
                <div class="relative overflow-hidden rounded-2xl p-8 transition-all duration-300"
                     style="background: linear-gradient(135deg, var(--primary-600), var(--accent-600));">
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-0 right-0 w-64 h-64 rounded-full blur-3xl" 
                             style="background-color: var(--accent-500); transform: translate(30%, -30%);"></div>
                        <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full blur-3xl" 
                             style="background-color: var(--primary-500); transform: translate(-30%, 30%);"></div>
                    </div>
                    <div class="relative z-10">
                        <h1 class="text-4xl font-bold mb-2 text-white">
                            ¡Bienvenido, {{ userName }}! 👋
                        </h1>
                        <p class="text-lg text-white opacity-90">
                            Panel de administración de TransPorta
                        </p>
                        <div class="mt-4 flex items-center gap-2">
                            <div class="px-4 py-2 rounded-full text-sm font-medium text-white"
                                 style="background-color: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px);">
                                {{ userRole }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjetas de Acción Rápida -->
                <div>
                    <h2 class="text-2xl font-bold mb-6" style="color: var(--text-primary)">
                        Acciones Rápidas
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link 
                            :href="route('rutas.index')" 
                            class="group relative overflow-hidden rounded-xl p-6 text-white transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-white hover:border-opacity-30"
                            style="background: linear-gradient(135deg, var(--primary-600), var(--primary-500)); border: 2px solid transparent;"
                        >
                            <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-20" 
                                 style="background-color: white; transform: translate(30%, -30%);"></div>
                            <div class="relative z-10">
                                <div class="text-4xl mb-3 transform group-hover:scale-110 transition-transform duration-300">🛣️</div>
                                <div class="font-bold text-xl mb-2">Rutas</div>
                                <div class="text-sm opacity-90">Gestionar rutas del sistema</div>
                            </div>
                        </Link>
                        
                        <Link 
                            :href="route('viajes.index')" 
                            class="group relative overflow-hidden rounded-xl p-6 text-white transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-white hover:border-opacity-30"
                            style="background: linear-gradient(135deg, var(--accent-600), var(--accent-500)); border: 2px solid transparent;"
                        >
                            <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-20" 
                                 style="background-color: white; transform: translate(30%, -30%);"></div>
                            <div class="relative z-10">
                                <div class="text-4xl mb-3 transform group-hover:scale-110 transition-transform duration-300">🚌</div>
                                <div class="font-bold text-xl mb-2">Viajes</div>
                                <div class="text-sm opacity-90">Gestionar viajes programados</div>
                            </div>
                        </Link>
                        
                        <Link 
                            :href="route('ventas.index')" 
                            class="group relative overflow-hidden rounded-xl p-6 text-white transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-white hover:border-opacity-30"
                            style="background: linear-gradient(135deg, var(--secondary-600), var(--secondary-500)); border: 2px solid transparent;"
                        >
                            <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-20" 
                                 style="background-color: white; transform: translate(30%, -30%);"></div>
                            <div class="relative z-10">
                                <div class="text-4xl mb-3 transform group-hover:scale-110 transition-transform duration-300">💰</div>
                                <div class="font-bold text-xl mb-2">Ventas</div>
                                <div class="text-sm opacity-90">Ver historial de ventas</div>
                            </div>
                        </Link>
                        
                        <Link 
                            :href="route('clientes.index')" 
                            class="group relative overflow-hidden rounded-xl p-6 text-white transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-white hover:border-opacity-30"
                            style="background: linear-gradient(135deg, var(--primary-500), var(--primary-600)); border: 2px solid transparent;"
                        >
                            <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-20" 
                                 style="background-color: white; transform: translate(30%, -30%);"></div>
                            <div class="relative z-10">
                                <div class="text-4xl mb-3 transform group-hover:scale-110 transition-transform duration-300">👥</div>
                                <div class="font-bold text-xl mb-2">Clientes</div>
                                <div class="text-sm opacity-90">Gestionar clientes</div>
                            </div>
                        </Link>
                        
                        <Link 
                            v-if="userRole === 'Admin'"
                            :href="route('vehiculos.index')" 
                            class="group relative overflow-hidden rounded-xl p-6 text-white transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-white hover:border-opacity-30"
                            style="background: linear-gradient(135deg, var(--accent-500), var(--accent-600)); border: 2px solid transparent;"
                        >
                            <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-20" 
                                 style="background-color: white; transform: translate(30%, -30%);"></div>
                            <div class="relative z-10">
                                <div class="text-4xl mb-3 transform group-hover:scale-110 transition-transform duration-300">🚗</div>
                                <div class="font-bold text-xl mb-2">Vehículos</div>
                                <div class="text-sm opacity-90">Gestionar vehículos</div>
                            </div>
                        </Link>
                        
                        <Link 
                            v-if="userRole === 'Admin'"
                            :href="route('estadisticas.index')" 
                            class="group relative overflow-hidden rounded-xl p-6 transition-all duration-300 hover:scale-105 hover:shadow-2xl hover:border-white hover:border-opacity-30"
                            style="background: linear-gradient(135deg, var(--primary-500), var(--accent-500)); color: white; border: 2px solid transparent;"
                        >
                            <div class="absolute top-0 right-0 w-32 h-32 rounded-full opacity-20" 
                                 style="background-color: white; transform: translate(30%, -30%);"></div>
                            <div class="relative z-10">
                                <div class="text-4xl mb-3 transform group-hover:scale-110 transition-transform duration-300">📊</div>
                                <div class="font-bold text-xl mb-2">Estadísticas</div>
                                <div class="text-sm opacity-90">Ver estadísticas del sistema</div>
                            </div>
                        </Link>
                    </div>
                </div>

                
            </div>
        </div>
    </AuthenticatedLayout>
</template>
