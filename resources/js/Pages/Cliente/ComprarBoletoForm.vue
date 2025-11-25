<script setup>
import ClienteLayout from '@/Layouts/ClienteLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed,  onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    viaje: Object,
    ruta: Object
});

const form = useForm({
    viaje_id: props.viaje?.id || '',
    asiento: '',
    metodo_pago: 'QR' // Solo QR para clientes
});

const asientosOcupados = ref([]);
const mostrarModalQr = ref(false);
const qrData = ref(null);
const errorQr = ref(null);
const reintentando = ref(false);

// Cuando se carga el componente, obtener asientos ocupados
onMounted(async () => {
    if (props.viaje?.id) {
        try {
            const response = await axios.get(route('cliente.boletos.asientos-ocupados', props.viaje.id));
            asientosOcupados.value = response.data.map(a => parseInt(a));
        } catch (error) {
            console.error('Error al cargar asientos ocupados:', error);
            asientosOcupados.value = [];
        }
    }
});

const asientosDisponiblesArray = computed(() => {
    if (!props.viaje) return [];
    
    const total = props.viaje.asientos_totales;
    const asientos = [];
    
    for (let i = 1; i <= total; i++) {
        asientos.push({
            numero: i,
            ocupado: asientosOcupados.value.includes(i)
        });
    }
    
    return asientos;
});

