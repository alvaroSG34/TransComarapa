<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    venta: Object
});

const marcarComoPagado = () => {
    if (confirm('¿Confirmar que esta venta ha sido pagada?')) {
        router.post(route('ventas.marcar-pagado', props.venta.id), {}, {
            preserveScroll: true
        });
    }
};

const cancelarVenta = () => {
    if (confirm('¿Está seguro de cancelar esta venta? Esta acción no se puede deshacer.')) {
        router.post(route('ventas.cancelar', props.venta.id), {}, {
            onSuccess: () => {
                router.visit(route('ventas.index'));
            }
        });
    }
};

const imprimirComprobante = () => {
    window.print();
};

const getEstadoPagoColor = (estado) => {
    const colores = {
        'Pendiente': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'Pagado': 'bg-green-100 text-green-800 border-green-200',
        'Cancelado': 'bg-red-100 text-red-800 border-red-200'
    };
    return colores[estado] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getTipoColor = (tipo) => {
    const colores = {
        'Boleto': 'bg-blue-100 text-blue-800 border-blue-200',
        'Encomienda': 'bg-purple-100 text-purple-800 border-purple-200'
    };
    return colores[tipo] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const esBoleto = props.venta.tipo === 'Boleto';
const esEncomienda = props.venta.tipo === 'Encomienda';
</script>

<template>
    <Head :title="`Venta #${venta.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                    Detalle de Venta #{{ venta.id }}
                </h2>
                <a
                    :href="route('ventas.index')"
                    class="text-sm hover:underline"
                    style="color: var(--text-secondary)"
                >
                    ← Volver a Ventas
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Información Principal -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-4">
                                <span :class="['px-4 py-2 text-sm font-semibold rounded-lg border-2', getTipoColor(venta.tipo)]">
                                    {{ venta.tipo }}
                                </span>
                                <span :class="['px-4 py-2 text-sm font-semibold rounded-lg border-2', getEstadoPagoColor(venta.estado_pago)]">
                                    {{ venta.estado_pago }}
                                </span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm" style="color: var(--text-secondary)">Monto Total</div>
                                <div class="text-3xl font-bold text-green-600">
                                    Bs {{ parseFloat(venta.monto_total).toFixed(2) }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium" style="color: var(--text-secondary)">Fecha de Venta:</span>
                                <span class="ml-2" style="color: var(--text-primary)">{{ formatearFecha(venta.fecha) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Cliente -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary)">
                            Información del Cliente
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium" style="color: var(--text-secondary)">Nombre Completo:</span>
                                <span class="ml-2" style="color: var(--text-primary)">
                                    {{ venta.cliente_nombre }} {{ venta.cliente_apellido }}
                                </span>
                            </div>
                            <div>
                                <span class="font-medium" style="color: var(--text-secondary)">CI:</span>
                                <span class="ml-2" style="color: var(--text-primary)">{{ venta.cliente_ci }}</span>
                            </div>
                            <div>
                                <span class="font-medium" style="color: var(--text-secondary)">Teléfono:</span>
                                <span class="ml-2" style="color: var(--text-primary)">{{ venta.cliente_telefono }}</span>
                            </div>
                            <div>
                                <span class="font-medium" style="color: var(--text-secondary)">Correo:</span>
                                <span class="ml-2" style="color: var(--text-primary)">{{ venta.cliente_correo || 'No especificado' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalle del Boleto -->
                <div v-if="esBoleto" class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary)">
                            Detalle del Boleto
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información del Viaje -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-sm" style="color: var(--text-secondary)">Información del Viaje</h4>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Ruta:</span>
                                        <span class="ml-2" style="color: var(--text-primary)">{{ venta.ruta_boleto_nombre }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Origen:</span>
                                        <span class="ml-2" style="color: var(--text-primary)">{{ venta.ruta_boleto_origen }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Destino:</span>
                                        <span class="ml-2" style="color: var(--text-primary)">{{ venta.ruta_boleto_destino }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del Asiento y Vehículo -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-sm" style="color: var(--text-secondary)">Datos del Viaje</h4>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Número de Asiento:</span>
                                        <span class="ml-2 text-lg font-bold text-blue-600">{{ venta.asiento }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Fecha de Salida:</span>
                                        <span class="ml-2" style="color: var(--text-primary)">{{ formatearFecha(venta.fecha_salida) }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Vehículo:</span>
                                        <span class="ml-2" style="color: var(--text-primary)">
                                            {{ venta.vehiculo_marca }} {{ venta.vehiculo_modelo }} - {{ venta.vehiculo_placa }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Conductor:</span>
                                        <span class="ml-2" style="color: var(--text-primary)">
                                            {{ venta.conductor_nombre }} {{ venta.conductor_apellido }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Estado del Viaje:</span>
                                        <span class="ml-2 capitalize" style="color: var(--text-primary)">{{ venta.viaje_estado }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detalle de la Encomienda -->
                <div v-if="esEncomienda" class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary)">
                            Detalle de la Encomienda
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Información de la Ruta -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-sm" style="color: var(--text-secondary)">Información de la Ruta</h4>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Ruta:</span>
                                        <span class="ml-2" style="color: var(--text-primary)">{{ venta.ruta_encomienda_nombre }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Origen:</span>
                                        <span class="ml-2" style="color: var(--text-primary)">{{ venta.ruta_encomienda_origen }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Destino:</span>
                                        <span class="ml-2" style="color: var(--text-primary)">{{ venta.ruta_encomienda_destino }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del Paquete -->
                            <div class="space-y-3">
                                <h4 class="font-semibold text-sm" style="color: var(--text-secondary)">Datos del Paquete</h4>
                                <div class="space-y-2 text-sm">
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Destinatario:</span>
                                        <span class="ml-2" style="color: var(--text-primary)">{{ venta.nombre_destinatario }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Peso:</span>
                                        <span class="ml-2 font-bold text-purple-600">{{ parseFloat(venta.peso).toFixed(2) }} kg</span>
                                    </div>
                                    <div>
                                        <span class="font-medium" style="color: var(--text-secondary)">Modalidad de Pago:</span>
                                        <span class="ml-2 capitalize" style="color: var(--text-primary)">{{ venta.modalidad_pago }}</span>
                                    </div>
                                    <div v-if="venta.descripcion">
                                        <span class="font-medium" style="color: var(--text-secondary)">Descripción:</span>
                                        <p class="mt-1" style="color: var(--text-primary)">{{ venta.descripcion }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Foto del Paquete -->
                        <div v-if="venta.img_url" class="mt-6">
                            <h4 class="font-semibold text-sm mb-2" style="color: var(--text-secondary)">Foto del Paquete</h4>
                            <img :src="venta.img_url" alt="Foto del paquete" class="h-64 w-auto object-cover rounded-lg border" style="border-color: var(--border-color)">
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex justify-end space-x-4 print:hidden">
                    <button
                        @click="imprimirComprobante"
                        class="px-4 py-2 rounded-md text-sm font-medium"
                        style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                    >
                        🖨️ Imprimir Comprobante
                    </button>

                    <button
                        v-if="venta.estado_pago === 'Pendiente'"
                        @click="marcarComoPagado"
                        class="px-4 py-2 rounded-md text-sm font-medium bg-green-600 text-white hover:bg-green-700"
                    >
                        ✓ Marcar como Pagado
                    </button>

                    <button
                        v-if="venta.estado_pago !== 'Cancelado'"
                        @click="cancelarVenta"
                        class="px-4 py-2 rounded-md text-sm font-medium bg-red-600 text-white hover:bg-red-700"
                    >
                        ✕ Cancelar Venta
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    .print\:hidden {
        display: none !important;
    }
}
</style>
