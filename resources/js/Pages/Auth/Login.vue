<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useHtml5Validation } from '@/composables/useHtml5Validation';

// Activar validaciones HTML5 en español
useHtml5Validation();

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar Sesión" />

        <div class="w-full max-w-md space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Bienvenido
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Ingresa tus credenciales para acceder al sistema
                </p>
            </div>

            <!-- Status Message -->
            <div
                v-if="status"
                class="rounded-lg bg-green-50 p-4 text-sm font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400"
            >
                {{ status }}
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="mt-8 space-y-6">
                <div class="space-y-5">
                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Correo Electrónico" />
                        <div class="mt-2 relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <svg
                                    class="h-5 w-5 text-muted-foreground"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                                    />
                                </svg>
                            </div>
                            <TextInput
                                id="email"
                                type="email"
                                class="pl-10 block w-full"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="tu@correo.com"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <InputLabel for="password" value="Contraseña" />
                        <div class="mt-2 relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <svg
                                    class="h-5 w-5 text-muted-foreground"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                    />
                                </svg>
                            </div>
                            <TextInput
                                id="password"
                                type="password"
                                class="pl-10 block w-full"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                        </div>
                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox
                            name="remember"
                            v-model:checked="form.remember"
                        />
                        <span class="ms-2 text-sm text-muted-foreground">
                            Recordarme
                        </span>
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-medium text-primary hover:text-primary/80 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md px-2 py-1"
                    >
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>

                <!-- Submit Button -->
                <div>
                    <PrimaryButton
                        class="w-full flex justify-center items-center gap-2 py-3 text-base font-semibold shadow-lg hover:shadow-xl transition-all duration-200"
                        :class="{
                            'opacity-50 cursor-not-allowed': form.processing,
                        }"
                        :disabled="form.processing"
                    >
                        <svg
                            v-if="form.processing"
                            class="animate-spin h-5 w-5"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        <svg
                            v-else
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                            />
                        </svg>
                        <span>{{
                            form.processing ? 'Iniciando sesión...' : 'Iniciar Sesión'
                        }}</span>
                    </PrimaryButton>
                </div>
            </form>

            <!-- Register Link -->
            <div class="text-center border-t pt-6" style="border-color: var(--border-color)">
                <p class="text-sm text-muted-foreground">
                    ¿No tienes una cuenta?
                    <Link
                        :href="route('register')"
                        class="ml-1 font-medium text-primary hover:text-primary/80 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md px-2 py-1"
                    >
                        Regístrate aquí
                    </Link>
                </p>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-xs text-muted-foreground">
                    © {{ new Date().getFullYear() }} TransComarapa. Todos los
                    derechos reservados.
                </p>
            </div>
        </div>
    </GuestLayout>
</template>
