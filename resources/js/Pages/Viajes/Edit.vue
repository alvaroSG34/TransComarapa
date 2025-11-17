<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useHtml5Validation } from '@/composables/useHtml5Validation';

const props = defineProps({
    viaje: Object,
    rutas: Array,
    vehiculos: Array
});

useHtml5Validation();

const form = useForm({
    ruta_id: props.viaje.ruta_id,
    vehiculo_id: props.viaje.vehiculo_id,
    fecha_salida: props.viaje.fecha_salida ? new Date(props.viaje.fecha_salida).toISOString().slice(0, 16) : '',
    fecha_llegada: props.viaje.fecha_llegada ? new Date(props.viaje.fecha_llegada).toISOString().slice(0, 16) : '',
    precio: props.viaje.precio,
    asientos_totales: props.viaje.asientos_totales
});

const submit = () => {
    form.put(route('viajes.update', props.viaje.id), {
        preserveScroll: true
    });
};

const rutaSeleccionada = computed(() => {
    return props.rutas.find(r => r.id === parseInt(form.ruta_id));
});

const vehiculoSeleccionado = computed(() => {
    return props.vehiculos.find(v => v.id === parseInt(form.vehiculo_id));
});

const boletosVendidos = computed(() => {
    return props.viaje.asientos_totales - props.viaje.asientos_disponibles;
});

const puedeEditarAsientos = computed(() => {
    return boletosVendidos.value === 0;
});
</script>

