<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useHtml5Validation } from '@/composables/useHtml5Validation';

const props = defineProps({
    vehiculo: Object,
    conductores: Array
});

useHtml5Validation();

const form = useForm({
    placa: props.vehiculo.placa,
    marca: props.vehiculo.marca,
    modelo: props.vehiculo.modelo,
    conductor_id: props.vehiculo.conductor_id || '',
    avatar: null
});

const imagePreview = ref(props.vehiculo.img_url_full || null);
const fileInput = ref(null);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validar tipo de archivo
        if (!file.type.startsWith('image/')) {
            alert('Por favor selecciona una imagen válida');
            return;
        }
        
        // Validar tamaño (2MB)
        if (file.size > 2048 * 1024) {
            alert('La imagen debe ser menor a 2MB');
            return;
        }
        
        form.avatar = file;
        
        // Crear vista previa
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.avatar = null;
    imagePreview.value = props.vehiculo.img_url_full || null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.transform((data) => {
        const formData = new FormData();
        Object.keys(data).forEach(key => {
            if (data[key] !== null && data[key] !== undefined && data[key] !== '') {
                formData.append(key, data[key]);
            }
        });
        formData.append('_method', 'PUT');
        return formData;
    }).post(route('vehiculos.update', props.vehiculo.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        }
    });
};
</script>

<template>
    <Head title="Editar Vehículo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                Editar Vehículo
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

                            <!-- Imagen del Vehículo (Opcional) -->
                            <div>
                                <label for="avatar" class="block text-sm font-medium mb-2">
                                    Imagen del Vehículo (Opcional)
                                </label>
                                <div class="mt-2">
                                    <input
                                        ref="fileInput"
                                        id="avatar"
                                        type="file"
                                        accept="image/jpeg,image/png,image/jpg,image/gif"
                                        @change="handleFileChange"
                                        class="block w-full text-sm"
                                        style="color: var(--text-primary)"
                                    />
                                    <p class="mt-1 text-xs" style="color: var(--text-secondary)">
                                        PNG, JPG, GIF hasta 2MB. Deje vacío para mantener la imagen actual.
                                    </p>
                                    <button
                                        v-if="imagePreview && form.avatar"
                                        type="button"
                                        @click="removeImage"
                                        class="mt-2 text-sm text-red-600 hover:text-red-800"
                                    >
                                        Cancelar cambio
                                    </button>
                                </div>
                                
                                <!-- Vista previa de imagen (más grande) -->
                                <div v-if="imagePreview" class="mt-4">
                                    <p class="text-sm font-medium mb-3" style="color: var(--text-primary)">
                                        Vista previa:
                                    </p>
                                    <div class="flex justify-center">
                                        <img
                                            :src="imagePreview"
                                            alt="Vista previa del vehículo"
                                            class="max-w-md w-full h-auto object-cover rounded-lg border-2 shadow-md"
                                            style="border-color: var(--border-color); max-height: 400px;"
                                            @error="$event.target.style.display='none'"
                                        />
                                    </div>
                                </div>
                                <p v-if="form.errors.avatar" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.avatar }}
                                </p>
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
                                    {{ form.processing ? 'Guardando...' : 'Actualizar Vehículo' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
