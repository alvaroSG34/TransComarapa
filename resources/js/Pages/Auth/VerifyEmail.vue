<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verificar Email - TransPorta" />

        <div class="w-full space-y-6">
            <!-- Header con Icono -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-4"
                     style="background-color: var(--primary-100); color: var(--primary-600);">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold tracking-tight" style="color: var(--text-primary);">
                    Verifica tu Email
                </h1>
                <p class="mt-3 text-base leading-relaxed" style="color: var(--text-secondary);">
                    ¡Gracias por registrarte! Antes de comenzar, por favor verifica tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar.
                </p>
            </div>

            <!-- Mensaje de Reenvío Exitoso -->
            <div
                v-if="verificationLinkSent"
                class="rounded-lg p-4 text-sm font-medium flex items-start gap-3 animate-fade-in"
                style="background-color: var(--success-50); color: var(--success-700); border: 1px solid var(--success-200);"
            >
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="font-semibold">¡Correo enviado!</p>
                    <p class="mt-1">Se ha enviado un nuevo enlace de verificación a tu correo electrónico.</p>
                </div>
            </div>

            <!-- Info Box -->
            <div class="rounded-lg p-4 flex items-start gap-3"
                 style="background-color: var(--primary-50); border: 1px solid var(--primary-200);">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: var(--primary-600);" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm" style="color: var(--text-secondary);">
                    <p class="font-medium mb-1" style="color: var(--text-primary);">¿No recibiste el correo?</p>
                    <ul class="list-disc list-inside space-y-1 ml-1">
                        <li>Revisa tu carpeta de spam o correo no deseado</li>
                        <li>Verifica que ingresaste correctamente tu email</li>
                        <li>Haz clic en "Reenviar Email" para recibir uno nuevo</li>
                    </ul>
                </div>
            </div>

            <!-- Formulario y Acciones -->
            <form @submit.prevent="submit" class="space-y-4">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    class="w-full justify-center"
                >
                    <span v-if="!form.processing" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reenviar Email de Verificación
                    </span>
                    <span v-else class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Enviando...
                    </span>
                </PrimaryButton>

                <!-- Link de Logout -->
                <div class="text-center pt-2">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm font-medium transition-colors hover:underline inline-flex items-center gap-2"
                        style="color: var(--text-secondary);"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Cerrar Sesión
                    </Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
