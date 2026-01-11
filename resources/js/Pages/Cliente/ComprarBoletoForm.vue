<script setup>
import ClienteLayout from '@/Layouts/ClienteLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import { loadStripe } from '@stripe/stripe-js';

const props = defineProps({
    auth: Object,
    viaje: Object,
    ruta: Object,
    qr_data: Object,
    stripe_data: Object,
    stripe_key: String,
});

const form = useForm({
    viaje_id: props.viaje?.id || '',
    asiento: '',
    metodo_pago: '',
});

const asientosOcupados = ref([]);
const mostrarModalQr = ref(false);
const qrData = ref(null);
const errorQr = ref(null);
const reintentando = ref(false);

// Stripe
const stripe = ref(null);
const elements = ref(null);
const cardElement = ref(null);
const procesandoStripe = ref(false);
const errorStripe = ref(null);
const mostrarFormularioStripe = ref(false);
const clientSecret = ref(null);
const estadoDebug = ref('Inicializando...');

// Precio y moneda del viaje (configurada por el administrador)
const montoBob = computed(() => parseFloat(props.viaje?.precio) || 0);
const monedaViaje = computed(() => props.viaje?.moneda || 'BOB');

const simboloMoneda = computed(() => {
    const simbolos = {
        'BOB': 'Bss', 'USD': '$', 'EUR': '€', 'ARS': '$', 'AUD': '$', 'BRL': 'R$',
        'CAD': '$', 'CLP': '$', 'CNY': '¥', 'COP': '$', 'CRC': '₡', 'DKK': 'kr',
        'GBP': '£', 'GTQ': 'Q', 'HNL': 'L', 'INR': '₹', 'JPY': '¥', 'KRW': '₩',
        'MXN': '$', 'NIO': 'C$', 'NOK': 'kr', 'PEN': 'S/', 'PYG': '₲', 'RON': 'lei',
        'RUB': '₽', 'SEK': 'kr', 'CHF': 'Fr', 'UYU': '$', 'DOP': '$',
    };
    return simbolos[monedaViaje.value] || '$';
});



