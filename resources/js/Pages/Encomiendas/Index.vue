<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    encomiendas: Array,
    rutas: Array,
    filtros: Object
});

const filtrosLocales = ref({
    ruta_id: props.filtros?.ruta_id || '',
    modalidad_pago: props.filtros?.modalidad_pago || '',
    fecha_desde: props.filtros?.fecha_desde || '',
    fecha_hasta: props.filtros?.fecha_hasta || ''
});

const aplicarFiltros = () => {
    router.get(route('encomiendas.index'), filtrosLocales.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const limpiarFiltros = () => {
    filtrosLocales.value = {
        ruta_id: '',
        modalidad_pago: '',
        fecha_desde: '',
        fecha_hasta: ''
    };
    router.get(route('encomiendas.index'));
};

// Filtrar encomiendas por estado
const encomiendasPendientes = computed(() => {
    return props.encomiendas.filter(encomienda => encomienda.estado_pago === 'Pendiente');
});

const encomiendasPagadas = computed(() => {
    return props.encomiendas.filter(encomienda => encomienda.estado_pago === 'Pagado');
});

const encomiendasCanceladas = computed(() => {
    return props.encomiendas.filter(encomienda => encomienda.estado_pago === 'Cancelado');
});

const eliminarEncomienda = (id, destinatario) => {
    if (confirm(`¿Está seguro de eliminar la encomienda para ${destinatario}?`)) {
        router.delete(route('encomiendas.destroy', id), {
            preserveScroll: true
        });
    }
};

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleString('es-BO', {
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

const getModalidadColor = (modalidad) => {
    const colores = {
        'origen': 'bg-blue-100 text-blue-800',
        'mixto': 'bg-purple-100 text-purple-800',
        'destino': 'bg-orange-100 text-orange-800'
    };
    return colores[modalidad] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Encomiendas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                    Gestión de Encomiendas
                </h2>
                <a
                    :href="route('encomiendas.create')"
                    class="px-4 py-2 rounded-md text-sm font-medium"
                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                >
                    + Registrar Encomienda
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Filtros -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <h3 class="text-lg font-medium mb-4">Filtros</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Ruta</label>
                                <select
                                    v-model="filtrosLocales.ruta_id"
                                    class="w-full rounded-md border-gray-300"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                >
                                    <option value="">Todas las rutas</option>
                                    <option v-for="ruta in rutas" :key="ruta.id" :value="ruta.id">
                                        {{ ruta.nombre }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Modalidad de Pago</label>
                                <select
                                    v-model="filtrosLocales.modalidad_pago"
                                    class="w-full rounded-md border-gray-300"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                >
                                    <option value="">Todas</option>
                                    <option value="origen">Pago en Origen</option>
                                    <option value="mixto">Pago Mixto</option>
                                    <option value="destino">Pago en Destino</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Desde</label>
                                <input
                                    type="date"
                                    v-model="filtrosLocales.fecha_desde"
                                    class="w-full rounded-md border-gray-300"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2">Hasta</label>
                                <input
                                    type="date"
                                    v-model="filtrosLocales.fecha_hasta"
                                    class="w-full rounded-md border-gray-300"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-4">
                            <button
                                @click="limpiarFiltros"
                                class="px-4 py-2 rounded-md text-sm"
                                style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                            >
                                Limpiar
                            </button>
                            <button
                                @click="aplicarFiltros"
                                class="px-4 py-2 rounded-md text-sm"
                                style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                            >
                                Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Encomiendas Pendientes -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <span :class="getEstadoPagoColor('Pendiente')" class="px-3 py-1 text-sm rounded-full font-medium mr-3">
                                Pendiente
                            </span>
                            <span class="text-sm font-normal" style="color: var(--text-secondary)">
                                ({{ encomiendasPendientes.length }})
                            </span>
                        </h3>
                        <div v-if="encomiendasPendientes.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead>
                                    <tr style="background-color: var(--header-bg)">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Ruta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Destinatario
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Remitente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Peso
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Precio
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Modalidad
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="encomienda in encomiendasPendientes" :key="encomienda.venta_id">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ encomienda.ruta_nombre }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                {{ encomienda.origen }} → {{ encomienda.destino }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ encomienda.nombre_destinatario }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ encomienda.cliente_nombre }} {{ encomienda.cliente_apellido }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                CI: {{ encomienda.cliente_ci }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ parseFloat(encomienda.peso).toFixed(2) }} kg
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-green-600">
                                                Bs {{ parseFloat(encomienda.monto_total).toFixed(2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getModalidadColor(encomienda.modalidad_pago)" class="px-2 py-1 text-xs rounded-full font-medium capitalize">
                                                {{ encomienda.modalidad_pago }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                            <a
                                                :href="route('encomiendas.show', encomienda.venta_id)"
                                                class="inline-block px-3 py-1 rounded text-sm text-blue-600 hover:text-blue-800"
                                            >
                                                Ver
                                            </a>
                                            <a
                                                :href="route('encomiendas.edit', encomienda.venta_id)"
                                                class="inline-block px-3 py-1 rounded text-sm"
                                                style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                            >
                                                Editar
                                            </a>
                                            <button
                                                @click="eliminarEncomienda(encomienda.venta_id, encomienda.nombre_destinatario)"
                                                class="inline-block px-3 py-1 rounded text-sm bg-red-600 text-white hover:bg-red-700"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-sm" style="color: var(--text-secondary)">
                                No hay encomiendas pendientes
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Encomiendas Pagadas -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <span :class="getEstadoPagoColor('Pagado')" class="px-3 py-1 text-sm rounded-full font-medium mr-3">
                                Pagado
                            </span>
                            <span class="text-sm font-normal" style="color: var(--text-secondary)">
                                ({{ encomiendasPagadas.length }})
                            </span>
                        </h3>
                        <div v-if="encomiendasPagadas.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead>
                                    <tr style="background-color: var(--header-bg)">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Ruta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Destinatario
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Remitente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Peso
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Precio
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Modalidad
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="encomienda in encomiendasPagadas" :key="encomienda.venta_id">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ encomienda.ruta_nombre }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                {{ encomienda.origen }} → {{ encomienda.destino }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ encomienda.nombre_destinatario }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ encomienda.cliente_nombre }} {{ encomienda.cliente_apellido }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                CI: {{ encomienda.cliente_ci }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ parseFloat(encomienda.peso).toFixed(2) }} kg
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-green-600">
                                                Bs {{ parseFloat(encomienda.monto_total).toFixed(2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getModalidadColor(encomienda.modalidad_pago)" class="px-2 py-1 text-xs rounded-full font-medium capitalize">
                                                {{ encomienda.modalidad_pago }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                            <a
                                                :href="route('encomiendas.show', encomienda.venta_id)"
                                                class="inline-block px-3 py-1 rounded text-sm text-blue-600 hover:text-blue-800"
                                            >
                                                Ver
                                            </a>
                                            <a
                                                :href="route('encomiendas.edit', encomienda.venta_id)"
                                                class="inline-block px-3 py-1 rounded text-sm"
                                                style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                            >
                                                Editar
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-sm" style="color: var(--text-secondary)">
                                No hay encomiendas pagadas
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Encomiendas Canceladas -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <span :class="getEstadoPagoColor('Cancelado')" class="px-3 py-1 text-sm rounded-full font-medium mr-3">
                                Cancelado
                            </span>
                            <span class="text-sm font-normal" style="color: var(--text-secondary)">
                                ({{ encomiendasCanceladas.length }})
                            </span>
                        </h3>
                        <div v-if="encomiendasCanceladas.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead>
                                    <tr style="background-color: var(--header-bg)">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Ruta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Destinatario
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Remitente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Peso
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Precio
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Modalidad
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="encomienda in encomiendasCanceladas" :key="encomienda.venta_id">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ encomienda.ruta_nombre }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                {{ encomienda.origen }} → {{ encomienda.destino }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ encomienda.nombre_destinatario }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ encomienda.cliente_nombre }} {{ encomienda.cliente_apellido }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                CI: {{ encomienda.cliente_ci }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ parseFloat(encomienda.peso).toFixed(2) }} kg
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-green-600">
                                                Bs {{ parseFloat(encomienda.monto_total).toFixed(2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getModalidadColor(encomienda.modalidad_pago)" class="px-2 py-1 text-xs rounded-full font-medium capitalize">
                                                {{ encomienda.modalidad_pago }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                            <a
                                                :href="route('encomiendas.show', encomienda.venta_id)"
                                                class="inline-block px-3 py-1 rounded text-sm text-blue-600 hover:text-blue-800"
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
                                No hay encomiendas canceladas
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
