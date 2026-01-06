<script setup>
import { ref, computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { PAISES } from '@/utils/paises.js';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

// Construir name desde nombre y apellido si name no está disponible
const userName = computed(() => {
    if (user?.name) {
        return user.name;
    }
    if (user?.nombre || user?.apellido) {
        return `${user.nombre || ''} ${user.apellido || ''}`.trim();
    }
    return '';
});

// Usar correo si email no está disponible
const userEmail = computed(() => {
    return user?.email || user?.correo || '';
});

const form = useForm({
    name: userName.value,
    email: userEmail.value,
    pais: user?.pais || 'Bolivia',
    ci: user?.ci || '',
    telefono: user?.telefono || '',
    codigo_pais_telefono: user?.codigo_pais_telefono || '+591',
    avatar: null,
});

const avatarPreview = ref(user?.img_url_full || null);
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
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeAvatar = () => {
    form.avatar = null;
    avatarPreview.value = user.img_url_full || null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.transform((data) => {
        const formData = new FormData();
        formData.append('name', data.name || '');
        formData.append('email', data.email || '');
        formData.append('pais', data.pais || '');
        formData.append('ci', data.ci || '');
        formData.append('telefono', data.telefono || '');
        formData.append('codigo_pais_telefono', data.codigo_pais_telefono || '');
        if (data.avatar) {
            formData.append('avatar', data.avatar);
        }
        formData.append('_method', 'PATCH');
        return formData;
    }).post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Resetear el input de archivo después de éxito
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        }
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Información del Perfil
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Actualiza la información de tu cuenta y dirección de correo electrónico.
            </p>
        </header>

        <form
            @submit.prevent="submit"
            class="mt-6 space-y-6"
        >
            <!-- Nombre Completo -->
            <div>
                <InputLabel for="name" value="Nombre Completo" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Correo Electrónico" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- País -->
            <div>
                <InputLabel for="pais" value="País" />

                <select
                    id="pais"
                    v-model="form.pais"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                >
                    <option value="">Selecciona un país</option>
                    <option v-for="pais in PAISES" :key="pais.iso" :value="pais.nombre">
                        {{ pais.nombre }}
                    </option>
                </select>

                <InputError class="mt-2" :message="form.errors.pais" />
            </div>

            <!-- Documento de Identidad -->
            <div>
                <InputLabel for="ci" value="Documento de Identidad / Pasaporte" />

                <TextInput
                    id="ci"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.ci"
                    autocomplete="off"
                    placeholder="CI, DNI, Pasaporte, etc."
                />

                <InputError class="mt-2" :message="form.errors.ci" />
                <p class="mt-1 text-xs text-gray-500">
                    Cédula de Identidad, DNI, Pasaporte u otro documento según tu país
                </p>
            </div>

            <!-- Teléfono con Código de País -->
            <div>
                <InputLabel for="telefono" value="Teléfono" />

                <div class="mt-1 flex gap-2">
                    <select
                        v-model="form.codigo_pais_telefono"
                        class="w-32 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                        <option v-for="pais in PAISES" :key="pais.codigo" :value="pais.codigo">
                            {{ pais.codigo }} {{ pais.iso }}
                        </option>
                    </select>

                    <TextInput
                        id="telefono"
                        type="tel"
                        class="flex-1"
                        v-model="form.telefono"
                        autocomplete="tel"
                        placeholder="70123456"
                    />
                </div>

                <InputError class="mt-2" :message="form.errors.telefono" />
                <InputError class="mt-2" :message="form.errors.codigo_pais_telefono" />
            </div>

            <!-- Avatar Upload -->
            <div>
                <InputLabel for="avatar" value="Foto de Perfil" />

                <div class="mt-2 flex items-center gap-4">
                    <!-- Vista previa -->
                    <div v-if="avatarPreview" class="flex-shrink-0">
                        <img
                            :src="avatarPreview"
                            alt="Avatar preview"
                            class="h-20 w-20 rounded-full object-cover border-2 border-gray-300"
                        />
                    </div>
                    <div v-else class="flex-shrink-0">
                        <div class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center">
                            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1">
                        <input
                            ref="fileInput"
                            id="avatar"
                            type="file"
                            accept="image/jpeg,image/png,image/jpg,image/gif"
                            @change="handleFileChange"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            PNG, JPG, GIF hasta 2MB
                        </p>
                        <button
                            v-if="avatarPreview && form.avatar"
                            type="button"
                            @click="removeAvatar"
                            class="mt-2 text-sm text-red-600 hover:text-red-800"
                        >
                            Eliminar imagen seleccionada
                        </button>
                    </div>
                </div>

                <InputError class="mt-2" :message="form.errors.avatar" />
            </div>

            <!-- Verificación de Email para Clientes -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <p class="text-sm text-yellow-800">
                    Tu dirección de correo electrónico no está verificada.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-yellow-900 underline hover:text-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
                    >
                        Haz clic aquí para reenviar el correo de verificación.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Guardar</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Guardado.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