// Inicializar Stripe
onMounted(async () => {
    console.log('🔵 [Stripe Debug] Componente montado');
    console.log('🔵 [Stripe Debug] Props:', { viaje: props.viaje, stripe_key: props.stripe_key, stripe_data: props.stripe_data });
    console.log('🔵 [Moneda Debug] viaje.moneda:', props.viaje?.moneda);
    console.log('🔵 [Moneda Debug] viaje.precio:', props.viaje?.precio);
    console.log('🔵 [Moneda Debug] monedaViaje computed:', monedaViaje.value);
    console.log('🔵 [Moneda Debug] simboloMoneda computed:', simboloMoneda.value);
    
    if (props.viaje?.id) {
        try {
            const response = await axios.get(route('cliente.boletos.asientos-ocupados', props.viaje.id));
            asientosOcupados.value = response.data.map(a => parseInt(a));
        } catch (error) {
            console.error('Error al cargar asientos ocupados:', error);
            asientosOcupados.value = [];
        }
    }

    // Inicializar Stripe si hay stripe_key
    if (props.stripe_key) {
        try {
            console.log('🔵 [Stripe Debug] Cargando Stripe con key:', props.stripe_key.substring(0, 20) + '...');
            stripe.value = await loadStripe(props.stripe_key);
            console.log('✅ [Stripe Debug] Stripe cargado exitosamente');
            estadoDebug.value = 'Stripe inicializado';
        } catch (error) {
            console.error('❌ [Stripe Debug] Error al cargar Stripe:', error);
            estadoDebug.value = 'Error al cargar Stripe: ' + error.message;
        }
    } else {
        console.warn('⚠️ [Stripe Debug] No hay stripe_key en props');
        estadoDebug.value = 'Falta stripe_key';
    }

    // Si hay datos de QR desde el backend, mostrar modal
    if (props.qr_data) {
        qrData.value = props.qr_data;
        mostrarModalQr.value = true;
    }

    // Si hay datos de Stripe desde el backend, mostrar formulario
    if (props.stripe_data) {
        clientSecret.value = props.stripe_data.client_secret;
        mostrarFormularioStripe.value = true;
        inicializarStripeElements();
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

const inicializarStripeElements = () => {
    console.log('🔵 [Stripe Debug] Inicializando Stripe Elements...');
    console.log('🔵 [Stripe Debug] stripe.value:', !!stripe.value, 'clientSecret.value:', !!clientSecret.value);
    
    if (!stripe.value || !clientSecret.value) {
        console.warn('⚠️ [Stripe Debug] No se puede inicializar: falta stripe o clientSecret');
        estadoDebug.value = 'Falta stripe o clientSecret';
        return;
    }

    try {
        const appearance = {
            theme: 'stripe',
            variables: {
                colorPrimary: '#4f46e5',
            },
        };

        console.log('🔵 [Stripe Debug] Creando elements con clientSecret:', clientSecret.value.substring(0, 20) + '...');
        elements.value = stripe.value.elements({ clientSecret: clientSecret.value, appearance });
        cardElement.value = elements.value.create('payment');
        console.log('✅ [Stripe Debug] Elements creados');
        
        setTimeout(() => {
            const cardContainer = document.querySelector('#card-element');
            console.log('🔵 [Stripe Debug] Buscando contenedor #card-element:', !!cardContainer);
            if (cardContainer) {
                cardElement.value.mount('#card-element');
                console.log('✅ [Stripe Debug] Card element montado');
                estadoDebug.value = 'Formulario de tarjeta listo';
            } else {
                console.error('❌ [Stripe Debug] No se encontró #card-element en el DOM');
                estadoDebug.value = 'Error: contenedor no encontrado';
            }
        }, 100);
    } catch (error) {
        console.error('❌ [Stripe Debug] Error al inicializar elements:', error);
        estadoDebug.value = 'Error al crear formulario: ' + error.message;
    }
};

const seleccionarMetodoPago = (metodo) => {
    form.metodo_pago = metodo;
    
    if (metodo === 'Stripe') {
        mostrarFormularioStripe.value = true;
    } else {
        mostrarFormularioStripe.value = false;
        clientSecret.value = null;
    }
};

const crearPaymentIntent = () => {
    console.log('🔵 [Stripe Debug] Intentando crear PaymentIntent...');
    console.log('🔵 [Stripe Debug] Form data:', { asiento: form.asiento, viaje_id: form.viaje_id, metodo_pago: form.metodo_pago, moneda: form.moneda });
    
    if (!form.asiento || !form.viaje_id || !form.metodo_pago || form.metodo_pago !== 'Stripe') {
        console.warn('⚠️ [Stripe Debug] Datos incompletos para crear PaymentIntent');
        estadoDebug.value = 'Faltan datos (asiento, viaje o método)';
        return;
    }

    console.log('🔵 [Stripe Debug] Enviando POST a procesar-compra...');
    procesandoStripe.value = true;
    errorStripe.value = null;
    estadoDebug.value = 'Creando PaymentIntent...';

    form.post(route('cliente.boletos.procesar-compra'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            console.log('✅ [Stripe Debug] POST exitoso. Props recibidos:', page.props);
            if (page.props.stripe_data) {
                console.log('🔵 [Stripe Debug] stripe_data recibido:', page.props.stripe_data);
                clientSecret.value = page.props.stripe_data.client_secret;
                estadoDebug.value = 'PaymentIntent creado, inicializando formulario...';
                nextTick(() => {
                    console.log('🔵 [Stripe Debug] Llamando a inicializarStripeElements en nextTick');
                    inicializarStripeElements();
                });
            } else if (page.props.success) {
                console.log('✅ [Stripe Debug] Success sin stripe_data, redirigiendo...');
                router.visit(route('cliente.historial'));
            } else {
                console.warn('⚠️ [Stripe Debug] Respuesta exitosa pero sin stripe_data ni success');
                estadoDebug.value = 'Respuesta incompleta del servidor';
            }
            procesandoStripe.value = false;
        },
        onError: (errors) => {
            console.error('❌ [Stripe Debug] Error en POST:', errors);
            errorStripe.value = errors.stripe_error || 'Error al inicializar el pago';
            estadoDebug.value = 'Error: ' + (errors.stripe_error || 'Error desconocido');
            procesandoStripe.value = false;
        },
        onFinish: () => {
            console.log('🔵 [Stripe Debug] POST finalizado');
            procesandoStripe.value = false;
        }
    });
};

// NO crear PaymentIntent automáticamente - esperar a que el usuario haga clic en el botón
// El formulario de Stripe se mostrará después de crear el PaymentIntent

const submit = () => {
    if (form.metodo_pago === 'Stripe') {
        // Si aún no hay clientSecret, crear el PaymentIntent primero
        if (!clientSecret.value) {
            crearPaymentIntent();
        } else {
            // Si ya existe el clientSecret, procesar el pago
            procesarPagoStripe();
        }
    } else {
        form.post(route('cliente.boletos.procesar-compra'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.qr_data) {
                    qrData.value = page.props.qr_data;
                    mostrarModalQr.value = true;
                    errorQr.value = null;
                } else {
                    router.visit(route('cliente.historial'));
                }
            },
            onError: (errors) => {
                if (errors.qr_error) {
                    errorQr.value = errors.qr_error;
                    mostrarModalQr.value = true;
                }
            }
        });
    }
};

