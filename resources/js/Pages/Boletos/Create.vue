<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BuscadorCliente from '@/Components/BuscadorCliente.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { useHtml5Validation } from '@/composables/useHtml5Validation';
import axios from 'axios';

const props = defineProps({
    viajes: Array,
    clientes: Array,
    qr_data: Object,
    success: String
});

useHtml5Validation();

const form = useForm({
    viaje_id: '',
    cliente_id: '',
    asiento: '',
    metodo_pago: ''
});

const mostrarModalQr = ref(false);
const qrData = ref(null);
const errorQr = ref(null);
const reintentando = ref(false);

const viajeSeleccionado = computed(() => {
    return props.viajes.find(v => v.id === parseInt(form.viaje_id));
});

const asientosOcupados = ref([]);

// Cuando se selecciona un viaje, obtener asientos ocupados
watch(() => form.viaje_id, async (viajeId) => {
    if (viajeId) {
        try {
            const response = await axios.get(route('boletos.asientos-ocupados', viajeId));
            asientosOcupados.value = response.data.map(a => parseInt(a));
        } catch (error) {
            console.error('Error al cargar asientos ocupados:', error);
            asientosOcupados.value = [];
        }
        form.asiento = '';
    } else {
        asientosOcupados.value = [];
        form.asiento = '';
    }
});

const submit = () => {
    form.post(route('boletos.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Si hay datos de QR en las props, mostrar modal
            if (page.props.qr_data) {
                qrData.value = page.props.qr_data;
                mostrarModalQr.value = true;
                errorQr.value = null;
            }
        },
        onError: (errors) => {
            // Si hay error específico de QR, mostrar en modal
            if (errors.qr_error) {
                errorQr.value = errors.qr_error;
                mostrarModalQr.value = true;
            }
        }
    });
};

