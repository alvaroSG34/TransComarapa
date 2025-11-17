<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useHtml5Validation } from '@/composables/useHtml5Validation';
import { useValidation } from '@/composables/useValidation';

// Activar validaciones HTML5 en español (opcional)
// useHtml5Validation();

// Usar validaciones personalizadas
const { errors: clientErrors, validate, validateConfirmed, clearError, setErrors } = useValidation();

const form = useForm({
    nombre: '',
    apellido: '',
    ci: '',
    telefono: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    // Definir reglas de validación
    const rules = {
        nombre: ['required', 'min:2', 'max:255', 'alpha'],
        apellido: ['required', 'min:2', 'max:255', 'alpha'],
        ci: ['required', 'min:5', 'max:20', 'alpha_num'],
        telefono: ['required', 'min:7', 'max:20'],
        email: ['required', 'email', 'max:255'],
        password: ['required', 'min:8'],
        password_confirmation: ['required'],
    };

    // Validar en frontend
    if (!validate(form, rules)) {
        return; // Detener si hay errores de validación
    }

    // Validar que las contraseñas coincidan
    if (!validateConfirmed(form, 'password', 'password_confirmation')) {
        return;
    }

    // Si pasa validaciones frontend, enviar al backend
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
        onError: (errors) => {
            // Si hay errores del servidor, mostrarlos
            setErrors(errors);
        },
    });
};

// Función para limpiar error al escribir
const handleInput = (field) => {
    clearError(field);
    form.clearErrors(field);
};
</script>

<template>
    <GuestLayout>
        <Head title="Registrarse - TransComarapa" />

        <div class="w-full max-w-md mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center gap-2 mb-3">
                    <span class="text-4xl">🚌</span>
                    <h1 class="text-2xl font-bold" style="color: var(--primary-600);">TransComarapa</h1>
                </div>
                <h2 class="text-xl font-semibold" style="color: var(--text-primary);">Crear Cuenta</h2>
                <p class="text-sm mt-2" style="color: var(--text-secondary);">
                    Regístrate para comprar pasajes y enviar encomiendas
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Nombre y Apellido en una fila -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Nombre -->
                    <div>
                        <label for="nombre" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                            Nombre
                        </label>
                        <input
                            id="nombre"
                            type="text"
                            v-model="form.nombre"
                            autofocus
                            autocomplete="given-name"
                            placeholder="Juan"
                            @input="handleInput('nombre')"
                            class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                            :class="(form.errors.nombre || clientErrors.nombre) ? 'border-red-500 focus:ring-red-500' : ''"
                            style="
                                background-color: var(--bg-secondary);
                                color: var(--text-primary);
                                border-color: var(--border-primary);
                            "
                        />
                        <InputError class="mt-2" :message="clientErrors.nombre || form.errors.nombre" />
                    </div>

                    <!-- Apellido -->
                    <div>
                        <label for="apellido" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                            Apellido
                        </label>
                        <input
                            id="apellido"
                            type="text"
                            v-model="form.apellido"
                            autocomplete="family-name"
                            placeholder="Pérez"
                            @input="handleInput('apellido')"
                            class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                            :class="(form.errors.apellido || clientErrors.apellido) ? 'border-red-500 focus:ring-red-500' : ''"
                            style="
                                background-color: var(--bg-secondary);
                                color: var(--text-primary);
                                border-color: var(--border-primary);
                            "
                        />
                        <InputError class="mt-2" :message="clientErrors.apellido || form.errors.apellido" />
                    </div>
                </div>

                <!-- CI y Teléfono en una fila -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- CI -->
                    <div>
                        <label for="ci" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                            CI
                        </label>
                        <input
                            id="ci"
                            type="text"
                            v-model="form.ci"
                            placeholder="12345678"
                            @input="handleInput('ci')"
                            class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                            :class="(form.errors.ci || clientErrors.ci) ? 'border-red-500 focus:ring-red-500' : ''"
                            style="
                                background-color: var(--bg-secondary);
                                color: var(--text-primary);
                                border-color: var(--border-primary);
                            "
                        />
                        <InputError class="mt-2" :message="clientErrors.ci || form.errors.ci" />
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label for="telefono" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                            Teléfono
                        </label>
                        <input
                            id="telefono"
                            type="tel"
                            v-model="form.telefono"
                            autocomplete="tel"
                            placeholder="71234567"
                            @input="handleInput('telefono')"
                            class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                            :class="(form.errors.telefono || clientErrors.telefono) ? 'border-red-500 focus:ring-red-500' : ''"
                            style="
                                background-color: var(--bg-secondary);
                                color: var(--text-primary);
                                border-color: var(--border-primary);
                            "
                        />
                        <InputError class="mt-2" :message="clientErrors.telefono || form.errors.telefono" />
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                        Correo Electrónico
                    </label>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        autocomplete="username"
                        placeholder="correo@ejemplo.com"
                        @input="handleInput('email')"
                        class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                        :class="(form.errors.email || clientErrors.email) ? 'border-red-500 focus:ring-red-500' : ''"
                        style="
                            background-color: var(--bg-secondary);
                            color: var(--text-primary);
                            border-color: var(--border-primary);
                        "
                    />
                    <InputError class="mt-2" :message="clientErrors.email || form.errors.email" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                        Contraseña
                    </label>
                    <input
                        id="password"
                        type="password"
                        v-model="form.password"
                        autocomplete="new-password"
                        placeholder="Mínimo 8 caracteres"
                        @input="handleInput('password')"
                        class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                        :class="(form.errors.password || clientErrors.password) ? 'border-red-500 focus:ring-red-500' : ''"
                        style="
                            background-color: var(--bg-secondary);
                            color: var(--text-primary);
                            border-color: var(--border-primary);
                        "
                    />
                    <InputError class="mt-2" :message="clientErrors.password || form.errors.password" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                        Confirmar Contraseña
                    </label>
                    <input
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                        placeholder="Repite tu contraseña"
                        @input="handleInput('password_confirmation')"
                        class="w-full px-4 py-3 rounded-lg border transition-all focus:outline-none focus:ring-2"
                        :class="(form.errors.password_confirmation || clientErrors.password_confirmation) ? 'border-red-500 focus:ring-red-500' : ''"
                        style="
                            background-color: var(--bg-secondary);
                            color: var(--text-primary);
                            border-color: var(--border-primary);
                        "
                    />
                    <InputError class="mt-2" :message="clientErrors.password_confirmation || form.errors.password_confirmation" />
                </div>

  

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3 rounded-lg font-semibold text-white transition-all hover:opacity-90 disabled:opacity-50"
                    style="background-color: var(--primary-600);"
                >
                    <span v-if="!form.processing">Crear Cuenta</span>
                    <span v-else>Registrando...</span>
                </button>

                <!-- Login Link -->
                <div class="text-center">
                    <Link
                        :href="route('login')"
                        class="text-sm hover:underline"
                        style="color: var(--primary-600);"
                    >
                        ¿Ya tienes cuenta? Inicia sesión
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