<template>
    <Head title="Editar Viaje" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                Editar Viaje
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Información de Estado -->
                <div v-if="viaje.estado !== 'programado'" class="mb-6 p-4 rounded-lg" style="background-color: var(--card-bg); border-left: 4px solid #f59e0b;">
                    <p class="text-sm font-medium" style="color: var(--text-primary)">
                        ⚠️ Advertencia: Este viaje está en estado "{{ viaje.estado }}". 
                        Solo los viajes en estado "programado" pueden ser editados.
                    </p>
                </div>

                <div v-if="!puedeEditarAsientos" class="mb-6 p-4 rounded-lg" style="background-color: var(--card-bg); border-left: 4px solid #3b82f6;">
                    <p class="text-sm font-medium" style="color: var(--text-primary)">
                        ℹ️ Información: Se han vendido {{ boletosVendidos }} boletos. 
                        No puede reducir el total de asientos por debajo de esta cantidad.
                    </p>
                </div>

                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Selección de Ruta -->
                            <div>
                                <label for="ruta_id" class="block text-sm font-medium mb-2">
                                    Ruta *
                                </label>
                                <select
                                    id="ruta_id"
                                    v-model="form.ruta_id"
                                    required
                                    :disabled="viaje.estado !== 'programado'"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.ruta_id, 'opacity-50 cursor-not-allowed': viaje.estado !== 'programado' }"
                                >
                                    <option value="">Seleccione una ruta</option>
                                    <option v-for="ruta in rutas" :key="ruta.id" :value="ruta.id">
                                        {{ ruta.nombre }} ({{ ruta.origen }} → {{ ruta.destino }})
                                    </option>
                                </select>
                                <p v-if="form.errors.ruta_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.ruta_id }}
                                </p>
                                <p v-if="rutaSeleccionada" class="mt-2 text-sm" style="color: var(--text-secondary)">
                                    Duración estimada: {{ rutaSeleccionada.duracion_estimada }} | 
                                    Distancia: {{ rutaSeleccionada.distancia }} km
                                </p>
                            </div>

                            <!-- Selección de Vehículo -->
                            <div>
                                <label for="vehiculo_id" class="block text-sm font-medium mb-2">
                                    Vehículo *
                                </label>
                                <select
                                    id="vehiculo_id"
                                    v-model="form.vehiculo_id"
                                    required
                                    :disabled="viaje.estado !== 'programado'"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.vehiculo_id, 'opacity-50 cursor-not-allowed': viaje.estado !== 'programado' }"
                                >
                                    <option value="">Seleccione un vehículo</option>
                                    <option v-for="vehiculo in vehiculos" :key="vehiculo.id" :value="vehiculo.id">
                                        {{ vehiculo.placa }} - {{ vehiculo.marca }} {{ vehiculo.modelo }}
                                    </option>
                                </select>
                                <p v-if="form.errors.vehiculo_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.vehiculo_id }}
                                </p>
                                <p v-if="vehiculoSeleccionado" class="mt-2 text-sm" style="color: var(--text-secondary)">
                                    Conductor: {{ vehiculoSeleccionado.conductor_id || 'Sin asignar' }}
                                </p>
                            </div>

                            <!-- Fecha de Salida -->
                            <div>
                                <label for="fecha_salida" class="block text-sm font-medium mb-2">
                                    Fecha y Hora de Salida *
                                </label>
                                <input
                                    id="fecha_salida"
                                    type="datetime-local"
                                    v-model="form.fecha_salida"
                                    required
                                    :disabled="viaje.estado !== 'programado'"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.fecha_salida, 'opacity-50 cursor-not-allowed': viaje.estado !== 'programado' }"
                                />
                                <p v-if="form.errors.fecha_salida" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.fecha_salida }}
                                </p>
                                <p v-else-if="viaje.estado === 'programado'" class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    La fecha de salida debe ser posterior a la fecha actual
                                </p>
                            </div>

                            <!-- Fecha de Llegada (opcional) -->
                            <div>
                                <label for="fecha_llegada" class="block text-sm font-medium mb-2">
                                    Fecha y Hora de Llegada Estimada
                                </label>
                                <input
                                    id="fecha_llegada"
                                    type="datetime-local"
                                    v-model="form.fecha_llegada"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.fecha_llegada }"
                                />
                                <p v-if="form.errors.fecha_llegada" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.fecha_llegada }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Puede actualizar esta información durante el viaje
                                </p>
                            </div>

                            <!-- Precio por Asiento -->
                            <div>
                                <label for="precio" class="block text-sm font-medium mb-2">
                                    Precio por Asiento (Bs) *
                                </label>
                                <input
                                    id="precio"
                                    type="number"
                                    v-model="form.precio"
                                    required
                                    min="0"
                                    step="0.01"
                                    :disabled="viaje.estado !== 'programado'"
                                    placeholder="0.00"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.precio, 'opacity-50 cursor-not-allowed': viaje.estado !== 'programado' }"
                                />
                                <p v-if="form.errors.precio" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.precio }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Ingrese el precio en bolivianos (mínimo 0)
                                </p>
                            </div>

                            <!-- Total de Asientos -->
                            <div>
                                <label for="asientos_totales" class="block text-sm font-medium mb-2">
                                    Total de Asientos Disponibles *
                                </label>
                                <input
                                    id="asientos_totales"
                                    type="number"
                                    v-model="form.asientos_totales"
                                    required
                                    :min="boletosVendidos"
                                    max="100"
                                    :disabled="viaje.estado !== 'programado'"
                                    placeholder="0"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.asientos_totales, 'opacity-50 cursor-not-allowed': viaje.estado !== 'programado' }"
                                />
                                <p v-if="form.errors.asientos_totales" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.asientos_totales }}
                                </p>
                                <p v-else-if="!puedeEditarAsientos" class="mt-1 text-sm text-red-600">
                                    Ya se vendieron {{ boletosVendidos }} boletos. Debe ingresar al menos {{ boletosVendidos }} asientos.
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Cantidad de asientos disponibles para la venta ({{ boletosVendidos }}-100)
                                </p>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex items-center justify-end space-x-4 pt-4">
                                <a
                                    :href="route('viajes.index')"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                                >
                                    Cancelar
                                </a>
                                <button
                                    v-if="viaje.estado === 'programado'"
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                >
                                    {{ form.processing ? 'Guardando...' : 'Actualizar Viaje' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