const reintentarGenerarQr = async () => {
    if (!qrData.value?.boleto_id) return;
    
    reintentando.value = true;
    errorQr.value = null;
    
    try {
        const response = await axios.post(route('boletos.reintentar-qr', qrData.value.boleto_id));
        
        if (response.data?.qr_data) {
            qrData.value = response.data.qr_data;
        } else if (response.data?.flash?.qr_data) {
            qrData.value = response.data.flash.qr_data;
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
    router.visit(route('boletos.index'));
};

const descargarQr = () => {
    if (!qrData.value?.qr_base64) return;
    
    try {
        // Convertir base64 a blob
        const byteCharacters = atob(qrData.value.qr_base64);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: 'image/png' });
        
        // Crear enlace de descarga
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `qr-pago-${qrData.value.transaction_id || Date.now()}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error al descargar QR:', error);
        alert('Error al descargar el código QR');
    }
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

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleString('es-BO', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const asientosDisponiblesArray = computed(() => {
    if (!viajeSeleccionado.value) return [];
    
    const total = viajeSeleccionado.value.asientos_totales;
    const asientos = [];
    
    for (let i = 1; i <= total; i++) {
        asientos.push({
            numero: i,
            ocupado: asientosOcupados.value.includes(i)
        });
    }
    
    return asientos;
});
</script>

<template>
    <Head title="Vender Boleto" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                Vender Boleto
            </h2>
        </template>

        <div class="py-12">
            <!-- Mensaje de error general -->
            <div v-if="form.errors.asiento && form.errors.asiento.includes('ocupado')" 
                 class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-medium">
                                {{ form.errors.asiento }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Selección de Viaje -->
                            <div>
                                <label for="viaje_id" class="block text-sm font-medium mb-2">
                                    Seleccionar Viaje Disponible *
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
                                    <option v-for="viaje in viajes" :key="viaje.id" :value="viaje.id">
                                        {{ viaje.ruta_nombre }} ({{ viaje.origen }} → {{ viaje.destino }}) - 
                                        {{ formatearFecha(viaje.fecha_salida) }} - 
                                        Bs {{ parseFloat(viaje.precio).toFixed(2) }} - 
                                        {{ viaje.asientos_disponibles }} asientos disponibles
                                    </option>
                                </select>
                                <p v-if="form.errors.viaje_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.viaje_id }}
                                </p>
                                <p v-else-if="!viajes || viajes.length === 0" class="mt-1 text-sm text-red-600">
                                    No hay viajes disponibles. Asegúrese de crear viajes con estado "programado" y con asientos disponibles.
                                </p>
                            </div>

                            <!-- Información del Viaje Seleccionado -->
                            <div v-if="viajeSeleccionado" class="p-4 rounded-lg" style="background-color: var(--header-bg); border: 1px solid var(--border-color)">
                                <h3 class="text-md font-semibold mb-3" style="color: var(--text-primary)">
                                    Detalles del Viaje
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span style="color: var(--text-secondary)">Ruta:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ viajeSeleccionado.ruta_nombre }}
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary)">Vehículo:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ viajeSeleccionado.vehiculo_placa }}
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary)">Precio:</span>
                                        <span class="ml-2 font-medium text-green-600">
                                            Bs {{ parseFloat(viajeSeleccionado.precio).toFixed(2) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary)">Asientos Disponibles:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ viajeSeleccionado.asientos_disponibles }} / {{ viajeSeleccionado.asientos_totales }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Selección de Cliente con Buscador -->
                            <div>
                                <label for="cliente_id" class="block text-sm font-medium mb-2">
                                    Seleccionar Cliente *
                                </label>
                                <BuscadorCliente
                                    v-model="form.cliente_id"
                                    :required="true"
                                    :error="form.errors.cliente_id"
                                    route-name-buscar="boletos.buscar-cliente"
                                    route-name-registrar="boletos.registrar-cliente"
                                />
                                <p v-if="!form.errors.cliente_id" class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Cliente que comprará el boleto. Puede buscar o registrar nuevo cliente.
                                </p>
                            </div>

                            <!-- Selección de Método de Pago -->
                            <div>
                                <label for="metodo_pago" class="block text-sm font-medium mb-2">
                                    Método de Pago *
                                </label>
                                <select
                                    id="metodo_pago"
                                    v-model="form.metodo_pago"
                                    required
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
                                    Seleccione cómo el cliente realizará el pago
                                </p>
                            </div>

                            <!-- Selección de Asiento -->
                            <div>
                                <label for="asiento" class="block text-sm font-medium mb-2">
                                    Número de Asiento *
                                </label>
                                <input
                                    id="asiento"
                                    type="number"
                                    v-model="form.asiento"
                                    required
                                    min="1"
                                    :max="viajeSeleccionado?.asientos_totales || 100"
                                    :disabled="!viajeSeleccionado"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.asiento, 'opacity-50 cursor-not-allowed': !viajeSeleccionado }"
                                />
                                <p v-if="form.errors.asiento" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.asiento }}
                                </p>
                                <p v-else-if="viajeSeleccionado" class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Ingrese un número de asiento del 1 al {{ viajeSeleccionado.asientos_totales }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Primero seleccione un viaje
                                </p>
                            </div>

                            <!-- Mapa de Asientos (Visual) -->
                            <div v-if="viajeSeleccionado" class="p-4 rounded-lg" style="background-color: var(--header-bg); border: 1px solid var(--border-color)">
                                <h3 class="text-md font-semibold mb-3" style="color: var(--text-primary)">
                                    Mapa de Asientos
                                </h3>
                                <div class="grid grid-cols-5 sm:grid-cols-10 gap-2">
                                    <button
                                        v-for="asiento in asientosDisponiblesArray"
                                        :key="asiento.numero"
                                        type="button"
                                        @click="form.asiento = asiento.numero"
                                        :disabled="asiento.ocupado"
                                        class="p-3 rounded text-sm font-medium transition-colors"
                                        :class="{
                                            'bg-green-100 text-green-800 hover:bg-green-200': !asiento.ocupado && form.asiento != asiento.numero,
                                            'bg-blue-600 text-white': form.asiento == asiento.numero,
                                            'bg-gray-300 text-gray-500 cursor-not-allowed': asiento.ocupado
                                        }"
                                    >
                                        {{ asiento.numero }}
                                    </button>
                                </div>
                                <div class="flex items-center gap-6 mt-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-green-100 rounded"></div>
                                        <span style="color: var(--text-secondary)">Disponible</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-blue-600 rounded"></div>
                                        <span style="color: var(--text-secondary)">Seleccionado</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-gray-300 rounded"></div>
                                        <span style="color: var(--text-secondary)">Ocupado</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen de la Venta -->
                            <div v-if="viajeSeleccionado && form.cliente_id && form.asiento" class="p-4 rounded-lg border-2" style="background-color: var(--card-bg); border-color: var(--button-primary-bg)">
                                <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">
                                    Resumen de la Venta
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span style="color: var(--text-secondary)">Viaje:</span>
                                        <span class="font-medium" style="color: var(--text-primary)">
                                            {{ viajeSeleccionado.ruta_nombre }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span style="color: var(--text-secondary)">Asiento:</span>
                                        <span class="font-medium" style="color: var(--text-primary)">
                                            # {{ form.asiento }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t" style="border-color: var(--border-color)">
                                        <span class="font-semibold" style="color: var(--text-primary)">Total a Pagar:</span>
                                        <span class="font-bold text-lg text-green-600">
                                            Bs {{ parseFloat(viajeSeleccionado.precio).toFixed(2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex items-center justify-end space-x-4 pt-4 border-t" style="border-color: var(--border-color)">
                                <a
                                    :href="route('boletos.index')"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                                >
                                    Cancelar
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing || !viajes || viajes.length === 0"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                    :class="{ 'opacity-50 cursor-not-allowed': form.processing || !viajes || viajes.length === 0 }"
                                >
                                    {{ form.processing ? 'Procesando...' : 'Confirmar Venta' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para mostrar QR -->
        <div v-if="mostrarModalQr" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4" style="background-color: var(--card-bg)">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold" style="color: var(--text-primary)">
                            {{ qrData ? 'Código QR de Pago' : 'Error al Generar QR' }}
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
                        <div class="flex gap-2 justify-center">
                            <button
                                @click="descargarQr"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 hover:scale-105"
                                style="background-color: var(--accent-600); color: white"
                            >
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Descargar QR
                            </button>
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
