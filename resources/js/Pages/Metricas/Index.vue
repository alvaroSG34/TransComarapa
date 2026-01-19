<script setup>
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';

// Registrar todos los componentes de Chart.js
Chart.register(...registerables);

const props = defineProps({
    totalVisitas: Number,
    visitasRango: Number,
    visitasPorDia: Array,
    visitasPorRuta: Array,
    todasLasRutas: Array,
    filtros: Object,
});

// Filtros
const fechaInicio = ref(props.filtros.fecha_inicio);
const fechaFin = ref(props.filtros.fecha_fin);

// Búsqueda en tabla
const busqueda = ref('');

// Gráfico
const chartCanvas = ref(null);
const barChartCanvas = ref(null);
let chartInstance = null;
let barChartInstance = null;

// Rutas importantes para análisis
const rutasImportantes = ['/', '//', '/login', '/register', '/dashboard'];

// Tabla filtrada - solo rutas importantes
const rutasPrincipales = computed(() => {
    return props.todasLasRutas.filter(item => 
        rutasImportantes.includes(item.ruta)
    );
});

// Métricas calculadas
const metricas = computed(() => {
    if (!props.visitasPorDia.length) {
        return {
            promedioPorDia: 0,
            diaPico: { fecha: '-', total: 0 },
            diaMenor: { fecha: '-', total: 0 }
        };
    }

    const totales = props.visitasPorDia.map(d => d.total);
    const suma = totales.reduce((a, b) => a + b, 0);
    const promedio = Math.round(suma / props.visitasPorDia.length);
    
    const maximo = Math.max(...totales);
    const minimo = Math.min(...totales);
    
    const diaPico = props.visitasPorDia.find(d => d.total === maximo);
    const diaMenor = props.visitasPorDia.find(d => d.total === minimo);
    
    return {
        promedioPorDia: promedio,
        diaPico: diaPico || { fecha: '-', total: 0 },
        diaMenor: diaMenor || { fecha: '-', total: 0 }
    };
});

