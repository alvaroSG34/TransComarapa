<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    encomienda: Object
});

const formPago = useForm({});

const montoPendiente = computed(() => {
    const total = parseFloat(props.encomienda.monto_total);
    const pagadoOrigen = parseFloat(props.encomienda.monto_pagado_origen || 0);
    const pagadoDestino = parseFloat(props.encomienda.monto_pagado_destino || 0);
    return total - pagadoOrigen - pagadoDestino;
});

const imprimirComprobante = () => {
    window.print();
};

const confirmarPagoDestino = () => {
    if (confirm(`¿Está seguro de confirmar el pago de Bs ${montoPendiente.value.toFixed(2)}?`)) {
        formPago.post(route('encomiendas.pago-destino', props.encomienda.venta_id), {
            preserveScroll: true
        });
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

                        <!-- Botón para Confirmar Pago en Destino -->
                        <div v-if="montoPendiente > 0 && encomienda.estado_pago === 'Pendiente'" class="mb-6">
                            <button
                                @click="confirmarPagoDestino"
                                type="button"
                                :disabled="formPago.processing"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span v-if="formPago.processing">Procesando...</span>
                                <span v-else>Confirmar Pago</span>
                            </button>
                            <p class="mt-2 text-sm" style="color: var(--text-secondary)">
                                Al confirmar, se registrará el pago de Bs {{ montoPendiente.toFixed(2) }}
                            </p>
                        </div>

                        <!-- Foto del Paquete -->
                        <div v-if="encomienda.img_url" class="mb-6">
                            <h4 class="text-lg font-semibold mb-3">Foto del Paquete</h4>
                            <div class="p-4 rounded-lg" style="background-color: var(--header-bg)">
                                <img :src="encomienda.img_url" alt="Foto del paquete" class="max-w-md rounded-lg shadow-md">
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex gap-3 mt-6 print:hidden">
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
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    .print\:hidden {
        display: none;
    }
}
</style>