const procesarPagoStripe = async () => {
    if (!stripe.value || !clientSecret.value) {
        errorStripe.value = 'Por favor espere mientras se inicializa el formulario de pago';
        return;
    }

    procesandoStripe.value = true;
    errorStripe.value = null;

    try {
        const { error, paymentIntent } = await stripe.value.confirmPayment({
            elements: elements.value,
            redirect: 'if_required',
        });

        if (error) {
            errorStripe.value = error.message;
            procesandoStripe.value = false;
        } else if (paymentIntent && paymentIntent.status === 'succeeded') {
            // Pago exitoso - redirigir al historial
            router.visit(route('cliente.historial'), {
                onSuccess: () => {
                    alert('¡Pago procesado exitosamente!');
                }
            });
        } else {
            errorStripe.value = 'Estado de pago inesperado: ' + (paymentIntent?.status || 'desconocido');
            procesandoStripe.value = false;
        }
    } catch (err) {
        console.error('Error al confirmar pago:', err);
        errorStripe.value = 'Error al procesar el pago. Intente nuevamente.';
        procesandoStripe.value = false;
    }
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
                            {{ simboloMoneda }} {{ montoBob.toFixed(2) }}
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

                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-sm font-medium mb-4" style="color: var(--text-primary)">
                            Método de Pago *
                        </label>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Botón Pago QR -->
                            <button
                                type="button"
                                @click="seleccionarMetodoPago('QR')"
                                class="group relative p-6 rounded-xl border-2 transition-all duration-200 hover:shadow-lg"
                                :class="{
                                    'border-blue-500 bg-blue-50': form.metodo_pago === 'QR',
                                    'border-gray-200 hover:border-gray-300': form.metodo_pago !== 'QR'
                                }"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="text-5xl">🏦</div>
                                    <div class="text-left flex-1">
                                        <div class="font-bold text-lg mb-1" style="color: var(--text-primary)">
                                            Pago QR
                                        </div>
                                        <div class="text-sm" style="color: var(--text-secondary)">
                                            PagoFácil - Escanea con tu app
                                        </div>
                                    </div>
                                    <div v-if="form.metodo_pago === 'QR'" class="text-blue-500">
                                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </button>

                            <!-- Botón Pago Stripe -->
                            <button
                                type="button"
                                @click="seleccionarMetodoPago('Stripe')"
                                class="group relative p-6 rounded-xl border-2 transition-all duration-200 hover:shadow-lg"
                                :class="{
                                    'border-blue-500 bg-blue-50': form.metodo_pago === 'Stripe',
                                    'border-gray-200 hover:border-gray-300': form.metodo_pago !== 'Stripe'
                                }"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="text-5xl">💳</div>
                                    <div class="text-left flex-1">
                                        <div class="font-bold text-lg mb-1" style="color: var(--text-primary)">
                                            Tarjeta
                                        </div>
                                        <div class="text-sm" style="color: var(--text-secondary)">
                                            Visa, Mastercard, Amex
                                        </div>
                                    </div>
                                    <div v-if="form.metodo_pago === 'Stripe'" class="text-blue-500">
                                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <p v-if="form.errors.metodo_pago" class="mt-2 text-sm text-red-600">
                            {{ form.errors.metodo_pago }}
                        </p>
                    </div>
                 

                    <!-- Formulario de Stripe (solo si seleccionó Stripe y ya hay PaymentIntent) -->
                    <div v-if="mostrarFormularioStripe && clientSecret" class="space-y-4">
                        <div class="p-6 rounded-lg border" style="background-color: var(--card-bg); border-color: var(--border-color)">
                            <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary)">
                                Información de Tarjeta
                            </h3>
                            <div id="card-element" class="p-4 rounded-lg border" style="background-color: white;"></div>
                            <p v-if="errorStripe" class="mt-2 text-sm text-red-600">
                                {{ errorStripe }}
                            </p>
                        </div>
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
                                    {{ form.metodo_pago === 'QR' ? 'QR (PagoFácil)' : form.metodo_pago === 'Stripe' ? 'Tarjeta (Stripe)' : 'No seleccionado' }}
                                </span>
                            </div>
                            <div class="flex justify-between pt-2 border-t" style="border-color: var(--border-color)">
                                <span class="font-semibold" style="color: var(--text-primary)">Total a Pagar:</span>
                                <span class="font-bold text-lg text-green-600">
                                    {{ simboloMoneda }} {{ montoBob.toFixed(2) }}
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
                            :disabled="form.processing || !form.asiento || !form.metodo_pago || procesandoStripe"
                            class="px-6 py-2 rounded-md text-sm font-medium text-white transition-all duration-200 hover:scale-105"
                            style="background: linear-gradient(135deg, var(--primary-600), var(--primary-500));" con Stripe
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing || !form.asiento || !form.metodo_pago || procesandoStripe }"
                        >
                            {{ procesandoStripe ? 'Procesando pago...' : form.processing ? 'Procesando...' : form.metodo_pago === 'Stripe' ? (clientSecret ? 'Pagar con Tarjeta' : 'Continuar') : 'Confirmar Compra' }}
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