// Aplicar filtros de fecha
const aplicarFiltros = () => {
    router.get(route('metricas.index'), {
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Establecer filtros rápidos
const establecerFiltroRapido = (dias) => {
    fechaFin.value = new Date().toISOString().split('T')[0];
    const inicio = new Date();
    inicio.setDate(inicio.getDate() - dias);
    fechaInicio.value = inicio.toISOString().split('T')[0];
    aplicarFiltros();
};

// Crear gráfico de línea
const crearGrafico = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }

    const ctx = chartCanvas.value.getContext('2d');
    
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.visitasPorDia.map(item => item.fecha),
            datasets: [{
                label: 'Visitas por día',
                data: props.visitasPorDia.map(item => item.total),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
};

// Crear gráfico de barras para top 20
const crearGraficoBarras = () => {
    if (barChartInstance) {
        barChartInstance.destroy();
    }

    const ctx = barChartCanvas.value.getContext('2d');
    
    barChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: props.visitasPorRuta.map(item => item.ruta),
            datasets: [{
                label: 'Visitas',
                data: props.visitasPorRuta.map(item => item.total),
                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Visitas: ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
};

// Exportar datos a CSV
const exportarCSV = () => {
    const headers = ['Ruta', 'Visitas'];
    const rows = rutasPrincipales.value.map(item => [item.ruta, item.total]);
    
    let csvContent = headers.join(',') + '\n';
    rows.forEach(row => {
        csvContent += row.join(',') + '\n';
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `metricas_rutas_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

onMounted(() => {
    crearGrafico();
    crearGraficoBarras();
});
</script>

<template>
    <Head title="Métricas de Visitas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight" style="color: var(--text-primary)">
                📈 Métricas de Visitas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filtros de Fecha -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg p-6" style="background-color: var(--bg-secondary);">
                    <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">
                        Filtros de Fecha
                    </h3>
                    
                    <!-- Filtros Rápidos -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button
                            @click="establecerFiltroRapido(7)"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all hover:opacity-90"
                            style="background-color: var(--primary-600); color: white;"
                        >
                            Últimos 7 días
                        </button>
                        <button
                            @click="establecerFiltroRapido(30)"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all hover:opacity-90"
                            style="background-color: var(--primary-600); color: white;"
                        >
                            Últimos 30 días
                        </button>
                        <button
                            @click="establecerFiltroRapido(90)"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all hover:opacity-90"
                            style="background-color: var(--primary-600); color: white;"
                        >
                            Últimos 90 días
                        </button>
                    </div>

                    <!-- Filtros Personalizados -->
                    <div class="flex flex-wrap gap-4 items-end">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                                Fecha Inicio
                            </label>
                            <input
                                type="date"
                                v-model="fechaInicio"
                                class="w-full px-4 py-2 rounded-lg border"
                                style="background-color: var(--bg-primary); color: var(--text-primary); border-color: var(--border-primary);"
                            />
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium mb-2" style="color: var(--text-primary);">
                                Fecha Fin
                            </label>
                            <input
                                type="date"
                                v-model="fechaFin"
                                class="w-full px-4 py-2 rounded-lg border"
                                style="background-color: var(--bg-primary); color: var(--text-primary); border-color: var(--border-primary);"
                            />
                        </div>
                        <button
                            @click="aplicarFiltros"
                            class="px-6 py-2 rounded-lg font-semibold text-white transition-all hover:opacity-90"
                            style="background-color: var(--accent-color);"
                        >
                            Aplicar
                        </button>
                    </div>
                </div>

                <!-- Tarjetas de Resumen -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="p-6 shadow-sm sm:rounded-lg" style="background-color: var(--bg-secondary);">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium" style="color: var(--text-secondary);">Total de Visitas</p>
                                <p class="text-3xl font-bold mt-2" style="color: var(--primary-600);">
                                    {{ totalVisitas.toLocaleString() }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full" style="background-color: var(--primary-100);">
                                <svg class="w-8 h-8" style="color: var(--primary-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 shadow-sm sm:rounded-lg" style="background-color: var(--bg-secondary);">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium" style="color: var(--text-secondary);">Visitas en Rango</p>
                                <p class="text-3xl font-bold mt-2" style="color: var(--accent-color);">
                                    {{ visitasRango.toLocaleString() }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full" style="background-color: var(--accent-100);">
                                <svg class="w-8 h-8" style="color: var(--accent-color);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 shadow-sm sm:rounded-lg" style="background-color: var(--bg-secondary);">
                        <div>
                            <p class="text-sm font-medium" style="color: var(--text-secondary);">Promedio Diario</p>
                            <p class="text-3xl font-bold mt-2" style="color: var(--primary-600);">
                                {{ metricas.promedioPorDia.toLocaleString() }}
                            </p>
                            <p class="text-xs mt-2" style="color: var(--text-secondary);">visitas/día</p>
                        </div>
                    </div>

                    <div class="p-6 shadow-sm sm:rounded-lg" style="background-color: var(--bg-secondary);">
                        <div>
                            <p class="text-sm font-medium" style="color: var(--text-secondary);">Día Pico</p>
                            <p class="text-2xl font-bold mt-2" style="color: var(--accent-color);">
                                {{ metricas.diaPico.total.toLocaleString() }}
                            </p>
                            <p class="text-xs mt-2" style="color: var(--text-secondary);">{{ metricas.diaPico.fecha }}</p>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Visitas por Día -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg p-6" style="background-color: var(--bg-secondary);">
                    <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">
                        Visitas por Día
                    </h3>
                    <div class="h-[400px]">
                        <canvas ref="chartCanvas"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Top 20 Rutas -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg p-6" style="background-color: var(--bg-secondary);">
                    <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">
                        Top 20 Rutas Más Visitadas
                    </h3>
                    <div class="h-[500px]">
                        <canvas ref="barChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Tabla de Rutas Principales -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg p-6" style="background-color: var(--bg-secondary);">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                        <h3 class="text-lg font-semibold" style="color: var(--text-primary);">
                            Rutas Principales del Sistema
                        </h3>
                        <button
                            @click="exportarCSV"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all hover:opacity-90"
                            style="background-color: var(--primary-600); color: white;"
                        >
                            📥 Exportar CSV
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--border-primary);">
                                    <th class="text-left py-3 px-4 text-sm font-semibold" style="color: var(--text-primary);">Ruta</th>
                                    <th class="text-left py-3 px-4 text-sm font-semibold" style="color: var(--text-primary);">Descripción</th>
                                    <th class="text-right py-3 px-4 text-sm font-semibold" style="color: var(--text-primary);">Visitas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in rutasPrincipales"
                                    :key="item.ruta"
                                    class="border-b hover:bg-opacity-50 transition-colors"
                                    style="border-color: var(--border-primary);"
                                >
                                    <td class="py-3 px-4 text-sm font-mono font-medium" style="color: var(--primary-600);">
                                        {{ item.ruta }}
                                    </td>
                                    <td class="py-3 px-4 text-sm" style="color: var(--text-secondary);">
                                        <span v-if="item.ruta === '/' || item.ruta === '//'">Página de inicio</span>
                                        <span v-else-if="item.ruta === '/login'">Inicio de sesión</span>
                                        <span v-else-if="item.ruta === '/register'">Registro de usuarios</span>
                                        <span v-else-if="item.ruta === '/dashboard'">Panel principal</span>
                                    </td>
                                    <td class="py-3 px-4 text-sm text-right font-semibold" style="color: var(--accent-color);">
                                        {{ item.total.toLocaleString() }}
                                    </td>
                                </tr>
                                <tr v-if="rutasPrincipales.length === 0">
                                    <td colspan="3" class="py-8 text-center text-sm" style="color: var(--text-secondary);">
                                        No hay datos disponibles para las rutas principales
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
