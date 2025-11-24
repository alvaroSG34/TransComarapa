<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BuscadorCliente from '@/Components/BuscadorCliente.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import { useHtml5Validation } from '@/composables/useHtml5Validation';
import axios from 'axios';

const props = defineProps({
    rutas: Array,
    clientes: Array,
    viajes: Array,
    qr_data: Object,
    success: String
});

useHtml5Validation();

const form = useForm({
    viaje_id: '',
    ruta_id: '',
    cliente_id: '',
    peso: '',
    descripcion: '',
    nombre_destinatario: '',
    img_url: '',
    modalidad_pago: 'origen',
    metodo_pago: '',
    metodo_pago_destino: '',
    precio: '',
    monto_pagado_origen: ''
});

const mostrarModalQr = ref(false);
const qrData = ref(null);
const errorQr = ref(null);
const reintentando = ref(false);

const viajesDisponibles = computed(() => {
    if (!form.ruta_id) return [];
    return props.viajes.filter(v => v.ruta_id === parseInt(form.ruta_id));
});

const viajeSeleccionado = computed(() => {
    return props.viajes.find(v => v.id === parseInt(form.viaje_id));
});

const rutaSeleccionada = computed(() => {
    return props.rutas.find(r => r.id === parseInt(form.ruta_id));
});

const submit = () => {
    console.log('=== INICIO SUBMIT ENCOMIENDA ===');
    console.log('Datos del formulario:', form.data());
    console.log('URL:', route('encomiendas.store'));
    
    form.post(route('encomiendas.store'), {
        preserveScroll: true,
        onStart: () => {
            console.log('Enviando petición al servidor...');
        },
        onSuccess: (page) => {
            console.log('Respuesta exitosa recibida:', page);
            console.log('Props recibidas:', page.props);
            
            // Si hay datos de QR en las props, mostrar modal
            if (page.props.qr_data) {
                console.log('QR data encontrado:', page.props.qr_data);
                qrData.value = page.props.qr_data;
                mostrarModalQr.value = true;
                errorQr.value = null;
            } else {
                console.log('No hay QR data, redirigiendo a index');
            }
        },
        onError: (errors) => {
            console.error('=== ERROR EN SUBMIT ===');
            console.error('Errores recibidos:', errors);
            console.error('Errores del formulario:', form.errors);
            
            // Si hay error específico de QR, mostrar en modal
            if (errors.qr_error) {
                console.error('Error de QR:', errors.qr_error);
                errorQr.value = errors.qr_error;
                mostrarModalQr.value = true;
            }
        },
        onFinish: () => {
            console.log('Submit finalizado');
        }
    });
};

const reintentarGenerarQr = async () => {
    if (!qrData.value?.encomienda_id) return;
    
    reintentando.value = true;
    errorQr.value = null;
    
    try {
        const response = await axios.post(route('encomiendas.reintentar-qr', qrData.value.encomienda_id), {
            tipo: qrData.value.tipo || 'origen'
        });
        
        if (response.data?.qr_data || response.data?.qr_data_destino) {
            qrData.value = response.data.qr_data || response.data.qr_data_destino;
        }
        
        // Recargar página para obtener datos actualizados
        router.reload({ only: [] });
    } catch (error) {
        errorQr.value = error.response?.data?.message || 'Error al reintentar generación de QR';
    } finally {
        reintentando.value = false;
    }
};

const cerrarModal = () => {
    mostrarModalQr.value = false;
    qrData.value = null;
    errorQr.value = null;
    // Redirigir a index después de cerrar
    router.visit(route('encomiendas.index'));
};

// Verificar si hay datos de QR en las props al cargar
onMounted(() => {
    // Si hay datos de QR en las props, mostrar modal
    if (props.qr_data) {
        qrData.value = props.qr_data;
        mostrarModalQr.value = true;
        errorQr.value = null;
    }
});
</script>

