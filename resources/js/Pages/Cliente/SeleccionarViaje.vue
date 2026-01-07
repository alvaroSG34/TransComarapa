<script setup>
import ClienteLayout from '@/Layouts/ClienteLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    ruta: Object,
    viajes: Array
});

// Obtener símbolo de moneda según código ISO
const getSimboloMoneda = (moneda) => {
    const simbolos = {
        'BOB': 'Bs', 'USD': '$', 'EUR': '€', 'ARS': '$', 'AUD': '$', 'BRL': 'R$',
        'CAD': '$', 'CLP': '$', 'CNY': '¥', 'COP': '$', 'CRC': '₡', 'DKK': 'kr',
        'GBP': '£', 'GTQ': 'Q', 'HNL': 'L', 'INR': '₹', 'JPY': '¥', 'KRW': '₩',
        'MXN': '$', 'NIO': 'C$', 'NOK': 'kr', 'PEN': 'S/', 'PYG': '₲', 'RON': 'lei',
        'RUB': '₽', 'SEK': 'kr', 'CHF': 'Fr', 'UYU': '$', 'DOP': '$',
    };
    return simbolos[moneda || 'BOB'] || '$';
};

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleString('es-BO', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatearFechaCorta = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-BO', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="`Viajes - ${ruta?.nombre || 'Ruta'}`" />

    <ClienteLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link 
                        :href="route('cliente.boletos.comprar')" 
                        class="inline-flex items-center text-sm mb-4 hover:underline"
                        style="color: var(--text-secondary)"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Volver a rutas
                    </Link>
                    <h1 class="text-3xl font-bold mb-2" style="color: var(--text-primary)">
                        {{ ruta?.nombre }}
                    </h1>
                    <p class="text-lg" style="color: var(--text-secondary)">
                        {{ ruta?.origen }} → {{ ruta?.destino }}
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Información de la Ruta -->
            <div class="p-6 rounded-2xl shadow-lg" style="background-color: var(--card-bg); border: 1px solid var(--border-color)">
                <div class="flex items-center gap-4">
                    <div class="text-5xl">🚌</div>
                    <div>
                        <h2 class="text-2xl font-bold mb-1" style="color: var(--text-primary)">
                            {{ ruta?.nombre }}
                        </h2>
                        <div class="flex items-center gap-3 text-sm" style="color: var(--text-secondary)">
                            <span class="font-semibold">{{ ruta?.origen }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                            <span class="font-semibold">{{ ruta?.destino }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Viajes Disponibles -->
            <div v-if="viajes && viajes.length > 0">
                <h2 class="text-2xl font-bold mb-4" style="color: var(--text-primary)">
                    Viajes Disponibles ({{ viajes.length }})
                </h2>
                
                <div class="grid grid-cols-1 gap-4">
                    <div
                        v-for="viaje in viajes"
                        :key="viaje.id"
                        class="p-6 rounded-xl shadow-md transition-all duration-300 hover:shadow-xl"
                        style="background-color: var(--card-bg); border: 2px solid var(--border-color);"
                    >
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <!-- Información del Viaje -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="px-3 py-1 rounded-full text-xs font-semibold"
                                         style="background-color: var(--primary-100); color: var(--primary-700)">
                                        {{ viaje.asientos_disponibles }} asientos disponibles
                                    </div>
                                    <div class="px-3 py-1 rounded-full text-xs font-semibold"
                                         style="background-color: var(--accent-100); color: var(--accent-700)">
                                        {{ getSimboloMoneda(viaje.moneda) }} {{ viaje.precio }}
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5" style="color: var(--primary-600)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="font-semibold" style="color: var(--text-primary)">
                                            Salida: {{ formatearFecha(viaje.fecha_salida) }}
                                        </span>
                                    </div>
                                    
                                    <div v-if="viaje.fecha_llegada" class="flex items-center gap-2">
                                        <svg class="w-5 h-5" style="color: var(--accent-600)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-sm" style="color: var(--text-secondary)">
                                            Llegada estimada: {{ formatearFecha(viaje.fecha_llegada) }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5" style="color: var(--text-secondary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                        <span class="text-sm" style="color: var(--text-secondary)">
                                            {{ viaje.marca }} {{ viaje.modelo }} - {{ viaje.placa }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón de Acción -->
                            <div class="flex-shrink-0">
                                <Link
                                    :href="route('cliente.boletos.form', { viajeId: viaje.id, rutaId: ruta.id })"
                                    class="inline-flex items-center px-6 py-3 rounded-lg font-semibold text-white transition-all duration-200 hover:scale-105"
                                    style="background: linear-gradient(135deg, var(--primary-600), var(--primary-500));"
                                >
                                    Seleccionar
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensaje si no hay viajes -->
            <div v-else class="text-center py-12 rounded-2xl" style="background-color: var(--card-bg); border: 1px solid var(--border-color)">
                <div class="text-6xl mb-4">📅</div>
                <h3 class="text-2xl font-bold mb-2" style="color: var(--text-primary)">
                    No hay viajes disponibles
                </h3>
                <p class="text-lg mb-6" style="color: var(--text-secondary)">
                    Por el momento no hay viajes programados para esta ruta con fecha futura y asientos disponibles.
                </p>
                <Link
                    :href="route('cliente.boletos.comprar')"
                    class="inline-flex items-center px-6 py-3 rounded-lg font-semibold text-white transition-all duration-200 hover:scale-105"
                    style="background: linear-gradient(135deg, var(--primary-600), var(--primary-500));"
                >
                    Ver otras rutas
                </Link>
            </div>
        </div>
    </ClienteLayout>
</template>

<style scoped>
/* Estilos adicionales si es necesario */
</style>

