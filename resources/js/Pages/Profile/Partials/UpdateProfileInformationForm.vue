<script setup>
import { ref, computed, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PaisDropdown from '@/Components/PaisDropdown.vue';
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

// Auto-actualizar código telefónico cuando cambia el país
watch(() => form.pais, (nuevoPais) => {
    const pais = PAISES.find(p => p.nombre === nuevoPais);
    if (pais) {
        form.codigo_pais_telefono = pais.codigo;
    }
});

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
                    readonly
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
                    readonly
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- País -->
            <div>
                <InputLabel for="pais" value="País" />

                <div class="mt-1 flex items-center gap-3 px-3 py-2 rounded-md shadow-sm border border-gray-300 bg-gray-50">
                    <span 
                        :class="`fi fi-${PAISES.find(p => p.nombre === form.pais)?.iso?.toLowerCase()}`"
                        class="text-2xl flex-shrink-0"
                        aria-hidden="true"
                    ></span>
                    <span class="text-gray-700">
                        {{ form.pais }}
                    </span>
                </div>

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
                    readonly
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
                    <!-- Código telefónico (solo lectura, automático según país) -->
                    <div 
                        class="flex items-center gap-2 px-3 py-2 rounded-md shadow-sm border border-gray-300 w-[120px] flex-shrink-0 bg-gray-50"
                    >
                        <span 
                            :class="`fi fi-${PAISES.find(p => p.codigo === form.codigo_pais_telefono)?.iso?.toLowerCase()}`"
                            class="text-xl flex-shrink-0"
                            aria-hidden="true"
                        ></span>
                        <span class="text-xs font-medium text-gray-700">
                            {{ form.codigo_pais_telefono }}
                        </span>
                    </div>

                    <TextInput
                        id="telefono"
                        type="tel"
                        class="flex-1"
                        v-model="form.telefono"
                        readonly
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
        </form>
    </section>
</template>
