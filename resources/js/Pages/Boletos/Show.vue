<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    boleto: Object
});

const imprimirComprobante = () => {
    window.print();
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-lg" style="background-color: var(--header-bg)">
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
                                </div>
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
