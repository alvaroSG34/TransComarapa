<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useValidation } from '@/composables/useValidation';
import { PAISES } from '@/utils/paises.js';
import { computed, ref } from 'vue';

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
    imagen: null,
});

const imagenPreview = ref(props.ruta.imagen ? `/storage/${props.ruta.imagen}` : null);

// Auto-completar moneda cuando cambia el país
const paisSeleccionado = computed(() => {
    return PAISES.find(p => p.nombre === form.pais_operacion);
});

const actualizarMoneda = () => {
    if (paisSeleccionado.value) {
        form.moneda = paisSeleccionado.value.moneda;
    }
};

const handleImagenChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validar que sea una imagen
        if (!file.type.startsWith('image/')) {
            alert('Por favor selecciona un archivo de imagen válido');
            return;
        }
        
        // Validar tamaño (máximo 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('La imagen no debe superar 5MB');
            return;
        }
        
        form.imagen = file;
        
        // Crear preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagenPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const eliminarImagen = () => {
    form.imagen = null;
    imagenPreview.value = null;
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

    // Usar POST con _method cuando hay archivos
    form.post(route('rutas.update', props.ruta.id), {
        forceFormData: true,
        _method: 'PUT',
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
                                <div class="relative">
                                    <span 
                                        v-if="form.pais_operacion" 
                                        :class="`fi fi-${PAISES.find(p => p.nombre === form.pais_operacion)?.iso.toLowerCase()} absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none z-10`"
                                        style="width: 1.25em; height: 0.9375em;"
                                    ></span>
                                    <select
                                        id="pais_operacion"
                                        v-model="form.pais_operacion"
                                        @change="actualizarMoneda(); handleInput('pais_operacion')"
                                        class="w-full py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                        :class="[
                                            form.pais_operacion ? 'pl-11 pr-4' : 'px-4',
                                            (form.errors.pais_operacion || clientErrors.pais_operacion) ? 'border-red-500 focus:ring-red-500' : ''
                                        ]"
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
                                </div>
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

                            <!-- Imagen de la Ruta (Opcional) -->
                            <div>
                                <label for="imagen" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                                    Imagen de la Ruta (Opcional)
                                </label>
                                <div class="space-y-3">
                                    <div v-if="imagenPreview" class="relative inline-block">
                                        <img :src="imagenPreview" alt="Preview" class="h-32 w-48 object-cover rounded-lg border" style="border-color: var(--border-primary);" />
                                        <button
                                            type="button"
                                            @click="eliminarImagen"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <input
                                        id="imagen"
                                        type="file"
                                        accept="image/*"
                                        @change="handleImagenChange"
                                        class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                                        style="
                                            background-color: var(--bg-primary);
                                            color: var(--text-primary);
                                            border-color: var(--border-primary);
                                        "
                                    />
                                    <p class="mt-1 text-xs" style="color: var(--text-tertiary);">
                                        📌 Deja vacío para mantener la imagen actual{{ !imagenPreview ? ' o usar la imagen por defecto' : '' }}. Máximo 5MB.
                                    </p>
                                </div>
                                <InputError class="mt-2" :message="form.errors.imagen" />
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
