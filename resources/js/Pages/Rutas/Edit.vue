<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useValidation } from '@/composables/useValidation';
import { PAISES } from '@/utils/paises.js';
import { computed } from 'vue';

const props = defineProps({
    ruta: {
        type: Object,
        required: true
    }
});

const { errors: clientErrors, validate, clearError } = useValidation();

const form = useForm({
    nombre: props.ruta.nombre,
    origen: props.ruta.origen,
    destino: props.ruta.destino,
    pais_operacion: props.ruta.pais_operacion || 'Bolivia',
    moneda: props.ruta.moneda || 'BOB',
});

// Auto-completar moneda cuando cambia el país
const paisSeleccionado = computed(() => {
    return PAISES.find(p => p.nombre === form.pais_operacion);
});

const actualizarMoneda = () => {
    if (paisSeleccionado.value) {
        form.moneda = paisSeleccionado.value.moneda;
    }
};

const submit = () => {
    const rules = {
        nombre: ['required', 'min:3', 'max:255'],
        origen: ['required', 'min:3', 'max:100'],
        destino: ['required', 'min:3', 'max:100'],
        pais_operacion: ['required'],
        moneda: ['required'],
    };

    if (!validate(form, rules)) {
        return;
    }

    form.put(route('rutas.update', props.ruta.id), {
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
    <Head title="Editar Ruta" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight" style="color: var(--text-primary)">
                    Editar Ruta
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

                            <!-- País de Operación -->
                            <div>
                                <label for="pais_operacion" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                                    País de Operación *
                                </label>
                                <select
                                    id="pais_operacion"
                                    v-model="form.pais_operacion"
                                    @change="actualizarMoneda(); handleInput('pais_operacion')"
                                    class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                    :class="(form.errors.pais_operacion || clientErrors.pais_operacion) ? 'border-red-500 focus:ring-red-500' : ''"
                                    style="
                                        background-color: var(--bg-primary);
                                        color: var(--text-primary);
                                        border-color: var(--border-primary);
                                    "
                                >
                                    <option value="" disabled>Seleccione un país</option>
                                    <option v-for="pais in PAISES" :key="pais.iso" :value="pais.nombre">
                                        {{ pais.nombre }} ({{ pais.simbolo }} {{ pais.moneda }})
                                    </option>
                                </select>
                                <p class="mt-1 text-xs" style="color: var(--text-tertiary);">
                                    Esto define la moneda en la que operará esta ruta
                                </p>
                                <InputError class="mt-2" :message="clientErrors.pais_operacion || form.errors.pais_operacion" />
                            </div>

                            <!-- Moneda (Auto-completado) -->
                            <div>
                                <label for="moneda" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                                    Moneda
                                </label>
                                <input
                                    id="moneda"
                                    type="text"
                                    v-model="form.moneda"
                                    disabled
                                    class="w-full px-4 py-3 rounded-lg border opacity-60 cursor-not-allowed"
                                    style="
                                        background-color: var(--bg-primary);
                                        color: var(--text-primary);
                                        border-color: var(--border-primary);
                                    "
                                />
                                <p class="mt-1 text-xs" style="color: var(--text-tertiary);">
                                    Se asigna automáticamente según el país seleccionado
                                </p>
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
                                    <span v-if="!form.processing">Actualizar Ruta</span>
                                    <span v-else>Actualizando...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
