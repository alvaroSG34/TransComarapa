<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    encomienda: Object,
    pago_origen: Object,
    pago_destino: Object
});

const formPago = useForm({
    metodo_pago_destino: 'Efectivo'
});

const montoPendiente = computed(() => {
    const total = parseFloat(props.encomienda.monto_total);
    const pagadoOrigen = parseFloat(props.encomienda.monto_pagado_origen || 0);
    const pagadoDestino = parseFloat(props.encomienda.monto_pagado_destino || 0);
    return total - pagadoOrigen - pagadoDestino;
});

// Verificar si hay QR de destino pendiente
const tienePagoDestinoQR = computed(() => {
    return props.pago_destino && props.pago_destino.estado_pago === 'Pendiente' && props.pago_destino.qr_base64;
});

// Verificar si hay QR de origen pendiente
const tienePagoOrigenQR = computed(() => {
    return props.pago_origen && props.pago_origen.estado_pago === 'Pendiente' && props.pago_origen.qr_base64;
});

const verificandoOrigen = ref(false);
const verificandoDestino = ref(false);
const mostrarModalQrOrigen = ref(false);
const mostrarModalQrDestino = ref(false);

const imprimirComprobante = () => {
    window.print();
};

const imprimirTicket = () => {
    // Crear una ventana nueva para el ticket
    const ventanaTicket = window.open('', '_blank', 'width=300,height=600');
    if (!ventanaTicket) {
        alert('Por favor, permite las ventanas emergentes para imprimir el ticket');
        return;
    }

    const contenido = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Ticket de Encomienda</title>
            <style>
                @media print {
                    @page {
                        size: 72mm auto;
                        margin: 0;
                    }
                    body {
                        width: 72mm;
                        margin: 0;
                        padding: 5mm;
                        font-family: 'Courier New', monospace;
                        font-size: 10pt;
                    }
                }
                body {
                    width: 72mm;
                    margin: 0;
                    padding: 5mm;
                    font-family: 'Courier New', monospace;
                    font-size: 10pt;
                    line-height: 1.3;
                }
                .ticket {
                    width: 100%;
                    max-width: 72mm;
                }
                .centrado {
                    text-align: center;
                }
                .separador {
                    border-top: 1px dashed #000;
                    margin: 8px 0;
                }
                .negrita {
                    font-weight: bold;
                }
                .titulo {
                    font-size: 12pt;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                .subtitulo {
                    font-size: 9pt;
                    margin-bottom: 3px;
                }
                .texto {
                    font-size: 9pt;
                    margin: 2px 0;
                }
                .qr-container {
                    text-align: center;
                    margin: 10px 0;
                }
                .qr-container img {
                    max-width: 150px;
                    height: auto;
                }
            </style>
        </head>
        <body>
            <div class="ticket">
                <div class="centrado">
                    <div class="titulo">TRANSCOMARAPA</div>
                    <div class="subtitulo">COMPROBANTE DE ENCOMIENDA</div>
                    <div class="separador"></div>
                </div>
                
                <div class="texto">
                    <div class="negrita">Encomienda #${props.encomienda.venta_id}</div>
                    <div>Fecha: ${new Date(props.encomienda.fecha_registro).toLocaleString('es-BO', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })}</div>
                </div>
                
                <div class="separador"></div>
                
                <div class="texto">
                    <div class="negrita">RUTA</div>
                   
                    <div>${props.encomienda.origen} → ${props.encomienda.destino}</div>
                </div>
                
                <div class="separador"></div>
                
                <div class="texto">
                    <div class="negrita">REMITENTE</div>
                    <div>${props.encomienda.cliente_nombre} ${props.encomienda.cliente_apellido}</div>
                    <div>CI: ${props.encomienda.cliente_ci}</div>
                    ${props.encomienda.cliente_telefono ? `<div>Tel: ${props.encomienda.cliente_telefono}</div>` : ''}
                </div>
                
                <div class="separador"></div>
                
                <div class="texto">
                    <div class="negrita">DESTINATARIO</div>
                    <div>${props.encomienda.nombre_destinatario}</div>
                </div>
                
                <div class="separador"></div>
                
                <div class="texto">
                    <div class="negrita">PAQUETE</div>
                    <div>Peso: ${parseFloat(props.encomienda.peso).toFixed(2)} kg</div>
                    <div>Modalidad: ${props.encomienda.modalidad_pago}</div>
                    ${props.encomienda.descripcion ? `<div style="margin-top: 3px;">Descripción: ${props.encomienda.descripcion}</div>` : ''}
                </div>
                
                <div class="separador"></div>
                
                <div class="texto">
                    <div class="negrita">PAGOS</div>
                    <div>Total: Bs ${parseFloat(props.encomienda.monto_total).toFixed(2)}</div>
                    <div>Origen: Bs ${parseFloat(props.encomienda.monto_pagado_origen || 0).toFixed(2)}</div>
                    <div>Destino: Bs ${parseFloat(props.encomienda.monto_pagado_destino || 0).toFixed(2)}</div>
                    <div>Pendiente: Bs ${montoPendiente.value.toFixed(2)}</div>
                    <div>Estado: ${props.encomienda.estado_pago}</div>
                </div>
                
                ${props.pago_origen && props.pago_origen.qr_base64 && props.pago_origen.estado_pago === 'Pendiente' ? `
                    <div class="separador"></div>
                    <div class="qr-container">
                        <div class="negrita">QR PAGO ORIGEN</div>
                        <div class="texto">Bs ${parseFloat(props.pago_origen.monto).toFixed(2)}</div>
                        <img src="data:image/png;base64,${props.pago_origen.qr_base64}" alt="QR Origen">
                        ${props.pago_origen.transaction_id ? `<div style="font-size: 8pt; margin-top: 5px;">ID: ${props.pago_origen.transaction_id}</div>` : ''}
                    </div>
                ` : ''}
                
                ${props.pago_destino && props.pago_destino.qr_base64 && props.pago_destino.estado_pago === 'Pendiente' ? `
                    <div class="separador"></div>
                    <div class="qr-container">
                        <div class="negrita">QR PAGO DESTINO</div>
                        <div class="texto">Bs ${parseFloat(props.pago_destino.monto).toFixed(2)}</div>
                        <img src="data:image/png;base64,${props.pago_destino.qr_base64}" alt="QR Destino">
                        ${props.pago_destino.transaction_id ? `<div style="font-size: 8pt; margin-top: 5px;">ID: ${props.pago_destino.transaction_id}</div>` : ''}
                    </div>
                ` : ''}
                
                <div class="separador"></div>
                
                <div class="centrado">
                    <div class="texto" style="font-size: 8pt; margin-top: 10px;">
                        Gracias por su preferencia
                    </div>
                    <div class="texto" style="font-size: 8pt;">
                        ${new Date().toLocaleDateString('es-BO')} ${new Date().toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' })}
                    </div>
                </div>
            </div>
        </body>
        </html>
    `;

    ventanaTicket.document.write(contenido);
    ventanaTicket.document.close();
    
    // Esperar a que se cargue el contenido y luego imprimir
    setTimeout(() => {
        ventanaTicket.print();
    }, 250);
};

const verificarEstadoOrigen = async () => {
    if (verificandoOrigen.value) return;
    
    verificandoOrigen.value = true;
    
    try {
        const response = await axios.post(route('encomiendas.verificar-estado', props.encomienda.venta_id), {
            tipo: 'origen'
        });
        
        if (response.data.success) {
            if (response.data.pagado) {
                alert('¡El pago en origen ha sido confirmado exitosamente!');
                router.reload({ only: ['encomienda', 'pago_origen', 'pago_destino'], preserveScroll: false });
            } else {
                alert('El pago aún está pendiente. El cliente debe completar el pago escaneando el QR.');
            }
        } else {
            alert('Error: ' + (response.data.error || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error al verificar estado origen:', error);
        alert('Error al verificar estado: ' + (error.response?.data?.error || error.message || 'Error desconocido'));
    } finally {
        verificandoOrigen.value = false;
    }
};

const verificarEstadoDestino = async () => {
    if (verificandoDestino.value) return;
    
    verificandoDestino.value = true;
    
    try {
        const response = await axios.post(route('encomiendas.verificar-estado', props.encomienda.venta_id), {
            tipo: 'destino'
        });
        
        if (response.data.success) {
            if (response.data.pagado) {
                alert('¡El pago en destino ha sido confirmado exitosamente!');
                router.reload({ only: ['encomienda', 'pago_origen', 'pago_destino'], preserveScroll: false });
            } else {
                alert('El pago aún está pendiente. El destinatario debe completar el pago escaneando el QR.');
            }
        } else {
            alert('Error: ' + (response.data.error || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error al verificar estado destino:', error);
        alert('Error al verificar estado: ' + (error.response?.data?.error || error.message || 'Error desconocido'));
    } finally {
        verificandoDestino.value = false;
    }
};

const abrirModalQrOrigen = () => {
    mostrarModalQrOrigen.value = true;
};

const cerrarModalQrOrigen = () => {
    mostrarModalQrOrigen.value = false;
};

const abrirModalQrDestino = () => {
    mostrarModalQrDestino.value = true;
};

const cerrarModalQrDestino = () => {
    mostrarModalQrDestino.value = false;
};

const confirmarPagoDestino = () => {
    console.log('=== CONFIRMAR PAGO DESTINO ===');
    console.log('Monto pendiente:', montoPendiente.value);
    console.log('Método seleccionado:', formPago.metodo_pago_destino);
    console.log('Form data:', formPago.data());
    console.log('Venta ID:', props.encomienda.venta_id);
    
    const metodoTexto = formPago.metodo_pago_destino === 'QR' ? 'QR (PagoFácil)' : 'Efectivo';
    if (confirm(`¿Está seguro de confirmar el pago de Bs ${montoPendiente.value.toFixed(2)} por ${metodoTexto}?`)) {
        console.log('Enviando petición...');
        formPago.post(route('encomiendas.pago-destino', props.encomienda.venta_id), {
            preserveScroll: true,
            onStart: () => {
                console.log('Iniciando envío...');
            },
            onSuccess: (page) => {
                console.log('Respuesta exitosa:', page);
                // Recargar para obtener el QR generado
                router.reload({ only: ['encomienda', 'pago_origen', 'pago_destino'], preserveScroll: false });
            },
            onError: (errors) => {
                console.error('Errores en la petición:', errors);
            },
            onFinish: () => {
                console.log('Finalizado');
            }
        });
    } else {
        console.log('Usuario canceló la confirmación');
    }
};
</script>

<template>
    <Head title="Detalle de Encomienda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                Detalle de Encomienda #{{ encomienda.venta_id }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <!-- Encabezado con Estado -->
                        <div class="mb-6 flex justify-between items-start">
                            <div>
                                <h3 class="text-2xl font-bold mb-2">Encomienda #{{ encomienda.venta_id }}</h3>
                                <p class="text-sm" style="color: var(--text-secondary)">
                                    Fecha de Registro: {{ new Date(encomienda.fecha_registro).toLocaleString('es-BO') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': encomienda.estado_pago === 'Pendiente',
                                        'bg-green-100 text-green-800': encomienda.estado_pago === 'Pagado',
                                        'bg-red-100 text-red-800': encomienda.estado_pago === 'Cancelado'
                                    }">
                                    {{ encomienda.estado_pago }}
                                </span>
                                <div class="mt-2 text-2xl font-bold" style="color: var(--accent-color)">
                                    Bs {{ parseFloat(encomienda.monto_total).toFixed(2) }}
                                </div>
                            </div>
                        </div>

                        <hr class="my-6" style="border-color: var(--border-color)">

                        <!-- Información de Ruta -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold mb-3">Información de Ruta</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-lg" style="background-color: var(--header-bg)">
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Ruta</p>
                                    <p class="font-medium">{{ encomienda.ruta_nombre }}</p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Origen</p>
                                    <p class="font-medium">{{ encomienda.origen }}</p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Destino</p>
                                    <p class="font-medium">{{ encomienda.destino }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Cliente (Remitente) -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold mb-3">Cliente Remitente</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-4 rounded-lg" style="background-color: var(--header-bg)">
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Nombre Completo</p>
                                    <p class="font-medium">{{ encomienda.cliente_nombre }} {{ encomienda.cliente_apellido }}</p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">CI</p>
                                    <p class="font-medium">{{ encomienda.cliente_ci }}</p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Teléfono</p>
                                    <p class="font-medium">{{ encomienda.cliente_telefono }}</p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Correo</p>
                                    <p class="font-medium">{{ encomienda.cliente_correo || 'No registrado' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles del Paquete -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold mb-3">Detalles del Paquete</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-lg" style="background-color: var(--header-bg)">
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Destinatario</p>
                                    <p class="font-medium text-lg">{{ encomienda.nombre_destinatario }}</p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Peso</p>
                                    <p class="font-medium text-lg" style="color: var(--accent-color)">
                                        {{ parseFloat(encomienda.peso).toFixed(2) }} kg
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Modalidad de Pago</p>
                                    <p class="font-medium capitalize">{{ encomienda.modalidad_pago }}</p>
                                </div>
                                <div v-if="encomienda.descripcion">
                                    <p class="text-sm" style="color: var(--text-secondary)">Descripción</p>
                                    <p class="font-medium">{{ encomienda.descripcion }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Pagos -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold mb-3">Información de Pagos</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-lg" style="background-color: var(--header-bg)">
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Pagado en Origen</p>
                                    <p class="font-medium text-lg text-green-600">
                                        Bs {{ parseFloat(encomienda.monto_pagado_origen || 0).toFixed(2) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Pagado en Destino</p>
                                    <p class="font-medium text-lg text-blue-600">
                                        Bs {{ parseFloat(encomienda.monto_pagado_destino || 0).toFixed(2) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Monto Pendiente</p>
                                    <p class="font-medium text-lg" :class="montoPendiente > 0 ? 'text-red-600' : 'text-green-600'">
                                        Bs {{ montoPendiente.toFixed(2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Código QR de Pago en Origen -->
                        <div v-if="tienePagoOrigenQR" class="mb-6">
                            <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">Código QR de Pago en Origen</h3>
                            <div class="p-6 rounded-lg border-2" style="background-color: var(--card-bg); border-color: var(--border-color)">
                                <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">
                                    <div class="flex-shrink-0">
                                        <img 
                                            :src="`data:image/png;base64,${pago_origen.qr_base64}`" 
                                            alt="Código QR de Pago en Origen"
                                            class="w-48 h-48 border-2 rounded-lg shadow-lg"
                                            style="border-color: var(--border-color);"
                                        />
                                    </div>
                                    <div class="flex-1 text-center md:text-left">
                                        <div class="mb-4">
                                            <p class="text-sm mb-2" style="color: var(--text-secondary)">
                                                <strong>Monto:</strong> Bs {{ parseFloat(pago_origen.monto).toFixed(2) }}
                                            </p>
                                            <p class="text-sm mb-2" style="color: var(--text-secondary)">
                                                <strong>Instrucciones:</strong> El cliente debe escanear este código QR con su aplicación de pago móvil para completar el pago en origen.
                                            </p>
                                            <p class="text-xs mt-2" style="color: var(--text-secondary)">
                                                ID de Transacción: <span class="font-mono">{{ pago_origen.transaction_id || 'N/A' }}</span>
                                            </p>
                                        </div>
                                        <div class="flex gap-2 justify-center md:justify-start">
                                            <button
                                                @click="verificarEstadoOrigen"
                                                :disabled="verificandoOrigen"
                                                class="px-4 py-2 rounded-md text-sm font-medium bg-purple-600 text-white hover:bg-purple-700 transition-colors"
                                                :class="{ 'opacity-50 cursor-not-allowed': verificandoOrigen }"
                                            >
                                                {{ verificandoOrigen ? 'Verificando...' : 'Verificar Estado Origen' }}
                                            </button>
                                            <button
                                                @click="abrirModalQrOrigen"
                                                class="px-4 py-2 rounded-md text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition-colors"
                                            >
                                                Mostrar QR
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Código QR de Pago en Destino -->
                        <div v-if="tienePagoDestinoQR" class="mb-6">
                            <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">Código QR de Pago en Destino</h3>
                            <div class="p-6 rounded-lg border-2" style="background-color: var(--card-bg); border-color: var(--border-color)">
                                <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">
                                    <div class="flex-shrink-0">
                                        <img 
                                            :src="`data:image/png;base64,${pago_destino.qr_base64}`" 
                                            alt="Código QR de Pago en Destino"
                                            class="w-48 h-48 border-2 rounded-lg shadow-lg"
                                            style="border-color: var(--border-color);"
                                        />
                                    </div>
                                    <div class="flex-1 text-center md:text-left">
                                        <div class="mb-4">
                                            <p class="text-sm mb-2" style="color: var(--text-secondary)">
                                                <strong>Monto:</strong> Bs {{ parseFloat(pago_destino.monto).toFixed(2) }}
                                            </p>
                                            <p class="text-sm mb-2" style="color: var(--text-secondary)">
                                                <strong>Instrucciones:</strong> El destinatario debe escanear este código QR con su aplicación de pago móvil para completar el pago en destino.
                                            </p>
                                            <p class="text-xs mt-2" style="color: var(--text-secondary)">
                                                ID de Transacción: <span class="font-mono">{{ pago_destino.transaction_id || 'N/A' }}</span>
                                            </p>
                                        </div>
                                        <div class="flex gap-2 justify-center md:justify-start">
                                            <button
                                                @click="verificarEstadoDestino"
                                                :disabled="verificandoDestino"
                                                class="px-4 py-2 rounded-md text-sm font-medium bg-purple-600 text-white hover:bg-purple-700 transition-colors"
                                                :class="{ 'opacity-50 cursor-not-allowed': verificandoDestino }"
                                            >
                                                {{ verificandoDestino ? 'Verificando...' : 'Verificar Estado Destino' }}
                                            </button>
                                            <button
                                                @click="abrirModalQrDestino"
                                                class="px-4 py-2 rounded-md text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition-colors"
                                            >
                                                Mostrar QR
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón para Confirmar Pago en Destino (solo si no hay QR pendiente) -->
                        <div v-if="montoPendiente > 0 && encomienda.estado_pago === 'Pendiente' && (encomienda.modalidad_pago === 'mixto' || encomienda.modalidad_pago === 'destino') && !tienePagoDestinoQR" class="mb-6">
                            <div class="p-4 rounded-lg border-2" style="background-color: var(--card-bg); border-color: var(--border-color)">
                                <h4 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">
                                    Registrar Pago en Destino
                                </h4>
                                
                                <div class="mb-4">
                                    <label for="metodo_pago_destino" class="block text-sm font-medium mb-2" style="color: var(--text-primary)">
                                        Método de Pago *
                                    </label>
                                    <select
                                        id="metodo_pago_destino"
                                        v-model="formPago.metodo_pago_destino"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                        :class="{ 'border-red-500': formPago.errors.metodo_pago_destino }"
                                    >
                                        <option value="Efectivo">Efectivo</option>
                                        <option value="QR">QR (PagoFácil)</option>
                                    </select>
                                    <p v-if="formPago.errors.metodo_pago_destino" class="mt-1 text-sm text-red-600">
                                        {{ formPago.errors.metodo_pago_destino }}
                                    </p>
                                    <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                        Seleccione cómo pagará el destinatario
                                    </p>
                                </div>

                                <div class="mb-4 p-3 rounded-lg" style="background-color: var(--header-bg)">
                                    <p class="text-sm mb-1" style="color: var(--text-secondary)">
                                        <strong>Monto a pagar:</strong> Bs {{ montoPendiente.toFixed(2) }}
                                    </p>
                                    <p class="text-sm" style="color: var(--text-secondary)">
                                        <strong>Método seleccionado:</strong> 
                                        <span class="font-medium">{{ formPago.metodo_pago_destino === 'QR' ? 'QR (PagoFácil)' : 'Efectivo' }}</span>
                                    </p>
                                    <p v-if="formPago.metodo_pago_destino === 'QR'" class="text-xs mt-2 text-blue-600">
                                        Se generará un código QR que el destinatario deberá escanear para realizar el pago.
                                    </p>
                                </div>

                                <button
                                    @click.prevent="confirmarPagoDestino"
                                    type="button"
                                    :disabled="formPago.processing || !formPago.metodo_pago_destino"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span v-if="formPago.processing">Procesando...</span>
                                    <span v-else-if="formPago.metodo_pago_destino === 'QR'">Generar QR para Pago</span>
                                    <span v-else>Confirmar Pago en Efectivo</span>
                                </button>
                            </div>
                        </div>

                        <!-- Foto del Paquete -->
                        <div v-if="encomienda.img_url_full" class="mb-6">
                            <h4 class="text-lg font-semibold mb-3">Foto del Paquete</h4>
                            <div class="p-4 rounded-lg" style="background-color: var(--header-bg)">
                                <img :src="encomienda.img_url_full" alt="Foto del paquete" class="max-w-md rounded-lg shadow-md">
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex gap-3 mt-6 print:hidden">
                            <button
                                @click="imprimirTicket"
                                type="button"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 hover:opacity-90"
                                style="background-color: #059669;"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Imprimir Ticket
                            </button>

                            <button
                                @click="imprimirComprobante"
                                type="button"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 hover:opacity-90"
                                style="background-color: var(--accent-color)"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Imprimir Comprobante
                            </button>

                            <a
                                :href="route('encomiendas.index')"
                                class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium"
                                style="border-color: var(--border-color); color: var(--text-primary); background-color: var(--card-bg)"
                            >
                                Volver al Listado
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Grande para Mostrar QR Origen -->
        <div 
            v-if="mostrarModalQrOrigen && tienePagoOrigenQR" 
            class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4"
            @click.self="cerrarModalQrOrigen"
        >
            <div 
                class="bg-white rounded-lg shadow-2xl max-w-2xl w-full relative"
                style="background-color: var(--card-bg)"
            >
                <button
                    @click="cerrarModalQrOrigen"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors z-10"
                    style="color: var(--text-secondary)"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="p-8 text-center">
                    <h3 class="text-2xl font-bold mb-4" style="color: var(--text-primary)">QR de Pago en Origen</h3>
                    <div class="mb-4">
                        <img 
                            :src="`data:image/png;base64,${pago_origen.qr_base64}`" 
                            alt="Código QR de Pago en Origen"
                            class="mx-auto border-4 rounded-lg shadow-2xl"
                            style="border-color: var(--border-color); max-width: 400px; width: 100%;"
                        />
                    </div>
                    <p class="text-lg font-semibold mb-2" style="color: var(--text-primary)">
                        Monto: Bs {{ parseFloat(pago_origen.monto).toFixed(2) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Modal Grande para Mostrar QR Destino -->
        <div 
            v-if="mostrarModalQrDestino && tienePagoDestinoQR" 
            class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4"
            @click.self="cerrarModalQrDestino"
        >
            <div 
                class="bg-white rounded-lg shadow-2xl max-w-2xl w-full relative"
                style="background-color: var(--card-bg)"
            >
                <button
                    @click="cerrarModalQrDestino"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors z-10"
                    style="color: var(--text-secondary)"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="p-8 text-center">
                    <h3 class="text-2xl font-bold mb-4" style="color: var(--text-primary)">QR de Pago en Destino</h3>
                    <div class="mb-4">
                        <img 
                            :src="`data:image/png;base64,${pago_destino.qr_base64}`" 
                            alt="Código QR de Pago en Destino"
                            class="mx-auto border-4 rounded-lg shadow-2xl"
                            style="border-color: var(--border-color); max-width: 400px; width: 100%;"
                        />
                    </div>
                    <p class="text-lg font-semibold mb-2" style="color: var(--text-primary)">
                        Monto: Bs {{ parseFloat(pago_destino.monto).toFixed(2) }}
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    .print\:hidden {
        display: none;
    }
}
</style>
