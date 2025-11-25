<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    mostrarRuta: {
        type: Boolean,
        default: true, // Mostrar la ruta actual junto al contador
    },
    mostrarTotal: {
        type: Boolean,
        default: false, // Mostrar contador total en lugar de por ruta
    }
});

const contador = ref(0);
const rutaActual = ref('');
const cargando = ref(true);

const page = usePage();

const obtenerRutaActual = () => {
    // Obtener la ruta actual desde Inertia o window.location
    if (page.url) {
        return page.url;
    }
    return window.location.pathname;
};

const cargarContador = async () => {
    try {
        cargando.value = true;
        const ruta = obtenerRutaActual();
        rutaActual.value = ruta;

        // Primero intentar usar el contador compartido desde el servidor (más rápido y preciso)
        if (page.props.contadorVisitas !== null && page.props.contadorVisitas !== undefined && !props.mostrarTotal) {
            contador.value = page.props.contadorVisitas;
            cargando.value = false;
            return;
        }

        // Si no está disponible, hacer petición API
        if (props.mostrarTotal) {
            // Obtener contador total
            const response = await axios.get(route('visitas.total'));
            contador.value = response.data.contador;
        } else {
            // Obtener contador por ruta
            const response = await axios.get(route('visitas.contador'), {
                params: { ruta: ruta }
            });
            contador.value = response.data.contador;
        }
    } catch (error) {
        console.error('Error al cargar contador de visitas:', error);
        contador.value = 0;
    } finally {
        cargando.value = false;
    }
};

onMounted(() => {
    cargarContador();
});

// Observar cambios en la URL de Inertia para actualizar el contador
watch(() => page.url, (newUrl, oldUrl) => {
    if (newUrl !== oldUrl && !props.mostrarTotal) {
        // Recargar cuando cambia la página
        cargarContador();
    }
}, { immediate: false });

// También observar cambios en el contador compartido
watch(() => page.props.contadorVisitas, (nuevoContador) => {
    if (nuevoContador !== null && nuevoContador !== undefined && !props.mostrarTotal) {
        contador.value = nuevoContador;
        cargando.value = false;
    }
});
</script>

<template>
    <div class="flex items-center gap-2 text-sm" style="color: var(--text-secondary);">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <span v-if="cargando" class="animate-pulse">Cargando...</span>
        <span v-else>
            <span v-if="mostrarTotal">Total: </span>
            <span v-else-if="mostrarRuta && rutaActual" class="hidden sm:inline">{{ rutaActual }}: </span>
            <span class="font-semibold" style="color: var(--primary-600);">{{ contador.toLocaleString() }}</span>
            <span class="ml-1">visitas</span>
        </span>
    </div>
</template>

