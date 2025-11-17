<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useHtml5Validation } from '@/composables/useHtml5Validation';

const props = defineProps({
    conductores: Array
});

useHtml5Validation();

const form = useForm({
    placa: '',
    marca: '',
    modelo: '',
    conductor_id: '',
    img_url: ''
});

const submit = () => {
    form.post(route('vehiculos.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Nuevo Vehículo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                Registrar Nuevo Vehículo
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Placa -->
                            <div>
                                <label for="placa" class="block text-sm font-medium mb-2">
                                    Placa *
                                </label>
                                <input
                                    id="placa"
                                    type="text"
                                    v-model="form.placa"
                                    required
                                    maxlength="20"
                                    placeholder="Ej: 1234-ABC"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.placa }"
                                />
                                <p v-if="form.errors.placa" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.placa }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Ingrese la placa del vehículo (máximo 20 caracteres)
                                </p>
                            </div>

                            <!-- Marca -->
                            <div>
                                <label for="marca" class="block text-sm font-medium mb-2">
                                    Marca *
                                </label>
                                <input
                                    id="marca"
                                    type="text"
                                    v-model="form.marca"
                                    required
                                    maxlength="50"
                                    placeholder="Ej: Toyota, Mercedes-Benz, Volvo"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.marca }"
                                />
                                <p v-if="form.errors.marca" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.marca }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Marca del vehículo (máximo 50 caracteres)
                                </p>
                            </div>

                            <!-- Modelo -->
                            <div>
                                <label for="modelo" class="block text-sm font-medium mb-2">
                                    Modelo *
                                </label>
                                <input
                                    id="modelo"
                                    type="text"
                                    v-model="form.modelo"
                                    required
                                    maxlength="50"
                                    placeholder="Ej: Coaster, Sprinter, 9700"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.modelo }"
                                />
                                <p v-if="form.errors.modelo" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.modelo }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Modelo del vehículo (máximo 50 caracteres)
                                </p>
                            </div>

                            <!-- Conductor -->
                            <div>
                                <label for="conductor_id" class="block text-sm font-medium mb-2">
                                    Conductor
                                </label>
                                <select
                                    id="conductor_id"
                                    v-model="form.conductor_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.conductor_id }"
                                >
                                    <option value="">Sin asignar</option>
                                    <option v-for="conductor in conductores" :key="conductor.id" :value="conductor.id">
                                        {{ conductor.nombre }} {{ conductor.apellido }} - CI: {{ conductor.ci }}
                                    </option>
                                </select>
                                <p v-if="form.errors.conductor_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.conductor_id }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Seleccione el conductor asignado al vehículo (opcional)
                                </p>
                            </div>

                            <!-- URL de Imagen -->
                            <div>
                                <label for="img_url" class="block text-sm font-medium mb-2">
                                    URL de Imagen
                                </label>
                                <input
                                    id="img_url"
                                    type="url"
                                    v-model="form.img_url"
                                    maxlength="255"
                                    placeholder="https://ejemplo.com/imagen.jpg"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.img_url }"
                                />
                                <p v-if="form.errors.img_url" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.img_url }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    URL de la imagen del vehículo (opcional)
                                </p>
                                
                                <!-- Vista previa de imagen -->
                                <div v-if="form.img_url" class="mt-3">
                                    <p class="text-sm font-medium mb-2">Vista previa:</p>
                                    <img :src="form.img_url" alt="Vista previa" class="h-32 w-32 object-cover rounded border" style="border-color: var(--border-color)" @error="$event.target.style.display='none'">
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex items-center justify-end space-x-4 pt-4">
                                <a
                                    :href="route('vehiculos.index')"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                                >
                                    Cancelar
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                >
                                    {{ form.processing ? 'Guardando...' : 'Registrar Vehículo' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
