<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    secretaria: Object
});
</script>

<template>
    <Head title="Detalle de Secretaria" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                    Detalle de Secretaria
                </h2>
                <a
                    :href="route('secretarias.index')"
                    class="px-4 py-2 rounded-md text-sm font-medium"
                    style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                >
                    Volver
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary)">
                                    Nombre
                                </label>
                                <p class="text-sm" style="color: var(--text-primary)">
                                    {{ secretaria.nombre }}
                                </p>
                            </div>

                            <!-- Apellido -->
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary)">
                                    Apellido
                                </label>
                                <p class="text-sm" style="color: var(--text-primary)">
                                    {{ secretaria.apellido }}
                                </p>
                            </div>

                            <!-- CI -->
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary)">
                                    Cédula de Identidad
                                </label>
                                <p class="text-sm" style="color: var(--text-primary)">
                                    {{ secretaria.ci }}
                                </p>
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary)">
                                    Teléfono
                                </label>
                                <p class="text-sm" style="color: var(--text-primary)">
                                    {{ secretaria.telefono || 'N/A' }}
                                </p>
                            </div>

                            <!-- Correo -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium mb-2" style="color: var(--text-secondary)">
                                    Correo Electrónico
                                </label>
                                <p class="text-sm" style="color: var(--text-primary)">
                                    {{ secretaria.correo }}
                                </p>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-center justify-end space-x-4 pt-6 mt-6 border-t" style="border-color: var(--border-color)">
                            <a
                                :href="route('secretarias.index')"
                                class="px-4 py-2 rounded-md text-sm font-medium"
                                style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                            >
                                Volver
                            </a>
                            <a
                                :href="route('secretarias.edit', secretaria.id)"
                                class="px-4 py-2 rounded-md text-sm font-medium"
                                style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                            >
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

