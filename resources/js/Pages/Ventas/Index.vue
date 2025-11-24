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
        fecha_desde: '',
        fecha_hasta: '',
        cliente_busqueda: ''
    };
    aplicarFiltros();
};

// Filtrar ventas por estado
const ventasPendientes = computed(() => {
    return props.ventas.filter(venta => venta.estado_pago === 'Pendiente');
});

const ventasPagadas = computed(() => {
    return props.ventas.filter(venta => venta.estado_pago === 'Pagado');
});

const ventasCanceladas = computed(() => {
    return props.ventas.filter(venta => venta.estado_pago === 'Cancelado');
});

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

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

                <!-- Tabla de Ventas Pendientes -->
                <div class="mb-6 overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <span :class="getEstadoPagoColor('Pendiente')" class="px-3 py-1 text-sm rounded-full font-medium mr-3">
                                Pendiente
                            </span>
                            <span class="text-sm font-normal" style="color: var(--text-secondary)">
                                ({{ ventasPendientes.length }})
                            </span>
                        </h3>
                        <div v-if="ventasPendientes.length > 0" class="overflow-x-auto">
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
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="venta in ventasPendientes" :key="venta.id" style="background-color: var(--card-bg)">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a
                                                :href="route('ventas.show', venta.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Ver
                                            </a>
                                            <button
                                                v-if="venta.estado_pago === 'Pendiente'"
                                                @click="marcarComoPagado(venta.id)"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Marcar Pagado
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-sm" style="color: var(--text-secondary)">
                                No hay ventas pendientes
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Ventas Pagadas -->
                <div class="mb-6 overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <span :class="getEstadoPagoColor('Pagado')" class="px-3 py-1 text-sm rounded-full font-medium mr-3">
                                Pagado
                            </span>
                            <span class="text-sm font-normal" style="color: var(--text-secondary)">
                                ({{ ventasPagadas.length }})
                            </span>
                        </h3>
                        <div v-if="ventasPagadas.length > 0" class="overflow-x-auto">
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
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="venta in ventasPagadas" :key="venta.id" style="background-color: var(--card-bg)">
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
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-sm" style="color: var(--text-secondary)">
                                No hay ventas pagadas
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Ventas Canceladas -->
                <div class="mb-6 overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <span :class="getEstadoPagoColor('Cancelado')" class="px-3 py-1 text-sm rounded-full font-medium mr-3">
                                Cancelado
                            </span>
                            <span class="text-sm font-normal" style="color: var(--text-secondary)">
                                ({{ ventasCanceladas.length }})
                            </span>
                        </h3>
                        <div v-if="ventasCanceladas.length > 0" class="overflow-x-auto">
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
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="venta in ventasCanceladas" :key="venta.id" style="background-color: var(--card-bg)">
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
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-sm" style="color: var(--text-secondary)">
                                No hay ventas canceladas
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
