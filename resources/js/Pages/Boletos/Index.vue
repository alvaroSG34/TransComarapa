<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    boletos: Array,
    viajes: Array,
    filtros: Object
});

const filtrosLocales = ref({
    viaje_id: props.filtros?.viaje_id || '',
    fecha_desde: props.filtros?.fecha_desde || '',
    fecha_hasta: props.filtros?.fecha_hasta || ''
});

const aplicarFiltros = () => {
    router.get(route('boletos.index'), filtrosLocales.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const limpiarFiltros = () => {
    filtrosLocales.value = {
        viaje_id: '',
        fecha_desde: '',
        fecha_hasta: ''
    };
    router.get(route('boletos.index'));
};

// Filtrar boletos por estado
const boletosPendientes = computed(() => {
    return props.boletos.filter(boleto => boleto.estado_pago === 'Pendiente');
});

const boletosPagados = computed(() => {
    return props.boletos.filter(boleto => boleto.estado_pago === 'Pagado');
});

const boletosCancelados = computed(() => {
    return props.boletos.filter(boleto => boleto.estado_pago === 'Cancelado');
});

const cancelarBoleto = (id, asiento, rutaNombre) => {
    if (confirm(`¿Está seguro de cancelar el boleto del asiento ${asiento} en la ruta ${rutaNombre}?`)) {
        router.delete(route('boletos.destroy', id), {
            preserveScroll: true
        });
    }
};

const marcarPagado = (id) => {
    if (confirm('¿Confirmar que este boleto ha sido pagado?')) {
        router.post(route('boletos.marcar-pagado', id), {}, {
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
</script>

<template>
    <Head title="Boletos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                    Gestión de Boletos
                </h2>
                <a
                    :href="route('boletos.create')"
                    class="px-4 py-2 rounded-md text-sm font-medium"
                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                >
                    + Vender Boleto
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Filtros -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <h3 class="text-lg font-medium mb-4">Filtros</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Viaje</label>
                                <select
                                    v-model="filtrosLocales.viaje_id"
                                    class="w-full rounded-md border-gray-300"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                >
                                    <option value="">Todos los viajes</option>
                                    <option v-for="viaje in viajes" :key="viaje.id" :value="viaje.id">
                                        {{ viaje.ruta.nombre }} - {{ formatearFecha(viaje.fecha_salida) }}
                                    </option>
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

                <!-- Tabla de Boletos Pendientes -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <span :class="getEstadoPagoColor('Pendiente')" class="px-3 py-1 text-sm rounded-full font-medium mr-3">
                                Pendiente
                            </span>
                            <span class="text-sm font-normal" style="color: var(--text-secondary)">
                                ({{ boletosPendientes.length }})
                            </span>
                        </h3>
                        <div v-if="boletosPendientes.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead>
                                    <tr style="background-color: var(--header-bg)">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Ruta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Fecha Salida
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Asiento
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Cliente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Precio
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Método Pago
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="boleto in boletosPendientes" :key="boleto.id">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ boleto.ruta_nombre }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                {{ boleto.origen }} → {{ boleto.destino }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ formatearFecha(boleto.fecha_salida) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold" style="color: var(--text-primary)">
                                                # {{ boleto.asiento }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ boleto.cliente_nombre }} {{ boleto.cliente_apellido }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                CI: {{ boleto.cliente_ci }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                Bs {{ parseFloat(boleto.precio).toFixed(2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="boleto.metodo_pago === 'QR'">
                                                <span class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700 font-medium">
                                                    QR
                                                </span>
                                            </div>
                                            <div v-else>
                                                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700 font-medium">
                                                    Efectivo
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                            <a
                                                :href="route('boletos.show', boleto.id)"
                                                class="inline-block px-3 py-1 rounded text-sm text-blue-600 hover:text-blue-800"
                                            >
                                                Ver
                                            </a>
                                            <!-- Botón Marcar Pagado - Solo para Efectivo -->
                                            <button
                                                v-if="boleto.metodo_pago !== 'QR'"
                                                @click="marcarPagado(boleto.id)"
                                                class="inline-block px-3 py-1 rounded text-sm text-green-600 hover:text-green-800 font-medium"
                                            >
                                                Marcar Pagado
                                            </button>
                                            <button
                                                @click="cancelarBoleto(boleto.id, boleto.asiento, boleto.ruta_nombre)"
                                                class="inline-block px-3 py-1 rounded text-sm bg-red-600 text-white hover:bg-red-700"
                                            >
                                                Cancelar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-sm" style="color: var(--text-secondary)">
                                No hay boletos pendientes
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Boletos Pagados -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <span :class="getEstadoPagoColor('Pagado')" class="px-3 py-1 text-sm rounded-full font-medium mr-3">
                                Pagado
                            </span>
                            <span class="text-sm font-normal" style="color: var(--text-secondary)">
                                ({{ boletosPagados.length }})
                            </span>
                        </h3>
                        <div v-if="boletosPagados.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead>
                                    <tr style="background-color: var(--header-bg)">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Ruta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Fecha Salida
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Asiento
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Cliente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Precio
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Método Pago
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="boleto in boletosPagados" :key="boleto.id">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ boleto.ruta_nombre }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                {{ boleto.origen }} → {{ boleto.destino }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ formatearFecha(boleto.fecha_salida) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold" style="color: var(--text-primary)">
                                                # {{ boleto.asiento }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ boleto.cliente_nombre }} {{ boleto.cliente_apellido }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                CI: {{ boleto.cliente_ci }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                Bs {{ parseFloat(boleto.precio).toFixed(2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="boleto.metodo_pago === 'QR'">
                                                <span class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700 font-medium">
                                                    QR
                                                </span>
                                            </div>
                                            <div v-else>
                                                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700 font-medium">
                                                    Efectivo
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                            <a
                                                :href="route('boletos.show', boleto.id)"
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
                                No hay boletos pagados
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Boletos Cancelados -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <span :class="getEstadoPagoColor('Cancelado')" class="px-3 py-1 text-sm rounded-full font-medium mr-3">
                                Cancelado
                            </span>
                            <span class="text-sm font-normal" style="color: var(--text-secondary)">
                                ({{ boletosCancelados.length }})
                            </span>
                        </h3>
                        <div v-if="boletosCancelados.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead>
                                    <tr style="background-color: var(--header-bg)">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Ruta
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Fecha Salida
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Asiento
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Cliente
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Precio
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Método Pago
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="boleto in boletosCancelados" :key="boleto.id">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ boleto.ruta_nombre }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                {{ boleto.origen }} → {{ boleto.destino }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ formatearFecha(boleto.fecha_salida) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold" style="color: var(--text-primary)">
                                                # {{ boleto.asiento }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ boleto.cliente_nombre }} {{ boleto.cliente_apellido }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                CI: {{ boleto.cliente_ci }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                Bs {{ parseFloat(boleto.precio).toFixed(2) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="boleto.metodo_pago === 'QR'">
                                                <span class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700 font-medium">
                                                    QR
                                                </span>
                                            </div>
                                            <div v-else>
                                                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700 font-medium">
                                                    Efectivo
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                            <a
                                                :href="route('boletos.show', boleto.id)"
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
                                No hay boletos cancelados
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
