<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    boleto: Object
});

const verificando = ref(false);
const mostrarModalQr = ref(false);

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
            <title>Ticket de Boleto</title>
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
                    <div class="subtitulo">COMPROBANTE DE BOLETO</div>
                    <div class="separador"></div>
                </div>
                
                <div class="texto">
                    <div class="negrita">Boleto #${props.boleto.id}</div>
                    <div>Fecha: ${new Date(props.boleto.fecha_venta).toLocaleString('es-BO', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })}</div>
                </div>
                
                <div class="separador"></div>
                
                <div class="texto">
                    <div class="negrita">RUTA</div>
             
                    <div>${props.boleto.origen} → ${props.boleto.destino}</div>
                </div>
                
                <div class="separador"></div>
                
                <div class="texto">
                    <div class="negrita">VIAJE</div>
                    <div>Salida: ${new Date(props.boleto.fecha_salida).toLocaleString('es-BO', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })}</div>
                    ${props.boleto.fecha_llegada ? `<div>Llegada: ${new Date(props.boleto.fecha_llegada).toLocaleString('es-BO', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })}</div>` : ''}
                    <div>Asiento: ${props.boleto.asiento}</div>
                    <div>Vehículo: ${props.boleto.vehiculo_placa}</div>
                </div>
                
                <div class="separador"></div>
                
                <div class="texto">
                    <div class="negrita">PASAJERO</div>
                    <div>${props.boleto.cliente_nombre} ${props.boleto.cliente_apellido}</div>
                    <div>CI: ${props.boleto.cliente_ci}</div>
                    ${props.boleto.cliente_telefono ? `<div>Tel: ${props.boleto.cliente_telefono}</div>` : ''}
                </div>
                
                <div class="separador"></div>
                
                <div class="texto">
                    <div class="negrita">PAGO</div>
                    <div>Monto: Bs ${parseFloat(props.boleto.monto_total).toFixed(2)}</div>
                    <div>Método: ${props.boleto.metodo_pago || 'Efectivo'}</div>
                    <div>Estado: ${props.boleto.estado_pago}</div>
                </div>
                
                ${props.boleto.qr_base64 && props.boleto.metodo_pago === 'QR' ? `
                    <div class="separador"></div>
                    <div class="qr-container">
                        <div class="negrita">CÓDIGO QR DE PAGO</div>
                        <img src="data:image/png;base64,${props.boleto.qr_base64}" alt="QR de Pago">
                        ${props.boleto.transaction_id ? `<div style="font-size: 8pt; margin-top: 5px;">ID: ${props.boleto.transaction_id}</div>` : ''}
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
        // Cerrar la ventana después de imprimir (opcional)
        // ventanaTicket.close();
    }, 250);
};

const verificarEstadoPago = async () => {
    if (verificando.value) return;
    
    verificando.value = true;
    
    try {
        const response = await axios.post(route('boletos.verificar-estado', props.boleto.id));
        
        if (response.data.success) {
            if (response.data.pagado) {
                alert('¡El pago ha sido confirmado exitosamente!');
                // Recargar la página para actualizar el estado
                router.reload({ only: ['boleto'], preserveScroll: false });
            } else {
                alert('El pago aún está pendiente. El cliente debe completar el pago escaneando el QR.');
            }
        } else {
            alert('Error: ' + (response.data.error || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error al verificar estado:', error);
        alert('Error al verificar estado: ' + (error.response?.data?.error || error.message || 'Error desconocido'));
    } finally {
        verificando.value = false;
    }
};

const abrirModalQr = () => {
    mostrarModalQr.value = true;
};

const cerrarModalQr = () => {
    mostrarModalQr.value = false;
};
</script>

<template>
    <Head title="Detalle de Boleto" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight" style="color: var(--text-primary)">
                Detalle de Boleto
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <!-- Encabezado del Comprobante -->
                        <div class="text-center mb-6 print:block">
                            <h1 class="text-2xl font-bold mb-2" style="color: var(--text-primary)">COMPROBANTE DE BOLETO</h1>
                            <p class="text-sm" style="color: var(--text-secondary)">
                                Fecha de emisión: {{ new Date(boleto.fecha_venta).toLocaleDateString('es-ES') }}
                            </p>
                        </div>

                        <!-- Información del Viaje -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">Información del Viaje</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-lg" style="background-color: var(--header-bg)">
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Ruta</p>
                                    <p class="font-medium" style="color: var(--text-primary)">{{ boleto.ruta_nombre }}</p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Origen - Destino</p>
                                    <p class="font-medium" style="color: var(--text-primary)">{{ boleto.origen }} → {{ boleto.destino }}</p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Fecha y Hora de Salida</p>
                                    <p class="font-medium" style="color: var(--text-primary)">
                                        {{ new Date(boleto.fecha_salida).toLocaleString('es-ES') }}
                                    </p>
                                </div>
                                <div v-if="boleto.fecha_llegada">
                                    <p class="text-sm" style="color: var(--text-secondary)">Fecha y Hora de Llegada</p>
                                    <p class="font-medium" style="color: var(--text-primary)">
                                        {{ new Date(boleto.fecha_llegada).toLocaleString('es-ES') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Vehículo</p>
                                    <p class="font-medium" style="color: var(--text-primary)">
                                        {{ boleto.vehiculo_marca }} {{ boleto.vehiculo_modelo }} - {{ boleto.vehiculo_placa }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Número de Asiento</p>
                                    <p class="font-bold text-2xl" style="color: var(--accent-color)">{{ boleto.asiento }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Pasajero -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">Información del Pasajero</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-lg" style="background-color: var(--header-bg)">
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">Nombre Completo</p>
                                    <p class="font-medium" style="color: var(--text-primary)">
                                        {{ boleto.cliente_nombre }} {{ boleto.cliente_apellido }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm" style="color: var(--text-secondary)">CI</p>
                                    <p class="font-medium" style="color: var(--text-primary)">{{ boleto.cliente_ci }}</p>
                                </div>
                                <div v-if="boleto.cliente_telefono">
                                    <p class="text-sm" style="color: var(--text-secondary)">Teléfono</p>
                                    <p class="font-medium" style="color: var(--text-primary)">{{ boleto.cliente_telefono }}</p>
                                </div>
                                <div v-if="boleto.cliente_correo">
                                    <p class="text-sm" style="color: var(--text-secondary)">Correo</p>
                                    <p class="font-medium" style="color: var(--text-primary)">{{ boleto.cliente_correo }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Información de Pago -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">Información de Pago</h3>
                            <div class="p-4 rounded-lg" style="background-color: var(--header-bg)">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm" style="color: var(--text-secondary)">Precio del Boleto</p>
                                        <p class="font-bold text-xl" style="color: var(--text-primary)">
                                            Bs {{ parseFloat(boleto.monto_total).toFixed(2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm" style="color: var(--text-secondary)">Estado de Pago</p>
                                        <span 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                            :class="boleto.estado_pago === 'Pagado' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                        >
                                            {{ boleto.estado_pago }}
                                        </span>
                                        <div v-if="boleto.metodo_pago === 'QR'" class="mt-2">
                                            <span class="text-xs px-2 py-0.5 rounded bg-purple-100 text-purple-700">
                                                Método: QR
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón de Verificación para Admin/Secretaria -->
                                <div v-if="boleto.metodo_pago === 'QR' && boleto.estado_pago === 'Pendiente'" class="pt-4 border-t" style="border-color: var(--border-color)">
                                    <div class="flex flex-col md:flex-row gap-3 items-center">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium" style="color: var(--text-primary)">
                                                🔍 Verificar Estado del Pago
                                            </p>
                                            <p class="text-xs mt-1" style="color: var(--text-secondary)">
                                                Consulta en tiempo real si el cliente ya completó el pago en PagoFácil
                                            </p>
                                        </div>
                                        <button
                                            @click="verificarEstadoPago"
                                            :disabled="verificando"
                                            class="px-6 py-2.5 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                                            style="background: linear-gradient(135deg, #10b981, #059669);"
                                        >
                                            <span v-if="!verificando" class="flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Verificar Estado
                                            </span>
                                            <span v-else class="flex items-center gap-2">
                                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Verificando...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Código QR de Pago (si está pendiente y tiene QR) -->
                        <div v-if="boleto.estado_pago === 'Pendiente' && boleto.metodo_pago === 'QR' && boleto.qr_base64" class="mb-6">
                            <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">Código QR de Pago</h3>
                            <div class="p-6 rounded-lg border-2" style="background-color: var(--card-bg); border-color: var(--border-color)">
                                <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">
                                    <!-- QR Image -->
                                    <div class="flex-shrink-0">
                                        <img 
                                            :src="`data:image/png;base64,${boleto.qr_base64}`" 
                                            alt="Código QR de Pago"
                                            class="w-48 h-48 border-2 rounded-lg shadow-lg"
                                            style="border-color: var(--border-color);"
                                        />
                                    </div>
                                    
                                    <!-- Información y Acciones -->
                                    <div class="flex-1 text-center md:text-left">
                                        <div class="mb-4">
                                            <p class="text-sm mb-2" style="color: var(--text-secondary)">
                                                <strong>Instrucciones:</strong> El cliente debe escanear este código QR con su aplicación de pago móvil para completar la transacción.
                                            </p>
                                            <p class="text-xs mt-2" style="color: var(--text-secondary)">
                                                ID de Transacción: <span class="font-mono">{{ boleto.transaction_id || 'N/A' }}</span>
                                            </p>
                                        </div>
                                        
                                        <div class="p-3 rounded-lg bg-blue-50 border border-blue-200 mb-4">
                                            <p class="text-xs text-blue-800">
                                                <strong>Nota:</strong> Después de que el cliente pague, haz click en "Verificar Estado" para confirmar el pago automáticamente.
                                            </p>
                                        </div>
                                        
                                        <div class="flex gap-2 justify-center md:justify-start">
                                            <button
                                                @click="verificarEstadoPago"
                                                :disabled="verificando"
                                                class="px-4 py-2 rounded-md text-sm font-medium bg-purple-600 text-white hover:bg-purple-700 transition-colors"
                                                :class="{ 'opacity-50 cursor-not-allowed': verificando }"
                                            >
                                                {{ verificando ? 'Verificando...' : 'Verificar Estado' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
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

                            <button
                                v-if="boleto.qr_base64 && boleto.metodo_pago === 'QR'"
                                @click="abrirModalQr"
                                type="button"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                Mostrar QR
                            </button>

                            <a
                                :href="route('boletos.index')"
                                class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium"
                                style="border-color: var(--border-color); color: var(--text-primary); background-color: var(--card-bg)"
                            >
                                Volver al Listado
                            </a>

                            <a
                                v-if="boleto.estado_pago === 'Pendiente'"
                                :href="route('boletos.edit', boleto.id)"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Grande para Mostrar QR -->
        <div 
            v-if="mostrarModalQr" 
            class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4"
            @click.self="cerrarModalQr"
        >
            <div 
                class="bg-white rounded-lg shadow-2xl max-w-2xl w-full relative"
                style="background-color: var(--card-bg)"
            >
                <!-- Botón Cerrar -->
                <button
                    @click="cerrarModalQr"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors z-10"
                    style="color: var(--text-secondary)"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Contenido del Modal -->
                <div class="p-8">
                    <div class="text-center">
                        <h3 class="text-2xl font-bold mb-6" style="color: var(--text-primary)">
                            Código QR de Pago
                        </h3>
                        
                        <div class="flex justify-center mb-6">
                            <img
                                v-if="boleto.qr_base64"
                                :src="`data:image/png;base64,${boleto.qr_base64}`"
                                alt="Código QR de Pago"
                                class="border-4 rounded-lg shadow-xl"
                                style="border-color: var(--border-color); max-width: 500px; width: 100%; height: auto;"
                            />
                        </div>

                        <div class="mt-6">
                            <button
                                @click="cerrarModalQr"
                                class="px-6 py-3 rounded-md text-base font-medium text-white bg-gray-600 hover:bg-gray-700 transition-colors"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    .print\:block {
        display: block !important;
    }
    .print\:hidden,
    .print\:hidden * {
        display: none !important;
    }
    #printable-section,
    #printable-section * {
        visibility: visible;
    }
    #printable-section {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>
