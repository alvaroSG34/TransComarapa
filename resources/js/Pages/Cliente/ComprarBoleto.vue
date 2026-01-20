<script setup>
import ClienteLayout from '@/Layouts/ClienteLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import busImage from '../../../images/bus.jpg';

const props = defineProps({
    rutas: Array,
    paisesDisponibles: Array,
    paisSeleccionado: String,
    paisUsuario: String
});

const paisFiltro = ref(props.paisSeleccionado || 'todos');

// Aplicar filtro cuando cambia
watch(paisFiltro, (newValue) => {
    router.get(route('cliente.boletos.comprar'), {
        pais: newValue
    }, {
        preserveState: true,
        preserveScroll: true
    });
});

// Función para obtener la imagen de la ruta (placeholder por ahora)
const obtenerImagenRuta = (ruta) => {
    // Si la ruta tiene imagen personalizada, usarla
    if (ruta.imagen) {
        return `/storage/${ruta.imagen}`;
    }
    // Si no, usar la imagen por defecto
    return busImage;
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
            <!-- Filtros -->
            <div class="p-6 rounded-2xl shadow-lg" style="background-color: var(--card-bg); border: 1px solid var(--border-color)">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium" style="color: var(--text-secondary)">🌎 Filtrar por país:</span>
                    </div>
                    <div class="flex-1 max-w-xs">
                        <select
                            v-model="paisFiltro"
                            class="w-full px-4 py-2 rounded-lg border transition-all focus:outline-none focus:ring-2"
                            style="background-color: var(--bg-primary); color: var(--text-primary); border-color: var(--border-primary);"
                        >
                            <option value="todos">Todos los países</option>
                            <option v-for="pais in paisesDisponibles" :key="pais" :value="pais">
                                {{ pais }}
                            </option>
                        </select>
                    </div>
                    <div v-if="paisFiltro !== 'todos'" class="text-sm" style="color: var(--text-tertiary)">
                        Mostrando {{ rutas.length }} ruta(s)
                    </div>
                </div>
            </div>

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
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xl font-bold" style="color: var(--text-primary)">
                                {{ ruta.nombre }}
                            </h3>
                            <span class="text-xs px-2 py-1 rounded-full" style="background-color: var(--primary-100); color: var(--primary-700);">
                                🌎 {{ ruta.pais_operacion || 'Bolivia' }}
                            </span>
                        </div>
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

