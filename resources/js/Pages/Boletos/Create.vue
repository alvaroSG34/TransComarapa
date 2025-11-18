<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BuscadorCliente from '@/Components/BuscadorCliente.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useHtml5Validation } from '@/composables/useHtml5Validation';

const props = defineProps({
    viajes: Array,
    clientes: Array
});

useHtml5Validation();

const form = useForm({
    viaje_id: '',
    cliente_id: '',
    asiento: ''
});

const viajeSeleccionado = computed(() => {
    return props.viajes.find(v => v.id === parseInt(form.viaje_id));
});

const asientosOcupados = ref([]);

// Cuando se selecciona un viaje, obtener asientos ocupados
watch(() => form.viaje_id, async (viajeId) => {
    if (viajeId) {
        // Aquí deberías hacer una petición al backend para obtener los asientos ocupados
        // Por ahora, lo dejamos vacío y se validará en el backend
        asientosOcupados.value = [];
        form.asiento = '';
    }
});

const submit = () => {
    form.post(route('boletos.store'), {
        preserveScroll: true
    });
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

const asientosDisponiblesArray = computed(() => {
    if (!viajeSeleccionado.value) return [];
    
    const total = viajeSeleccionado.value.asientos_totales;
    const asientos = [];
    
    for (let i = 1; i <= total; i++) {
        asientos.push({
            numero: i,
            ocupado: asientosOcupados.value.includes(i)
        });
    }
    
    return asientos;
});
</script>

<template>
    <Head title="Vender Boleto" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                Vender Boleto
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Selección de Viaje -->
                            <div>
                                <label for="viaje_id" class="block text-sm font-medium mb-2">
                                    Seleccionar Viaje Disponible *
                                </label>
                                <select
                                    id="viaje_id"
                                    v-model="form.viaje_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.viaje_id }"
                                >
                                    <option value="">Seleccione un viaje</option>
                                    <option v-for="viaje in viajes" :key="viaje.id" :value="viaje.id">
                                        {{ viaje.ruta_nombre }} ({{ viaje.origen }} → {{ viaje.destino }}) - 
                                        {{ formatearFecha(viaje.fecha_salida) }} - 
                                        Bs {{ parseFloat(viaje.precio).toFixed(2) }} - 
                                        {{ viaje.asientos_disponibles }} asientos disponibles
                                    </option>
                                </select>
                                <p v-if="form.errors.viaje_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.viaje_id }}
                                </p>
                                <p v-else-if="!viajes || viajes.length === 0" class="mt-1 text-sm text-red-600">
                                    No hay viajes disponibles. Asegúrese de crear viajes con estado "programado" y con asientos disponibles.
                                </p>
                            </div>

                            <!-- Información del Viaje Seleccionado -->
                            <div v-if="viajeSeleccionado" class="p-4 rounded-lg" style="background-color: var(--header-bg); border: 1px solid var(--border-color)">
                                <h3 class="text-md font-semibold mb-3" style="color: var(--text-primary)">
                                    Detalles del Viaje
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span style="color: var(--text-secondary)">Ruta:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ viajeSeleccionado.ruta_nombre }}
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary)">Vehículo:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ viajeSeleccionado.vehiculo_placa }}
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary)">Precio:</span>
                                        <span class="ml-2 font-medium text-green-600">
                                            Bs {{ parseFloat(viajeSeleccionado.precio).toFixed(2) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary)">Asientos Disponibles:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ viajeSeleccionado.asientos_disponibles }} / {{ viajeSeleccionado.asientos_totales }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Selección de Cliente con Buscador -->
                            <div>
                                <label for="cliente_id" class="block text-sm font-medium mb-2">
                                    Seleccionar Cliente *
                                </label>
                                <BuscadorCliente
                                    v-model="form.cliente_id"
                                    :required="true"
                                    :error="form.errors.cliente_id"
                                    endpoint="/boletos-buscar-cliente"
                                    registro-endpoint="/boletos-registrar-cliente"
                                />
                                <p v-if="!form.errors.cliente_id" class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Cliente que comprará el boleto. Puede buscar o registrar nuevo cliente.
                                </p>
                            </div>

                            <!-- Selección de Asiento -->
                            <div>
                                <label for="asiento" class="block text-sm font-medium mb-2">
                                    Número de Asiento *
                                </label>
                                <input
                                    id="asiento"
                                    type="number"
                                    v-model="form.asiento"
                                    required
                                    min="1"
                                    :max="viajeSeleccionado?.asientos_totales || 100"
                                    :disabled="!viajeSeleccionado"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.asiento, 'opacity-50 cursor-not-allowed': !viajeSeleccionado }"
                                />
                                <p v-if="form.errors.asiento" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.asiento }}
                                </p>
                                <p v-else-if="viajeSeleccionado" class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Ingrese un número de asiento del 1 al {{ viajeSeleccionado.asientos_totales }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Primero seleccione un viaje
                                </p>
                            </div>

                            <!-- Mapa de Asientos (Visual) -->
                            <div v-if="viajeSeleccionado" class="p-4 rounded-lg" style="background-color: var(--header-bg); border: 1px solid var(--border-color)">
                                <h3 class="text-md font-semibold mb-3" style="color: var(--text-primary)">
                                    Mapa de Asientos
                                </h3>
                                <div class="grid grid-cols-5 sm:grid-cols-10 gap-2">
                                    <button
                                        v-for="asiento in asientosDisponiblesArray"
                                        :key="asiento.numero"
                                        type="button"
                                        @click="form.asiento = asiento.numero"
                                        :disabled="asiento.ocupado"
                                        class="p-3 rounded text-sm font-medium transition-colors"
                                        :class="{
                                            'bg-green-100 text-green-800 hover:bg-green-200': !asiento.ocupado && form.asiento != asiento.numero,
                                            'bg-blue-600 text-white': form.asiento == asiento.numero,
                                            'bg-gray-300 text-gray-500 cursor-not-allowed': asiento.ocupado
                                        }"
                                    >
                                        {{ asiento.numero }}
                                    </button>
                                </div>
                                <div class="flex items-center gap-6 mt-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-green-100 rounded"></div>
                                        <span style="color: var(--text-secondary)">Disponible</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-blue-600 rounded"></div>
                                        <span style="color: var(--text-secondary)">Seleccionado</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-gray-300 rounded"></div>
                                        <span style="color: var(--text-secondary)">Ocupado</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen de la Venta -->
                            <div v-if="viajeSeleccionado && form.cliente_id && form.asiento" class="p-4 rounded-lg border-2" style="background-color: var(--card-bg); border-color: var(--button-primary-bg)">
                                <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">
                                    Resumen de la Venta
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span style="color: var(--text-secondary)">Viaje:</span>
                                        <span class="font-medium" style="color: var(--text-primary)">
                                            {{ viajeSeleccionado.ruta_nombre }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span style="color: var(--text-secondary)">Asiento:</span>
                                        <span class="font-medium" style="color: var(--text-primary)">
                                            # {{ form.asiento }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t" style="border-color: var(--border-color)">
                                        <span class="font-semibold" style="color: var(--text-primary)">Total a Pagar:</span>
                                        <span class="font-bold text-lg text-green-600">
                                            Bs {{ parseFloat(viajeSeleccionado.precio).toFixed(2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex items-center justify-end space-x-4 pt-4 border-t" style="border-color: var(--border-color)">
                                <a
                                    :href="route('boletos.index')"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                                >
                                    Cancelar
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing || !viajes || viajes.length === 0"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                    :class="{ 'opacity-50 cursor-not-allowed': form.processing || !viajes || viajes.length === 0 }"
                                >
                                    {{ form.processing ? 'Procesando...' : 'Confirmar Venta' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
