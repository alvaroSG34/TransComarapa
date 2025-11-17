<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useHtml5Validation } from '@/composables/useHtml5Validation';

const props = defineProps({
    conductor: Object
});

useHtml5Validation();

const form = useForm({
    nombre: props.conductor.nombre,
    apellido: props.conductor.apellido,
    ci: props.conductor.ci,
    telefono: props.conductor.telefono || '',
    correo: props.conductor.correo,
    password: '',
    password_confirmation: ''
});

const submit = () => {
    form.put(route('conductores.update', props.conductor.id), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Editar Conductor" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                Editar Conductor
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nombre -->
                                <div>
                                    <label for="nombre" class="block text-sm font-medium mb-2">
                                        Nombre *
                                    </label>
                                    <input
                                        id="nombre"
                                        type="text"
                                        v-model="form.nombre"
                                        required
                                        maxlength="100"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                        :class="{ 'border-red-500': form.errors.nombre }"
                                    />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.nombre }}
                                    </p>
                                </div>

                                <!-- Apellido -->
                                <div>
                                    <label for="apellido" class="block text-sm font-medium mb-2">
                                        Apellido *
                                    </label>
                                    <input
                                        id="apellido"
                                        type="text"
                                        v-model="form.apellido"
                                        required
                                        maxlength="100"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                        :class="{ 'border-red-500': form.errors.apellido }"
                                    />
                                    <p v-if="form.errors.apellido" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.apellido }}
                                    </p>
                                </div>

                                <!-- CI -->
                                <div>
                                    <label for="ci" class="block text-sm font-medium mb-2">
                                        Cédula de Identidad *
                                    </label>
                                    <input
                                        id="ci"
                                        type="text"
                                        v-model="form.ci"
                                        required
                                        maxlength="20"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                        :class="{ 'border-red-500': form.errors.ci }"
                                    />
                                    <p v-if="form.errors.ci" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.ci }}
                                    </p>
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <label for="telefono" class="block text-sm font-medium mb-2">
                                        Teléfono
                                    </label>
                                    <input
                                        id="telefono"
                                        type="tel"
                                        v-model="form.telefono"
                                        maxlength="20"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                        :class="{ 'border-red-500': form.errors.telefono }"
                                    />
                                    <p v-if="form.errors.telefono" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.telefono }}
                                    </p>
                                </div>
                            </div>

                            <!-- Correo -->
                            <div>
                                <label for="correo" class="block text-sm font-medium mb-2">
                                    Correo Electrónico *
                                </label>
                                <input
                                    id="correo"
                                    type="email"
                                    v-model="form.correo"
                                    required
                                    maxlength="255"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.correo }"
                                />
                                <p v-if="form.errors.correo" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.correo }}
                                </p>
                            </div>

                            <div class="border-t pt-6" style="border-color: var(--border-color)">
                                <h3 class="text-lg font-medium mb-4" style="color: var(--text-primary)">
                                    Cambiar Contraseña (Opcional)
                                </h3>
                                <p class="text-sm mb-4" style="color: var(--text-secondary)">
                                    Deje estos campos vacíos si no desea cambiar la contraseña
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Nueva Contraseña -->
                                    <div>
                                        <label for="password" class="block text-sm font-medium mb-2">
                                            Nueva Contraseña
                                        </label>
                                        <input
                                            id="password"
                                            type="password"
                                            v-model="form.password"
                                            minlength="8"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                            :class="{ 'border-red-500': form.errors.password }"
                                        />
                                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.password }}
                                        </p>
                                        <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                            Mínimo 8 caracteres
                                        </p>
                                    </div>

                                    <!-- Confirmar Nueva Contraseña -->
                                    <div>
                                        <label for="password_confirmation" class="block text-sm font-medium mb-2">
                                            Confirmar Nueva Contraseña
                                        </label>
                                        <input
                                            id="password_confirmation"
                                            type="password"
                                            v-model="form.password_confirmation"
                                            minlength="8"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                            :class="{ 'border-red-500': form.errors.password_confirmation }"
                                        />
                                        <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.password_confirmation }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex items-center justify-end space-x-4 pt-4 border-t" style="border-color: var(--border-color)">
                                <a
                                    :href="route('conductores.index')"
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
                                    {{ form.processing ? 'Guardando...' : 'Actualizar Conductor' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
