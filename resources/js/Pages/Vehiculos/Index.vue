<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    vehiculos: Array
});

const eliminarVehiculo = (id, placa) => {
    if (confirm(`¿Está seguro de eliminar el vehículo ${placa}?`)) {
        router.delete(route('vehiculos.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Vehículos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                    Vehículos
                </h2>
                <a
                    :href="route('vehiculos.create')"
                    class="px-4 py-2 rounded-md text-sm font-medium"
                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                >
                    + Nuevo Vehículo
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <!-- Lista de Vehículos -->
                        <div v-if="vehiculos.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead>
                                    <tr style="background-color: var(--header-bg)">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Placa
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Marca
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Modelo
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Conductor
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Imagen
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="vehiculo in vehiculos" :key="vehiculo.id" class="hover:bg-opacity-50" style="background-color: var(--card-bg)">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ vehiculo.placa }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ vehiculo.marca }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ vehiculo.modelo }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="vehiculo.conductor" class="text-sm" style="color: var(--text-primary)">
                                                {{ vehiculo.conductor.nombre }} {{ vehiculo.conductor.apellido }}
                                            </div>
                                            <div v-else class="text-sm" style="color: var(--text-secondary)">
                                                Sin asignar
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="vehiculo.img_url_full" class="flex items-center">
                                                <img 
                                                    :src="vehiculo.img_url_full" 
                                                    :alt="vehiculo.placa" 
                                                    class="h-20 w-20 rounded-lg object-cover border-2"
                                                    style="border-color: var(--border-color)"
                                                    @error="$event.target.style.display='none'"
                                                >
                                            </div>
                                            <div v-else class="flex items-center">
                                                <div class="h-20 w-20 rounded-lg flex items-center justify-center border-2" style="background-color: var(--input-bg); border-color: var(--border-color)">
                                                    <svg class="h-10 w-10" style="color: var(--text-secondary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a
                                                :href="route('vehiculos.edit', vehiculo.id)"
                                                class="inline-block px-3 py-1 rounded text-sm"
                                                style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                            >
                                                Editar
                                            </a>
                                            <button
                                                @click="eliminarVehiculo(vehiculo.id, vehiculo.placa)"
                                                class="inline-block px-3 py-1 rounded text-sm bg-red-600 text-white hover:bg-red-700"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Estado Vacío -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 opacity-50" style="color: var(--text-secondary)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium" style="color: var(--text-primary)">
                                No hay vehículos registrados
                            </h3>
                            <p class="mt-1 text-sm" style="color: var(--text-secondary)">
                                Comienza registrando un nuevo vehículo.
                            </p>
                            <div class="mt-6">
                                <a
                                    :href="route('vehiculos.create')"
                                    class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                >
                                    + Nuevo Vehículo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
