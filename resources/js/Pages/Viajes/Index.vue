<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    viajes: {
        type: Array,
        required: true
    }
});

const deleteViaje = (id) => {
    if (confirm('¿Estás seguro de eliminar este viaje? Se eliminarán también todos los boletos asociados.')) {
        router.delete(route('viajes.destroy', id));
    }
};

const cambiarEstado = (id, nuevoEstado) => {
    const mensajes = {
        en_curso: '¿Iniciar este viaje?',
        finalizado: '¿Marcar este viaje como finalizado?',
        cancelado: '¿Cancelar este viaje? Los pasajeros deberán ser reembolsados.'
    };

    if (confirm(mensajes[nuevoEstado])) {
        router.post(route('viajes.cambiar-estado', id), { estado: nuevoEstado });
    }
};

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleString('es-BO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getEstadoBadgeClass = (estado) => {
    const classes = {
        programado: 'bg-blue-100 text-blue-800',
        en_curso: 'bg-yellow-100 text-yellow-800',
        finalizado: 'bg-green-100 text-green-800',
        cancelado: 'bg-red-100 text-red-800'
    };
    return classes[estado] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Gestionar Viajes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight" style="color: var(--text-primary)">
                    Gestionar Viajes
                </h2>
                <Link
                    :href="route('viajes.create')"
                    class="px-4 py-2 rounded-lg text-white font-semibold transition-all hover:opacity-90"
                    style="background-color: var(--primary-600);"
                >
                    + Nuevo Viaje
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--bg-secondary);">
                    <div class="p-6">
                        <!-- Tabla de viajes -->
                        <div v-if="viajes.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-primary);">
                                <thead>
                                    <tr style="background-color: var(--bg-primary);">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Ruta</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Vehículo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Salida</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Precio</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Asientos</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-primary);">
                                    <tr v-for="viaje in viajes" :key="viaje.id" style="background-color: var(--bg-secondary);">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary);">{{ viaje.ruta.nombre }}</div>
                                            <div class="text-xs" style="color: var(--text-tertiary);">{{ viaje.ruta.origen }} → {{ viaje.ruta.destino }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-secondary);">
                                            {{ viaje.vehiculo.placa }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-secondary);">
                                            {{ formatearFecha(viaje.fecha_salida) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold" style="color: var(--text-primary);">
                                            Bs {{ viaje.precio }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-secondary);">
                                            {{ viaje.asientos_disponibles }} / {{ viaje.asientos_totales }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getEstadoBadgeClass(viaje.estado)">
                                                {{ viaje.estado }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2">
                                                <!-- Cambiar estado -->
                                                <button
                                                    v-if="viaje.estado === 'programado'"
                                                    @click="cambiarEstado(viaje.id, 'en_curso')"
                                                    class="px-3 py-1 rounded text-white transition-all hover:opacity-80 text-xs"
                                                    style="background-color: #f59e0b;"
                                                    title="Iniciar viaje"
                                                >
                                                    Iniciar
                                                </button>
                                                <button
                                                    v-if="viaje.estado === 'en_curso'"
                                                    @click="cambiarEstado(viaje.id, 'finalizado')"
                                                    class="px-3 py-1 rounded text-white transition-all hover:opacity-80 text-xs"
                                                    style="background-color: #10b981;"
                                                    title="Finalizar viaje"
                                                >
                                                    Finalizar
                                                </button>
                                                
                                                <Link
                                                    v-if="viaje.estado === 'programado'"
                                                    :href="route('viajes.edit', viaje.id)"
                                                    class="px-3 py-1 rounded text-white transition-all hover:opacity-80"
                                                    style="background-color: var(--secondary-600);"
                                                >
                                                    Editar
                                                </Link>
                                                <button
                                                    v-if="viaje.estado === 'programado' && viaje.asientos_disponibles === viaje.asientos_totales"
                                                    @click="deleteViaje(viaje.id)"
                                                    class="px-3 py-1 rounded text-white transition-all hover:opacity-80"
                                                    style="background-color: #dc2626;"
                                                >
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Sin viajes -->
                        <div v-else class="text-center py-12">
                            <div class="text-6xl mb-4">🚌</div>
                            <h3 class="text-lg font-semibold mb-2" style="color: var(--text-primary);">
                                No hay viajes programados
                            </h3>
                            <p class="mb-4" style="color: var(--text-secondary);">
                                Comienza creando tu primer viaje
                            </p>
                            <Link
                                :href="route('viajes.create')"
                                class="inline-block px-4 py-2 rounded-lg text-white font-semibold transition-all hover:opacity-90"
                                style="background-color: var(--primary-600);"
                            >
                                + Nuevo Viaje
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
