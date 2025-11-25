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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-3xl font-bold mb-4">
                            ¡Bienvenido, {{ userName }}! 👋
                        </h1>
                        <p class="text-lg text-gray-600 mb-6">
                            Panel de administración de TransComarapa
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                            <Link 
                                :href="route('rutas.index')" 
                                class="bg-blue-500 hover:bg-blue-600 text-white p-6 rounded-lg transition-colors"
                            >
                                <div class="text-2xl mb-2">🛣️</div>
                                <div class="font-bold text-lg">Rutas</div>
                                <div class="text-sm opacity-90">Gestionar rutas</div>
                            </Link>
                            
                            <Link 
                                :href="route('viajes.index')" 
                                class="bg-green-500 hover:bg-green-600 text-white p-6 rounded-lg transition-colors"
                            >
                                <div class="text-2xl mb-2">🚌</div>
                                <div class="font-bold text-lg">Viajes</div>
                                <div class="text-sm opacity-90">Gestionar viajes</div>
                            </Link>
                            
                            <Link 
                                :href="route('ventas.index')" 
                                class="bg-purple-500 hover:bg-purple-600 text-white p-6 rounded-lg transition-colors"
                            >
                                <div class="text-2xl mb-2">💰</div>
                                <div class="font-bold text-lg">Ventas</div>
                                <div class="text-sm opacity-90">Ver ventas</div>
                            </Link>
                            
                            <Link 
                                :href="route('clientes.index')" 
                                class="bg-orange-500 hover:bg-orange-600 text-white p-6 rounded-lg transition-colors"
                            >
                                <div class="text-2xl mb-2">👥</div>
                                <div class="font-bold text-lg">Clientes</div>
                                <div class="text-sm opacity-90">Gestionar clientes</div>
                            </Link>
                            
                            <Link 
                                v-if="userRole === 'Admin'"
                                :href="route('vehiculos.index')" 
                                class="bg-red-500 hover:bg-red-600 text-white p-6 rounded-lg transition-colors"
                            >
                                <div class="text-2xl mb-2">🚗</div>
                                <div class="font-bold text-lg">Vehículos</div>
                                <div class="text-sm opacity-90">Gestionar vehículos</div>
                            </Link>
                            
                            <Link 
                                v-if="userRole === 'Admin'"
                                :href="route('estadisticas.index')" 
                                class="bg-indigo-500 hover:bg-indigo-600 text-white p-6 rounded-lg transition-colors"
                            >
                                <div class="text-2xl mb-2">📊</div>
                                <div class="font-bold text-lg">Estadísticas</div>
                                <div class="text-sm opacity-90">Ver estadísticas</div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
