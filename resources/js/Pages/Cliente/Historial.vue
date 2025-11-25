<script setup>
import ClienteLayout from '@/Layouts/ClienteLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    compras: Array,
    filtros: Object
});

const tipoFiltro = ref(props.filtros?.tipo || 'todos');
const estadoFiltro = ref(props.filtros?.estado_pago || 'todos');
const fechaDesde = ref(props.filtros?.fecha_desde || '');
const fechaHasta = ref(props.filtros?.fecha_hasta || '');

const mostrarModalQr = ref(false);
const qrData = ref(null);
const reintentando = ref(false);

const aplicarFiltros = () => {
    router.get(route('cliente.historial'), {
        tipo: tipoFiltro.value,
        estado_pago: estadoFiltro.value,
        fecha_desde: fechaDesde.value,
        fecha_hasta: fechaHasta.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const mostrarQr = (compra) => {
    if (compra.pago?.qr_base64) {
        qrData.value = {
            qr_base64: compra.pago.qr_base64,
            transaction_id: compra.pago.transaction_id,
            monto_total: compra.monto_total,
        };
        mostrarModalQr.value = true;
    }
};

const cerrarModal = () => {
    mostrarModalQr.value = false;
    qrData.value = null;
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

const reintentarGenerarQr = async (boletoId) => {
    if (!boletoId) return;
    
    reintentando.value = true;
    
    try {
        const response = await axios.post(route('boletos.reintentar-qr', boletoId));
        
        if (response.data?.qr_data) {
            qrData.value = response.data.qr_data;
        } else if (response.data?.flash?.qr_data) {
            qrData.value = response.data.flash.qr_data;
        }
        
        router.reload({ only: ['compras'] });
    } catch (error) {
        console.error('Error al reintentar:', error);
    } finally {
        reintentando.value = false;
    }
};

const formatearFecha = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatearFechaCorta = (fecha) => {
    if (!fecha) return 'N/A';
    return new Date(fecha).toLocaleDateString('es-BO', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const obtenerColorEstado = (estado) => {
    const colores = {
        'Pagado': 'bg-green-100 text-green-800',
        'Pendiente': 'bg-yellow-100 text-yellow-800',
        'Cancelado': 'bg-red-100 text-red-800',
    };
    return colores[estado] || 'bg-gray-100 text-gray-800';
};

const comprasFiltradas = computed(() => {
    return props.compras || [];
});
</script>

<template>
    <Head title="Mis Compras" />

    <ClienteLayout>
        <template #header>
            <div>
                <h1 class="text-3xl font-bold mb-2" style="color: var(--text-primary)">
                    Mis Compras 📋
                </h1>
                <p class="text-lg" style="color: var(--text-secondary)">
                    Consulta tu historial de boletos y encomiendas
                </p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filtros -->
            <div class="p-6 rounded-2xl shadow-lg" style="background-color: var(--card-bg); border: 1px solid var(--border-color)">
                <h2 class="text-xl font-bold mb-4" style="color: var(--text-primary)">
                    Filtros
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--text-primary)">
                            Tipo
                        </label>
                        <select
                            v-model="tipoFiltro"
                            @change="aplicarFiltros"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                        >
                            <option value="todos">Todos</option>
                            <option value="Boleto">Boletos</option>
                            <option value="Encomienda">Encomiendas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--text-primary)">
                            Estado de Pago
                        </label>
                        <select
                            v-model="estadoFiltro"
                            @change="aplicarFiltros"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                        >
                            <option value="todos">Todos</option>
                            <option value="Pagado">Pagado</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--text-primary)">
                            Fecha Desde
                        </label>
                        <input
                            type="date"
                            v-model="fechaDesde"
                            @change="aplicarFiltros"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2" style="color: var(--text-primary)">
                            Fecha Hasta
                        </label>
                        <input
                            type="date"
                            v-model="fechaHasta"
                            @change="aplicarFiltros"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                        />
                    </div>
                </div>
            </div>

            <!-- Lista de Compras -->
            <div v-if="comprasFiltradas.length > 0" class="space-y-4">
                <div
                    v-for="compra in comprasFiltradas"
                    :key="compra.id"
                    class="p-6 rounded-xl shadow-md transition-all duration-300 hover:shadow-xl"
                    style="background-color: var(--card-bg); border: 1px solid var(--border-color);"
                >
                    <!-- Header de la Compra -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 pb-4 border-b" style="border-color: var(--border-color)">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">
                                    {{ compra.tipo === 'Boleto' ? '🎫' : '📦' }}
                                </span>
                                <div>
                                    <h3 class="text-lg font-bold" style="color: var(--text-primary)">
                                        {{ compra.tipo === 'Boleto' ? 'Boleto' : 'Encomienda' }}
                                    </h3>
                                    <p class="text-sm" style="color: var(--text-secondary)">
                                        {{ formatearFecha(compra.fecha) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold" :class="obtenerColorEstado(compra.estado_pago)">
                                {{ compra.estado_pago }}
                            </span>
                            <span class="text-xl font-bold text-green-600">
                                Bs {{ parseFloat(compra.monto_total).toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Detalles de Boletos -->
                    <div v-if="compra.tipo === 'Boleto' && compra.boletos && compra.boletos.length > 0" class="space-y-3">
                        <h4 class="font-semibold mb-2" style="color: var(--text-primary)">
                            Boletos ({{ compra.boletos.length }})
                        </h4>
                        <div
                            v-for="boleto in compra.boletos"
                            :key="boleto.id"
                            class="p-4 rounded-lg"
                            style="background-color: var(--header-bg); border: 1px solid var(--border-color)"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span style="color: var(--text-secondary)">Ruta:</span>
                                    <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                        {{ boleto.ruta_nombre }}
                                    </span>
                                </div>
                                <div>
                                    <span style="color: var(--text-secondary)">Asiento:</span>
                                    <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                        #{{ boleto.asiento }}
                                    </span>
                                </div>
                                <div>
                                    <span style="color: var(--text-secondary)">Fecha Salida:</span>
                                    <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                        {{ formatearFechaCorta(boleto.fecha_salida) }}
                                    </span>
                                </div>
                                <div>
                                    <span style="color: var(--text-secondary)">Origen → Destino:</span>
                                    <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                        {{ boleto.origen }} → {{ boleto.destino }}
                                    </span>
                                </div>
                                <div>
                                    <span style="color: var(--text-secondary)">Estado Viaje:</span>
                                    <span class="ml-2 font-medium capitalize" style="color: var(--text-primary)">
                                        {{ boleto.viaje_estado }}
                                    </span>
                                </div>
                                <div v-if="compra.vehiculo?.placa">
                                    <span style="color: var(--text-secondary)">Vehículo:</span>
                                    <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                        {{ compra.vehiculo.placa }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles de Encomienda -->
                    <div v-if="compra.tipo === 'Encomienda' && compra.encomienda" class="space-y-3">
                        <h4 class="font-semibold mb-2" style="color: var(--text-primary)">
                            Encomienda
                        </h4>
                        <div class="p-4 rounded-lg" style="background-color: var(--header-bg); border: 1px solid var(--border-color)">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span style="color: var(--text-secondary)">Ruta:</span>
                                    <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                        {{ compra.encomienda.ruta_nombre }}
                                    </span>
                                </div>
                                <div>
                                    <span style="color: var(--text-secondary)">Destinatario:</span>
                                    <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                        {{ compra.encomienda.nombre_destinatario }}
                                    </span>
                                </div>
                                <div>
                                    <span style="color: var(--text-secondary)">Peso:</span>
                                    <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                        {{ parseFloat(compra.encomienda.peso).toFixed(2) }} kg
                                    </span>
                                </div>
                                <div>
                                    <span style="color: var(--text-secondary)">Modalidad Pago:</span>
                                    <span class="ml-2 font-medium capitalize" style="color: var(--text-primary)">
                                        {{ compra.encomienda.modalidad_pago }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Pago -->
                    <div v-if="compra.pago" class="mt-4 pt-4 border-t" style="border-color: var(--border-color)">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm" style="color: var(--text-secondary)">
                                    Método de Pago:
                                </span>
                                <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                    {{ compra.pago.metodo_pago || 'N/A' }}
                                </span>
                            </div>
                            <div v-if="compra.pago.qr_base64 && compra.estado_pago === 'Pendiente'">
                                <button
                                    @click="mostrarQr(compra)"
                                    class="px-4 py-2 rounded-lg text-sm font-medium text-white transition-all duration-200 hover:scale-105"
                                    style="background: linear-gradient(135deg, var(--primary-600), var(--primary-500));"
                                >
                                    Ver Código QR
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensaje si no hay compras -->
            <div v-else class="text-center py-12 rounded-2xl" style="background-color: var(--card-bg); border: 1px solid var(--border-color)">
                <div class="text-6xl mb-4">📋</div>
                <h3 class="text-2xl font-bold mb-2" style="color: var(--text-primary)">
                    No hay compras registradas
                </h3>
                <p class="text-lg mb-6" style="color: var(--text-secondary)">
                    Aún no has realizado ninguna compra. ¡Comienza a viajar con nosotros!
                </p>
                <Link
                    :href="route('cliente.boletos.comprar')"
                    class="inline-flex items-center px-6 py-3 rounded-lg font-semibold text-white transition-all duration-200 hover:scale-105"
                    style="background: linear-gradient(135deg, var(--primary-600), var(--primary-500));"
                >
                    Comprar mi primer boleto
                </Link>
            </div>
        </div>

        <!-- Modal para mostrar QR -->
        <div v-if="mostrarModalQr" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="cerrarModal">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4" style="background-color: var(--card-bg)">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold" style="color: var(--text-primary)">
                            Código QR de Pago
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
                        <p class="text-xs mb-2" style="color: var(--text-secondary)">
                            ID de Transacción: {{ qrData.transaction_id || 'N/A' }}
                        </p>
                        <p class="text-sm font-medium mb-4" style="color: var(--text-primary)">
                            Monto: Bs {{ parseFloat(qrData.monto_total).toFixed(2) }}
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
                </div>
            </div>
        </div>
    </ClienteLayout>
</template>

<style scoped>
/* Estilos adicionales si es necesario */
</style>

