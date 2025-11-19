<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    cliente: Object,
    historial: Array
});

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getEstadoPagoColor = (estado) => {
    const colores = {
        'Pendiente': 'bg-yellow-100 text-yellow-800',
        'Pagado': 'bg-green-100 text-green-800',
        'Cancelado': 'bg-red-100 text-red-800'
    };
    return colores[estado] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Cliente: ${cliente.nombre} ${cliente.apellido}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl leading-tight" style="color: var(--text-primary)">
                Perfil del Cliente
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Información Personal -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-medium leading-6" style="color: var(--text-primary)">
                                    {{ cliente.nombre }} {{ cliente.apellido }}
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm" style="color: var(--text-secondary)">
                                    Información personal y de contacto.
                                </p>
                            </div>
                            <span v-if="cliente.deleted_at" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                Baneado
                            </span>
                            <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Activo
                            </span>
                        </div>
                        
                        <div class="mt-5 border-t border-gray-200">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-8 pt-5">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium" style="color: var(--text-secondary)">Cédula de Identidad</dt>
                                    <dd class="mt-1 text-sm" style="color: var(--text-primary)">{{ cliente.ci }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium" style="color: var(--text-secondary)">Teléfono</dt>
                                    <dd class="mt-1 text-sm" style="color: var(--text-primary)">{{ cliente.telefono }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium" style="color: var(--text-secondary)">Correo Electrónico</dt>
                                    <dd class="mt-1 text-sm" style="color: var(--text-primary)">{{ cliente.correo || 'No registrado' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium" style="color: var(--text-secondary)">Registrado el</dt>
                                    <dd class="mt-1 text-sm" style="color: var(--text-primary)">{{ formatearFecha(cliente.created_at) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Historial de Compras -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <h3 class="text-lg font-medium leading-6 mb-4" style="color: var(--text-primary)">
                            Historial de Compras
                        </h3>

                        <div v-if="historial.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead style="background-color: var(--header-bg)">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">Detalle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">Monto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="venta in historial" :key="venta.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-primary)">
                                            {{ formatearFecha(venta.fecha) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-primary)">
                                            {{ venta.tipo }}
                                        </td>
                                        <td class="px-6 py-4 text-sm" style="color: var(--text-primary)">
                                            <div v-if="venta.tipo === 'Boleto'">
                                                <span class="font-medium">{{ venta.ruta_boleto }}</span>
                                                <span class="text-xs block text-gray-500">Asiento: {{ venta.asiento }}</span>
                                            </div>
                                            <div v-else>
                                                <span class="font-medium">{{ venta.ruta_encomienda }}</span>
                                                <span class="text-xs block text-gray-500">Dest: {{ venta.nombre_destinatario }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                            Bs {{ parseFloat(venta.monto_total).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getEstadoPagoColor(venta.estado_pago)]">
                                                {{ venta.estado_pago }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            Este cliente no tiene historial de compras.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

