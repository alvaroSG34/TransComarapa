<script setup>
import { ref, watch, computed } from 'vue';
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption, ComboboxButton, Dialog, DialogPanel, DialogTitle } from '@headlessui/vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    modelValue: [Number, String],
    required: {
        type: Boolean,
        default: false
    },
    error: String,
    routeNameBuscar: {
        type: String,
        default: 'encomiendas.buscar-cliente'
    },
    routeNameRegistrar: {
        type: String,
        default: 'encomiendas.registrar-cliente'
    }
});

const emit = defineEmits(['update:modelValue', 'clienteSeleccionado']);

const query = ref('');
const clientes = ref([]);
const clienteSeleccionado = ref(null);
const mostrarModal = ref(false);
const buscando = ref(false);
const registrando = ref(false);

// Datos del nuevo cliente
const nuevoCliente = ref({
    nombre: '',
    apellido: '',
    ci: '',
    telefono: '',
    correo: ''
});

const erroresRegistro = ref({});

// Buscar clientes cuando el usuario escribe
watch(query, async (newQuery) => {
    if (newQuery.length < 2) {
        clientes.value = [];
        return;
    }

    buscando.value = true;
    try {
        const response = await axios.get(route(props.routeNameBuscar), {
            params: { q: newQuery }
        });
        clientes.value = response.data;
    } catch (error) {
        console.error('Error al buscar clientes:', error);
        clientes.value = [];
    } finally {
        buscando.value = false;
    }
});

// Cuando se selecciona un cliente del combobox
const onClienteChange = (cliente) => {
    if (cliente) {
        clienteSeleccionado.value = cliente;
        emit('update:modelValue', cliente.id);
        emit('clienteSeleccionado', cliente);
        query.value = `${cliente.nombre} ${cliente.apellido} - CI: ${cliente.ci}`;
    }
};

// Abrir modal de registro
const abrirModal = () => {
    mostrarModal.value = true;
    erroresRegistro.value = {};
    nuevoCliente.value = {
        nombre: '',
        apellido: '',
        ci: '',
        telefono: '',
        correo: ''
    };
};

// Registrar nuevo cliente
const registrarCliente = async () => {
    erroresRegistro.value = {};
    registrando.value = true;

    try {
        const response = await axios.post(route(props.routeNameRegistrar), nuevoCliente.value);
        
        if (response.data.success) {
            const cliente = response.data.cliente;
            
            // Agregar a la lista y seleccionar
            clientes.value.unshift(cliente);
            clienteSeleccionado.value = cliente;
            emit('update:modelValue', cliente.id);
            emit('clienteSeleccionado', cliente);
            query.value = `${cliente.nombre} ${cliente.apellido} - CI: ${cliente.ci}`;
            
            // Cerrar modal
            mostrarModal.value = false;
            
            // Notificar éxito
            alert('Cliente registrado exitosamente');
        }
    } catch (error) {
        if (error.response && error.response.data.errors) {
            erroresRegistro.value = error.response.data.errors;
        } else {
            alert('Error al registrar cliente: ' + (error.response?.data?.message || error.message));
        }
    } finally {
        registrando.value = false;
    }
};

const displayValue = computed(() => {
    return clienteSeleccionado.value 
        ? `${clienteSeleccionado.value.nombre} ${clienteSeleccionado.value.apellido} - CI: ${clienteSeleccionado.value.ci}`
        : '';
});
</script>

