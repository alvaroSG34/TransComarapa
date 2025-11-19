<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    ventas: Array,
    filtros: Object
});

const filtrosLocales = ref({
    tipo: props.filtros?.tipo || 'todos',
    estado_pago: props.filtros?.estado_pago || 'todos',
    fecha_desde: props.filtros?.fecha_desde || '',
    fecha_hasta: props.filtros?.fecha_hasta || '',
    cliente_busqueda: props.filtros?.cliente_busqueda || ''
});

const aplicarFiltros = () => {
    router.get(route('ventas.index'), filtrosLocales.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const limpiarFiltros = () => {
    filtrosLocales.value = {
        tipo: 'todos',
        estado_pago: 'todos',
        fecha_desde: '',
        fecha_hasta: '',
        cliente_busqueda: ''
    };
    aplicarFiltros();
};

const marcarComoPagado = (ventaId) => {
    if (confirm('¿Confirmar que esta venta ha sido pagada?')) {
        router.post(route('ventas.marcar-pagado', ventaId), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Recargar datos
            }
        });
    }
};

const getEstadoPagoColor = (estado) => {
    const colores = {
        'Pendiente': 'bg-yellow-100 text-yellow-800',
        'Pagado': 'bg-green-100 text-green-800',
        'Cancelado': 'bg-red-100 text-red-800'
    };
    return colores[estado] || 'bg-gray-100 text-gray-800';
};

const getTipoColor = (tipo) => {
    const colores = {
        'Boleto': 'bg-blue-100 text-blue-800',
        'Encomienda': 'bg-purple-100 text-purple-800'
    };
    return colores[tipo] || 'bg-gray-100 text-gray-800';
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

const obtenerDetalle = (venta) => {
    if (venta.tipo === 'Boleto') {
        return {
            titulo: `${venta.ruta_boleto_nombre || 'Ruta'}`,
            subtitulo: `Asiento: ${venta.asiento} • Salida: ${formatearFecha(venta.fecha_salida)}`,
            origen: venta.ruta_boleto_origen,
            destino: venta.ruta_boleto_destino
        };
    } else {
        return {
            titulo: `${venta.ruta_encomienda_nombre || 'Ruta'}`,
            subtitulo: `Destinatario: ${venta.nombre_destinatario} • Peso: ${parseFloat(venta.peso).toFixed(2)} kg`,
            origen: venta.ruta_encomienda_origen,
            destino: venta.ruta_encomienda_destino
        };
    }
};
</script>

<template>
    <Head title="Ventas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                Gestión de Ventas
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Panel de Filtros -->
                <div class="mb-6 overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary)">
                            Filtros de Búsqueda
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Tipo de Venta -->
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-primary)">
                                    Tipo de Venta
                                </label>
                                <select
                                    v-model="filtrosLocales.tipo"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                >
                                    <option value="todos">Todos</option>
                                    <option value="Boleto">Boletos</option>
                                    <option value="Encomienda">Encomiendas</option>
                                </select>
                            </div>

                            <!-- Estado de Pago -->
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-primary)">
                                    Estado de Pago
                                </label>
                                <select
                                    v-model="filtrosLocales.estado_pago"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                >
                                    <option value="todos">Todos</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Pagado">Pagado</option>
                                    <option value="Cancelado">Cancelado</option>
                                </select>
                            </div>

                            <!-- Búsqueda de Cliente -->
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-primary)">
                                    Buscar Cliente
                                </label>
                                <input
                                    v-model="filtrosLocales.cliente_busqueda"
                                    type="text"
                                    placeholder="CI, nombre o apellido..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                />
                            </div>

                            <!-- Fecha Desde -->
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-primary)">
                                    Fecha Desde
                                </label>
                                <input
                                    v-model="filtrosLocales.fecha_desde"
                                    type="date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                />
                            </div>

                            <!-- Fecha Hasta -->
                            <div>
                                <label class="block text-sm font-medium mb-1" style="color: var(--text-primary)">
                                    Fecha Hasta
                                </label>
                                <input
                                    v-model="filtrosLocales.fecha_hasta"
                                    type="date"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                />
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex justify-end space-x-3 mt-4">
                            <button
                                @click="limpiarFiltros"
                                class="px-4 py-2 rounded-md text-sm font-medium"
                                style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                            >
                                Limpiar Filtros
                            </button>
                            <button
                                @click="aplicarFiltros"
                                class="px-4 py-2 rounded-md text-sm font-medium"
                                style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                            >
                                Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Ventas -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead>
                                    <tr style="background-color: var(--header-bg)">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Fecha
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Tipo
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Cliente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Detalle
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Monto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Estado
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="venta in ventas" :key="venta.id" style="background-color: var(--card-bg)">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" style="color: var(--text-primary)">
                                            #{{ venta.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-primary)">
                                            {{ formatearFecha(venta.fecha) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getTipoColor(venta.tipo)]">
                                                {{ venta.tipo }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm" style="color: var(--text-primary)">
                                            <div class="font-medium">{{ venta.cliente_nombre }} {{ venta.cliente_apellido }}</div>
                                            <div class="text-xs" style="color: var(--text-secondary)">CI: {{ venta.cliente_ci }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm" style="color: var(--text-primary)">
                                            <div class="font-medium">{{ obtenerDetalle(venta).titulo }}</div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                {{ obtenerDetalle(venta).origen }} → {{ obtenerDetalle(venta).destino }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                {{ obtenerDetalle(venta).subtitulo }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                            Bs {{ parseFloat(venta.monto_total).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2 py-1 text-xs font-semibold rounded-full', getEstadoPagoColor(venta.estado_pago)]">
                                                {{ venta.estado_pago }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a
                                                :href="route('ventas.show', venta.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Estado Vacío -->
                            <div v-if="!ventas || ventas.length === 0" class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium" style="color: var(--text-primary)">No se encontraron ventas</h3>
                                <p class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Intenta ajustar los filtros de búsqueda o registra nuevas ventas desde Boletos o Encomiendas.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