<template>
    <Head title="Registrar Encomienda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                Registrar Nueva Encomienda
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Selección de Ruta -->
                            <div>
                                <label for="ruta_id" class="block text-sm font-medium mb-2">
                                    Seleccionar Ruta de Envío *
                                </label>
                                <select
                                    id="ruta_id"
                                    v-model="form.ruta_id"
                                    required
                                    @change="form.viaje_id = ''"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.ruta_id }"
                                >
                                    <option value="">Seleccione una ruta</option>
                                    <option v-for="ruta in rutas" :key="ruta.id" :value="ruta.id">
                                        {{ ruta.nombre }} ({{ ruta.origen }} → {{ ruta.destino }})
                                    </option>
                                </select>
                                <p v-if="form.errors.ruta_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.ruta_id }}
                                </p>
                            </div>

                            <!-- Selección de Viaje -->
                            <div v-if="form.ruta_id">
                                <label for="viaje_id" class="block text-sm font-medium mb-2">
                                    Seleccionar Viaje *
                                </label>
                                <select
                                    id="viaje_id"
                                    v-model="form.viaje_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.viaje_id }"
                                >
                                    <option value="">Seleccione un viaje</option>
                                    <option v-for="viaje in viajesDisponibles" :key="viaje.id" :value="viaje.id">
                                        {{ new Date(viaje.fecha_salida).toLocaleString('es-BO') }} - 
                                        {{ viaje.marca }} {{ viaje.modelo }} ({{ viaje.placa }}) - 
                                        Estado: {{ viaje.estado }}
                                    </option>
                                </select>
                                <p v-if="form.errors.viaje_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.viaje_id }}
                                </p>
                                <p v-else-if="viajesDisponibles.length === 0" class="mt-1 text-sm text-yellow-600">
                                    No hay viajes disponibles para esta ruta
                                </p>
                            </div>

                            <!-- Información de Viaje Seleccionado -->
                            <div v-if="viajeSeleccionado" class="p-4 rounded-lg" style="background-color: var(--header-bg); border: 1px solid var(--border-color)">
                                <h3 class="text-md font-semibold mb-3" style="color: var(--text-primary)">
                                    Detalles del Viaje
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span style="color: var(--text-secondary)">Ruta:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ viajeSeleccionado.ruta_nombre }}
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary)">Fecha Salida:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ new Date(viajeSeleccionado.fecha_salida).toLocaleString('es-BO') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary)">Vehículo:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ viajeSeleccionado.marca }} {{ viajeSeleccionado.modelo }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Selección de Cliente (Remitente) con Buscador -->
                            <div>
                                <label for="cliente_id" class="block text-sm font-medium mb-2">
                                    Cliente Remitente *
                                </label>
                                <BuscadorCliente
                                    v-model="form.cliente_id"
                                    :required="true"
                                    :error="form.errors.cliente_id"
                                    endpoint="/encomiendas-buscar-cliente"
                                    registro-endpoint="/encomiendas-registrar-cliente"
                                />
                                <p v-if="!form.errors.cliente_id" class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Persona que envía el paquete. Puede buscar o registrar nuevo cliente.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Peso del Paquete -->
                                <div>
                                    <label for="peso" class="block text-sm font-medium mb-2">
                                        Peso del Paquete (kg) *
                                    </label>
                                    <input
                                        id="peso"
                                        type="number"
                                        v-model="form.peso"
                                        required
                                        min="0.01"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                        :class="{ 'border-red-500': form.errors.peso }"
                                    />
                                    <p v-if="form.errors.peso" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.peso }}
                                    </p>
                                    <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                        Peso en kilogramos (mínimo 0.01 kg)
                                    </p>
                                </div>

                                <!-- Precio -->
                                <div>
                                    <label for="precio" class="block text-sm font-medium mb-2">
                                        Precio del Envío (Bs) *
                                    </label>
                                    <input
                                        id="precio"
                                        type="number"
                                        v-model="form.precio"
                                        required
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                        :class="{ 'border-red-500': form.errors.precio }"
                                    />
                                    <p v-if="form.errors.precio" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.precio }}
                                    </p>
                                    <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                        Costo total del envío en bolivianos
                                    </p>
                                </div>
                            </div>

                            <!-- Nombre del Destinatario -->
                            <div>
                                <label for="nombre_destinatario" class="block text-sm font-medium mb-2">
                                    Nombre del Destinatario *
                                </label>
                                <input
                                    id="nombre_destinatario"
                                    type="text"
                                    v-model="form.nombre_destinatario"
                                    required
                                    maxlength="150"
                                    placeholder="Nombre completo de quien recibe"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.nombre_destinatario }"
                                />
                                <p v-if="form.errors.nombre_destinatario" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.nombre_destinatario }}
                                </p>
                            </div>

                            <!-- Descripción del Paquete -->
                            <div>
                                <label for="descripcion" class="block text-sm font-medium mb-2">
                                    Descripción del Contenido
                                </label>
                                <textarea
                                    id="descripcion"
                                    v-model="form.descripcion"
                                    maxlength="500"
                                    rows="3"
                                    placeholder="Describa brevemente el contenido del paquete (opcional)"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.descripcion }"
                                ></textarea>
                                <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.descripcion }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Opcional - Máximo 500 caracteres
                                </p>
                            </div>

                            <!-- Modalidad de Pago -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Modalidad de Pago *
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <label class="flex items-center p-4 rounded-lg border-2 cursor-pointer transition-colors"
                                        :class="form.modalidad_pago === 'origen' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
                                        style="border-color: var(--border-color)">
                                        <input
                                            type="radio"
                                            v-model="form.modalidad_pago"
                                            value="origen"
                                            class="mr-3"
                                            @change="form.metodo_pago_destino = ''"
                                        />
                                        <div>
                                            <div class="font-medium" style="color: var(--text-primary)">Pago en Origen</div>
                                            <div class="text-xs" style="color: var(--text-secondary)">El remitente paga todo</div>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-4 rounded-lg border-2 cursor-pointer transition-colors"
                                        :class="form.modalidad_pago === 'mixto' ? 'border-purple-500 bg-purple-50' : 'border-gray-300'"
                                        style="border-color: var(--border-color)">
                                        <input
                                            type="radio"
                                            v-model="form.modalidad_pago"
                                            value="mixto"
                                            class="mr-3"
                                        />
                                        <div>
                                            <div class="font-medium" style="color: var(--text-primary)">Pago Mixto</div>
                                            <div class="text-xs" style="color: var(--text-secondary)">Dividido entre ambos</div>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-4 rounded-lg border-2 cursor-pointer transition-colors"
                                        :class="form.modalidad_pago === 'destino' ? 'border-orange-500 bg-orange-50' : 'border-gray-300'"
                                        style="border-color: var(--border-color)">
                                        <input
                                            type="radio"
                                            v-model="form.modalidad_pago"
                                            value="destino"
                                            class="mr-3"
                                            @change="form.metodo_pago = ''"
                                        />
                                        <div>
                                            <div class="font-medium" style="color: var(--text-primary)">Pago en Destino</div>
                                            <div class="text-xs" style="color: var(--text-secondary)">El destinatario paga</div>
                                        </div>
                                    </label>
                                </div>
                                <p v-if="form.errors.modalidad_pago" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.modalidad_pago }}
                                </p>
                            </div>

                            <!-- Método de Pago en Origen (solo para origen o mixto) -->
                            <div v-if="form.modalidad_pago === 'origen' || form.modalidad_pago === 'mixto'">
                                <label for="metodo_pago" class="block text-sm font-medium mb-2">
                                    Método de Pago en Origen *
                                </label>
                                <select
                                    id="metodo_pago"
                                    v-model="form.metodo_pago"
                                    :required="form.modalidad_pago === 'origen' || form.modalidad_pago === 'mixto'"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.metodo_pago }"
                                >
                                    <option value="">Seleccione método de pago</option>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="QR">QR (PagoFácil)</option>
                                </select>
                                <p v-if="form.errors.metodo_pago" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.metodo_pago }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Cómo se pagará la parte de origen
                                </p>
                            </div>


                            <!-- Monto Pagado en Origen (solo para modalidad mixto) -->
                            <div v-if="form.modalidad_pago === 'mixto'">
                                <label for="monto_pagado_origen" class="block text-sm font-medium mb-2">
                                    Monto Pagado en Origen (Bs) *
                                </label>
                                <input
                                    id="monto_pagado_origen"
                                    type="number"
                                    v-model="form.monto_pagado_origen"
                                    :required="form.modalidad_pago === 'mixto'"
                                    min="0"
                                    :max="form.precio"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.monto_pagado_origen }"
                                />
                                <p v-if="form.errors.monto_pagado_origen" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.monto_pagado_origen }}
                                </p>
                                <p v-else-if="form.precio && form.monto_pagado_origen" class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Pendiente en destino: Bs {{ (parseFloat(form.precio) - parseFloat(form.monto_pagado_origen)).toFixed(2) }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Cantidad que paga el remitente ahora
                                </p>
                            </div>

                            <!-- URL de Imagen del Paquete -->
                            <div>
                                <label for="img_url" class="block text-sm font-medium mb-2">
                                    URL de Foto del Paquete
                                </label>
                                <input
                                    id="img_url"
                                    type="url"
                                    v-model="form.img_url"
                                    maxlength="255"
                                    placeholder="https://ejemplo.com/foto-paquete.jpg"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.img_url }"
                                />
                                <p v-if="form.errors.img_url" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.img_url }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Opcional - URL de la foto del paquete para registro
                                </p>
                                
                                <!-- Vista previa de imagen -->
                                <div v-if="form.img_url" class="mt-3">
                                    <p class="text-sm font-medium mb-2">Vista previa:</p>
                                    <img :src="form.img_url" alt="Vista previa del paquete" class="h-48 w-auto object-cover rounded border" style="border-color: var(--border-color)" @error="$event.target.style.display='none'">
                                </div>
                            </div>

                            <!-- Resumen -->
                            <div v-if="form.ruta_id && form.cliente_id && form.precio" class="p-4 rounded-lg border-2" style="background-color: var(--card-bg); border-color: var(--button-primary-bg)">
                                <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">
                                    Resumen del Registro
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span style="color: var(--text-secondary)">Ruta:</span>
                                        <span class="font-medium" style="color: var(--text-primary)">
                                            {{ rutaSeleccionada?.nombre }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span style="color: var(--text-secondary)">Peso:</span>
                                        <span class="font-medium" style="color: var(--text-primary)">
                                            {{ form.peso ? parseFloat(form.peso).toFixed(2) : '0.00' }} kg
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span style="color: var(--text-secondary)">Modalidad de Pago:</span>
                                        <span class="font-medium capitalize" style="color: var(--text-primary)">
                                            {{ form.modalidad_pago }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t" style="border-color: var(--border-color)">
                                        <span class="font-semibold" style="color: var(--text-primary)">Total:</span>
                                        <span class="font-bold text-lg text-green-600">
                                            Bs {{ form.precio ? parseFloat(form.precio).toFixed(2) : '0.00' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex items-center justify-end space-x-4 pt-4 border-t" style="border-color: var(--border-color)">
                                <a
                                    :href="route('encomiendas.index')"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                                >
                                    Cancelar
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                >
                                    {{ form.processing ? 'Procesando...' : 'Registrar Encomienda' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para mostrar QR -->
        <div v-if="mostrarModalQr" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="cerrarModal">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4" style="background-color: var(--card-bg)">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold" style="color: var(--text-primary)">
                            {{ errorQr ? 'Error al Generar QR' : 'Código QR de Pago' }}
                        </h3>
                        <button
                            @click="cerrarModal"
                            class="text-gray-400 hover:text-gray-600"
                            style="color: var(--text-secondary)"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Si hay QR -->
                    <div v-if="qrData && qrData.qr_base64" class="text-center">
                        <div class="mb-4">
                            <img 
                                :src="`data:image/png;base64,${qrData.qr_base64}`" 
                                alt="Código QR de Pago"
                                class="mx-auto border-2 rounded-lg"
                                style="border-color: var(--border-color); max-width: 300px;"
                            />
                        </div>
                        <p class="text-sm mb-4" style="color: var(--text-secondary)">
                            El cliente debe escanear este código QR para realizar el pago.
                        </p>
                        <p class="text-xs mb-4" style="color: var(--text-secondary)">
                            ID de Transacción: {{ qrData.transaction_id || 'N/A' }}
                        </p>
                        <p class="text-sm font-medium mb-2" style="color: var(--text-primary)">
                            Monto: Bs {{ parseFloat(qrData.monto_total).toFixed(2) }}
                        </p>
                        <div class="flex gap-2 justify-center">
                            <button
                                @click="cerrarModal"
                                class="px-4 py-2 rounded-md text-sm font-medium"
                                style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>

                    <!-- Si hay error -->
                    <div v-else-if="errorQr" class="text-center">
                        <div class="mb-4">
                            <svg class="w-16 h-16 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-red-600 mb-4">{{ errorQr }}</p>
                        <div class="flex gap-2 justify-center">
                            <button
                                @click="reintentarGenerarQr"
                                :disabled="reintentando"
                                class="px-4 py-2 rounded-md text-sm font-medium"
                                style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                :class="{ 'opacity-50 cursor-not-allowed': reintentando }"
                            >
                                {{ reintentando ? 'Reintentando...' : 'Reintentar' }}
                            </button>
                            <button
                                @click="cerrarModal"
                                class="px-4 py-2 rounded-md text-sm font-medium"
                                style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                            >
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