<template>
    <div>
        <Combobox :modelValue="clienteSeleccionado" @update:modelValue="onClienteChange">
            <div class="relative">
                <div class="relative">
                    <ComboboxInput
                        :displayValue="() => displayValue"
                        @change="query = $event.target.value"
                        :required="required"
                        placeholder="Buscar por CI, nombre o teléfono..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                        :class="{ 'border-red-500': error }"
                    />
                    <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
                        <svg class="h-5 w-5" style="color: var(--text-secondary)" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </ComboboxButton>
                </div>

                <ComboboxOptions 
                    v-if="query.length >= 2"
                    class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                    style="background-color: var(--card-bg)"
                >
                    <div v-if="buscando" class="px-4 py-2" style="color: var(--text-secondary)">
                        Buscando...
                    </div>
                    
                    <ComboboxOption
                        v-for="cliente in clientes"
                        :key="cliente.id"
                        :value="cliente"
                        v-slot="{ active, selected }"
                        class="cursor-pointer"
                    >
                        <div
                            :class="[
                                'relative cursor-pointer select-none py-2 pl-3 pr-9',
                                active ? 'bg-indigo-600 text-white' : ''
                            ]"
                            :style="!active ? `color: var(--text-primary)` : ''"
                        >
                            <div class="flex flex-col">
                                <span :class="['font-medium', selected ? 'font-semibold' : '']">
                                    {{ cliente.nombre }} {{ cliente.apellido }}
                                </span>
                                <span :class="['text-sm', active ? 'text-white' : '']" :style="!active ? `color: var(--text-secondary)` : ''">
                                    CI: {{ cliente.ci }} • Tel: {{ cliente.telefono }}
                                </span>
                            </div>
                        </div>
                    </ComboboxOption>

                    <div v-if="clientes.length === 0 && !buscando" class="px-4 py-2" style="color: var(--text-secondary)">
                        No se encontraron clientes
                    </div>
                </ComboboxOptions>
            </div>
        </Combobox>

        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>

        <!-- Botón para registrar nuevo cliente -->
        <button
            @click="abrirModal"
            type="button"
            class="mt-2 text-sm font-medium hover:underline"
            style="color: var(--button-primary-bg)"
        >
            + Registrar Nuevo Cliente
        </button>

        <!-- Modal de registro rápido -->
        <Dialog :open="mostrarModal" @close="mostrarModal = false" class="relative z-50">
            <div class="fixed inset-0 bg-black/30" aria-hidden="true" />
            
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel class="w-full max-w-md rounded-lg p-6 shadow-xl bg-white">
                    <DialogTitle class="text-lg font-semibold mb-4 text-gray-900">
                        Registrar Nuevo Cliente
                    </DialogTitle>

                    <form @submit.prevent="registrarCliente" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1 text-gray-700">
                                    Nombre *
                                </label>
                                <input
                                    v-model="nuevoCliente.nombre"
                                    type="text"
                                    required
                                    maxlength="100"
                                    class="w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-500': erroresRegistro.nombre }"
                                />
                                <p v-if="erroresRegistro.nombre" class="mt-1 text-xs text-red-600">
                                    {{ erroresRegistro.nombre[0] }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1 text-gray-700">
                                    Apellido *
                                </label>
                                <input
                                    v-model="nuevoCliente.apellido"
                                    type="text"
                                    required
                                    maxlength="100"
                                    class="w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    :class="{ 'border-red-500': erroresRegistro.apellido }"
                                />
                                <p v-if="erroresRegistro.apellido" class="mt-1 text-xs text-red-600">
                                    {{ erroresRegistro.apellido[0] }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">
                                CI *
                            </label>
                            <input
                                v-model="nuevoCliente.ci"
                                type="text"
                                required
                                maxlength="20"
                                class="w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': erroresRegistro.ci }"
                            />
                            <p v-if="erroresRegistro.ci" class="mt-1 text-xs text-red-600">
                                {{ erroresRegistro.ci[0] }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">
                                Teléfono *
                            </label>
                            <input
                                v-model="nuevoCliente.telefono"
                                type="tel"
                                required
                                maxlength="20"
                                class="w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': erroresRegistro.telefono }"
                            />
                            <p v-if="erroresRegistro.telefono" class="mt-1 text-xs text-red-600">
                                {{ erroresRegistro.telefono[0] }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 text-gray-700">
                                Correo Electrónico
                            </label>
                            <input
                                v-model="nuevoCliente.correo"
                                type="email"
                                maxlength="100"
                                class="w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': erroresRegistro.correo }"
                            />
                            <p v-if="erroresRegistro.correo" class="mt-1 text-xs text-red-600">
                                {{ erroresRegistro.correo[0] }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Opcional
                            </p>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4">
                            <button
                                type="button"
                                @click="mostrarModal = false"
                                class="px-4 py-2 rounded-md text-sm font-medium bg-gray-200 text-gray-800 hover:bg-gray-300"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="registrando"
                                class="px-4 py-2 rounded-md text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700"
                                :class="{ 'opacity-50 cursor-not-allowed': registrando }"
                            >
                                {{ registrando ? 'Guardando...' : 'Guardar y Continuar' }}
                            </button>
                        </div>
                    </form>
                </DialogPanel>
            </div>
        </Dialog>
    </div>
</template>