const submit = () => {
    form.post(route('cliente.boletos.procesar-compra'), {
        preserveScroll: true,
        onSuccess: (page) => {
            // Si hay datos de QR en las props, mostrar modal
            if (page.props.qr_data) {
                qrData.value = page.props.qr_data;
                mostrarModalQr.value = true;
                errorQr.value = null;
            } else {
                // Si no hay QR, redirigir al historial
                router.visit(route('cliente.historial'));
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
    router.visit(route('cliente.historial'));
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
</script>

<template>
    <Head title="Completar Compra" />

    <ClienteLayout>
        <template #header>
            <div>
                <Link 
                    :href="route('cliente.boletos.viajes', ruta?.id)" 
                    class="inline-flex items-center text-sm mb-4 hover:underline"
                    style="color: var(--text-secondary)"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a viajes
                </Link>
                <h1 class="text-3xl font-bold mb-2" style="color: var(--text-primary)">
                    Completar tu Compra 🎫
                </h1>
                <p class="text-lg" style="color: var(--text-secondary)">
                    Selecciona tu asiento y método de pago
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Información del Viaje -->
            <div class="p-6 rounded-2xl shadow-lg" style="background-color: var(--card-bg); border: 1px solid var(--border-color)">
                <h2 class="text-xl font-bold mb-4" style="color: var(--text-primary)">
                    Detalles del Viaje
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span style="color: var(--text-secondary)">Ruta:</span>
                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                            {{ ruta?.nombre }}
                        </span>
                    </div>
                    <div>
                        <span style="color: var(--text-secondary)">Origen → Destino:</span>
                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                            {{ ruta?.origen }} → {{ ruta?.destino }}
                        </span>
                    </div>
                    <div>
                        <span style="color: var(--text-secondary)">Fecha y Hora de Salida:</span>
                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                            {{ formatearFecha(viaje?.fecha_salida) }}
                        </span>
                    </div>
                    <div>
                        <span style="color: var(--text-secondary)">Precio:</span>
                        <span class="ml-2 font-bold text-lg text-green-600">
                            Bs {{ parseFloat(viaje?.precio).toFixed(2) }}
                        </span>
                    </div>
                    <div>
                        <span style="color: var(--text-secondary)">Asientos Disponibles:</span>
                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                            {{ viaje?.asientos_disponibles }} / {{ viaje?.asientos_totales }}
                        </span>
                    </div>
                    <div>
                        <span style="color: var(--text-secondary)">Vehículo:</span>
                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                            {{ viaje?.marca }} {{ viaje?.modelo }} - {{ viaje?.placa }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Formulario de Compra -->
            <div class="p-6 rounded-2xl shadow-lg" style="background-color: var(--card-bg); border: 1px solid var(--border-color)">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Selección de Asiento -->
                    <div>
                        <label class="block text-sm font-medium mb-3" style="color: var(--text-primary)">
                            Selecciona tu Asiento *
                        </label>
                        
                        <!-- Mapa de Asientos -->
                        <div class="p-4 rounded-lg mb-4" style="background-color: var(--header-bg); border: 1px solid var(--border-color)">
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
                        
                        <p v-if="form.errors.asiento" class="mt-1 text-sm text-red-600">
                            {{ form.errors.asiento }}
                        </p>
                        <p v-else class="text-sm" style="color: var(--text-secondary)">
                            Selecciona un asiento disponible del mapa
                        </p>
                    </div>

                    <!-- Método de Pago (Solo QR para clientes) -->
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--text-primary)">
                            Método de Pago
                        </label>
                        <div class="p-4 rounded-lg" style="background-color: var(--header-bg); border: 1px solid var(--border-color)">
                            <div class="flex items-center gap-3">
                                <div class="text-3xl">📱</div>
                                <div>
                                    <div class="font-semibold" style="color: var(--text-primary)">
                                        Pago con QR (PagoFácil)
                                    </div>
                                    <div class="text-sm" style="color: var(--text-secondary)">
                                        Escanea el código QR que se generará para realizar el pago de forma segura
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" v-model="form.metodo_pago" value="QR" />
                        <p v-if="form.errors.metodo_pago" class="mt-1 text-sm text-red-600">
                            {{ form.errors.metodo_pago }}
                        </p>
                    </div>

                    <!-- Resumen de la Compra -->
                    <div v-if="form.asiento" class="p-4 rounded-lg border-2" style="background-color: var(--card-bg); border-color: var(--button-primary-bg)">
                        <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">
                            Resumen de tu Compra
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span style="color: var(--text-secondary)">Ruta:</span>
                                <span class="font-medium" style="color: var(--text-primary)">
                                    {{ ruta?.nombre }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span style="color: var(--text-secondary)">Asiento:</span>
                                <span class="font-medium" style="color: var(--text-primary)">
                                    # {{ form.asiento }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span style="color: var(--text-secondary)">Método de Pago:</span>
                                <span class="font-medium" style="color: var(--text-primary)">
                                    QR (PagoFácil)
                                </span>
                            </div>
                            <div class="flex justify-between pt-2 border-t" style="border-color: var(--border-color)">
                                <span class="font-semibold" style="color: var(--text-primary)">Total a Pagar:</span>
                                <span class="font-bold text-lg text-green-600">
                                    Bs {{ parseFloat(viaje?.precio).toFixed(2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end space-x-4 pt-4 border-t" style="border-color: var(--border-color)">
                        <Link
                            :href="route('cliente.boletos.viajes', ruta?.id)"
                            class="px-4 py-2 rounded-md text-sm font-medium"
                            style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.asiento"
                            class="px-6 py-2 rounded-md text-sm font-medium text-white transition-all duration-200 hover:scale-105"
                            style="background: linear-gradient(135deg, var(--primary-600), var(--primary-500));"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing || !form.asiento }"
                        >
                            {{ form.processing ? 'Procesando...' : 'Confirmar Compra' }}
                        </button>
                    </div>
                </form>
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
                            Escanea este código QR para realizar el pago.
                        </p>
                        <p class="text-xs mb-4" style="color: var(--text-secondary)">
                            ID de Transacción: {{ qrData.transaction_id || 'N/A' }}
                        </p>
                        <div class="flex gap-2 justify-center">
                            <button
                                @click="descargarQr"
                                class="px-4 py-2 rounded-md text-sm font-medium text-white transition-all duration-200 hover:scale-105"
                                style="background: linear-gradient(135deg, var(--accent-600), var(--accent-500));"
                            >
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Descargar QR
                            </button>
                            <button
                                @click="cerrarModal"
                                class="px-4 py-2 rounded-md text-sm font-medium text-white"
                                style="background: linear-gradient(135deg, var(--primary-600), var(--primary-500));"
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
                                class="px-4 py-2 rounded-md text-sm font-medium text-white"
                                style="background: linear-gradient(135deg, var(--primary-600), var(--primary-500));"
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
    </ClienteLayout>
</template>

