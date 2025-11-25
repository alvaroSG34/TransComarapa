<script setup>
import ClienteLayout from '@/Layouts/ClienteLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import busImage from '../../../images/bus.jpg';

const props = defineProps({
    rutas: Array
});

// Función para obtener la imagen de la ruta (placeholder por ahora)
const obtenerImagenRuta = (ruta) => {
    // Por ahora usamos una imagen placeholder
    // Cuando tengas las imágenes, puedes mapearlas por nombre, origen, destino, etc.
    // Ejemplo: return `/images/rutas/${ruta.nombre.toLowerCase().replace(/\s+/g, '-')}.jpg`;
    return busImage; // Imagen placeholder
};
</script>

<template>
    <Head title="Comprar Boleto" />

    <ClienteLayout>
        <template #header>
            <div>
                <h1 class="text-3xl font-bold mb-2" style="color: var(--text-primary)">
                    Selecciona tu Ruta 🚌
                </h1>
                <p class="text-lg" style="color: var(--text-secondary)">
                    Elige el destino de tu viaje y encuentra los horarios disponibles
                </p>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Grid de Rutas -->
            <div v-if="rutas && rutas.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link
                    v-for="ruta in rutas"
                    :key="ruta.id"
                    :href="route('cliente.boletos.viajes', ruta.id)"
                    class="group relative overflow-hidden rounded-2xl shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl"
                >
                    <!-- Imagen de la Ruta -->
                    <div class="relative h-48 overflow-hidden">
                        <img
                            :src="obtenerImagenRuta(ruta)"
                            :alt="ruta.nombre"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                            @error="$event.target.src=busImage"
                        />
                        <!-- Overlay con gradiente -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    </div>

                    <!-- Contenido de la Card -->
                    <div class="p-6" style="background-color: var(--card-bg);">
                        <h3 class="text-xl font-bold mb-2" style="color: var(--text-primary)">
                            {{ ruta.nombre }}
                        </h3>
                        <div class="flex items-center gap-2 text-sm mb-4" style="color: var(--text-secondary)">
                            <span class="font-semibold" style="color: var(--primary-600)">{{ ruta.origen }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                            <span class="font-semibold" style="color: var(--primary-600)">{{ ruta.destino }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium" style="color: var(--text-secondary)">
                                Ver horarios disponibles
                            </span>
                            <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" 
                                 style="color: var(--primary-600)" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Mensaje si no hay rutas -->
            <div v-else class="text-center py-12">
                <div class="text-6xl mb-4">🚌</div>
                <h3 class="text-2xl font-bold mb-2" style="color: var(--text-primary)">
                    No hay rutas disponibles
                </h3>
                <p class="text-lg" style="color: var(--text-secondary)">
                    Por el momento no contamos con rutas disponibles. Vuelve pronto.
                </p>
            </div>
        </div>
    </ClienteLayout>
</template>

<style scoped>
/* Animaciones adicionales si es necesario */
</style>

