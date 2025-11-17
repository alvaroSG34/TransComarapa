<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useValidation } from '@/composables/useValidation';

const { errors: clientErrors, validate, clearError } = useValidation();

const form = useForm({
    nombre: '',
    origen: '',
    destino: '',
});

const submit = () => {
    const rules = {
        nombre: ['required', 'min:3', 'max:255'],
        origen: ['required', 'min:3', 'max:100'],
        destino: ['required', 'min:3', 'max:100'],
    };

    if (!validate(form, rules)) {
        return;
    }

    form.post(route('rutas.store'), {
        onError: () => {
            // Los errores del servidor se mostrarán automáticamente
        }
    });
};

const handleInput = (field) => {
    clearError(field);
    form.clearErrors(field);
};
</script>

<template>
    <Head title="Nueva Ruta" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight" style="color: var(--text-primary)">
                    Nueva Ruta
                </h2>
                <Link
                    :href="route('rutas.index')"
                    class="px-4 py-2 rounded-lg font-semibold transition-all hover:opacity-90"
                    style="background-color: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-primary);"
                >
                    ← Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--bg-secondary);">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Nombre de la ruta -->
                            <div>
                                <label for="nombre" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                                    Nombre de la Ruta
                                </label>
                                <input
                                    id="nombre"
                                    type="text"
                                    v-model="form.nombre"
                                    placeholder="Ej: Santa Cruz - Comarapa"
                                    @input="handleInput('nombre')"
                                    class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    :class="(form.errors.nombre || clientErrors.nombre) ? 'border-red-500 focus:ring-red-500' : ''"
                                    style="
                                        background-color: var(--bg-primary);
                                        color: var(--text-primary);
                                        border-color: var(--border-primary);
                                    "
                                />
                                <InputError class="mt-2" :message="clientErrors.nombre || form.errors.nombre" />
                            </div>

                            <!-- Origen -->
                            <div>
                                <label for="origen" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                                    Origen
                                </label>
                                <input
                                    id="origen"
                                    type="text"
                                    v-model="form.origen"
                                    placeholder="Ej: Santa Cruz"
                                    @input="handleInput('origen')"
                                    class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    :class="(form.errors.origen || clientErrors.origen) ? 'border-red-500 focus:ring-red-500' : ''"
                                    style="
                                        background-color: var(--bg-primary);
                                        color: var(--text-primary);
                                        border-color: var(--border-primary);
                                    "
                                />
                                <InputError class="mt-2" :message="clientErrors.origen || form.errors.origen" />
                            </div>

                            <!-- Destino -->
                            <div>
                                <label for="destino" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                                    Destino
                                </label>
                                <input
                                    id="destino"
                                    type="text"
                                    v-model="form.destino"
                                    placeholder="Ej: Comarapa"
                                    @input="handleInput('destino')"
                                    class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    :class="(form.errors.destino || clientErrors.destino) ? 'border-red-500 focus:ring-red-500' : ''"
                                    style="
                                        background-color: var(--bg-primary);
                                        color: var(--text-primary);
                                        border-color: var(--border-primary);
                                    "
                                />
                                <InputError class="mt-2" :message="clientErrors.destino || form.errors.destino" />
                            </div>

                            <!-- Botones -->
                            <div class="flex gap-3 justify-end">
                                <Link
                                    :href="route('rutas.index')"
                                    class="px-6 py-3 rounded-lg font-semibold transition-all hover:opacity-90"
                                    style="background-color: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-primary);"
                                >
                                    Cancelar
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-3 rounded-lg font-semibold text-white transition-all hover:opacity-90 disabled:opacity-50"
                                    style="background-color: var(--primary-600);"
                                >
                                    <span v-if="!form.processing">Crear Ruta</span>
                                    <span v-else>Creando...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
