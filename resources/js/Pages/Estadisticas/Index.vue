<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    kpis: Object,
    rutas_top: Array,
    vehiculos_top: Array,
    grafico_datos: Array,
    filtros: Object
});

const tabActiva = ref('resumen');
const chartLinea = ref(null);
const chartTorta = ref(null);
let lineChartInstance = null;
let pieChartInstance = null;

const filtrosLocales = ref({
    fecha_inicio: props.filtros.fecha_inicio,
    fecha_fin: props.filtros.fecha_fin
});

const aplicarFiltros = () => {
    router.get(route('estadisticas.index'), filtrosLocales.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const setFiltroRapido = (tipo) => {
    const hoy = new Date();
    let inicio, fin;
    
    switch(tipo) {
        case 'hoy':
            inicio = fin = hoy.toISOString().split('T')[0];
            break;
        case 'semana':
            const primerDia = new Date(hoy.setDate(hoy.getDate() - hoy.getDay()));
            inicio = primerDia.toISOString().split('T')[0];
            fin = new Date().toISOString().split('T')[0];
            break;
        case 'mes':
            inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
            fin = new Date().toISOString().split('T')[0];
            break;
    }
    
    filtrosLocales.value.fecha_inicio = inicio;
    filtrosLocales.value.fecha_fin = fin;
    aplicarFiltros();
};

const imprimirReporte = () => {
    window.print();
};

const formatearMoneda = (valor) => {
    return `Bs ${parseFloat(valor).toFixed(2)}`;
};

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-BO', { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatearRangoFechas = () => {
    if (!filtrosLocales.value.fecha_inicio || !filtrosLocales.value.fecha_fin) return '';
    return `${formatearFecha(filtrosLocales.value.fecha_inicio)} - ${formatearFecha(filtrosLocales.value.fecha_fin)}`;
};

const calcularPorcentaje = (valor, maximo) => {
    if (!maximo || maximo === 0) return 0;
    return Math.round((valor / maximo) * 100);
};

const getColorForIndex = (index) => {
    const colors = ['#8b5cf6', '#06b6d4', '#f59e0b', '#10b981', '#ef4444'];
    return colors[index % colors.length];
};

const crearGraficos = () => {
    nextTick(() => {
        // Gráfico de líneas
        if (chartLinea.value && !lineChartInstance) {
            const ctx = chartLinea.value.getContext('2d');
            lineChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: props.grafico_datos.map(d => new Date(d.fecha).toLocaleDateString('es-BO', { day: '2-digit', month: 'short' })),
                    datasets: [
                        {
                            label: 'Boletos',
                            data: props.grafico_datos.map(d => d.boleto),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.08)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Encomiendas',
                            data: props.grafico_datos.map(d => d.encomienda),
                            borderColor: '#a855f7',
                            backgroundColor: 'rgba(168, 85, 247, 0.08)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#a855f7',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 13
                            },
                            bodyFont: {
                                size: 12
                            },
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Bs ' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                color: '#6B7280',
                                callback: function(value) {
                                    return 'Bs ' + value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                color: '#6B7280'
                            }
                        }
                    }
                }
            });
        }
        
        // Gráfico de torta
        if (chartTorta.value && !pieChartInstance) {
            const ctx = chartTorta.value.getContext('2d');
            pieChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Boletos', 'Encomiendas'],
                    datasets: [{
                        data: [props.kpis.total_boletos, props.kpis.total_encomiendas],
                        backgroundColor: ['#3b82f6', '#a855f7'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 13
                            },
                            bodyFont: {
                                size: 12
                            },
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const porcentaje = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': Bs ' + context.parsed.toFixed(2) + ' (' + porcentaje + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
};

const handleButtonHover = (event) => {
    event.target.style.backgroundColor = 'var(--primary-500)';
    event.target.style.color = 'white';
    event.target.style.borderColor = 'var(--primary-500)';
};

const handleButtonLeave = (event) => {
    event.target.style.backgroundColor = 'var(--card-bg)';
    event.target.style.color = 'var(--text-primary)';
    event.target.style.borderColor = 'var(--border-color)';
};

onMounted(() => {
    crearGraficos();
});
</script>

<template>
    <Head title="Reportes y Estadísticas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-2xl" style="color: var(--text-primary)">
                    Reportes y Estadísticas
                </h2>
                <div class="flex items-center gap-4 print:hidden">
                    <!-- Atajos Rápidos -->
                    <div class="flex gap-2">
                        <button
                            @click="setFiltroRapido('hoy')"
                            class="px-3 py-1.5 text-sm rounded-md transition-all duration-200 hover:scale-105"
                            style="background-color: var(--card-bg); color: var(--text-primary); border: 1px solid var(--border-color);"
                            @mouseenter="handleButtonHover"
                            @mouseleave="handleButtonLeave"
                        >
                            Hoy
                        </button>
                        <button
                            @click="setFiltroRapido('semana')"
                            class="px-3 py-1.5 text-sm rounded-md transition-all duration-200 hover:scale-105"
                            style="background-color: var(--card-bg); color: var(--text-primary); border: 1px solid var(--border-color);"
                            @mouseenter="handleButtonHover"
                            @mouseleave="handleButtonLeave"
                        >
                            Esta Semana
                        </button>
                        <button
                            @click="setFiltroRapido('mes')"
                            class="px-3 py-1.5 text-sm rounded-md transition-all duration-200 hover:scale-105"
                            style="background-color: var(--card-bg); color: var(--text-primary); border: 1px solid var(--border-color);"
                            @mouseenter="handleButtonHover"
                            @mouseleave="handleButtonLeave"
                        >
                            Este Mes
                        </button>
                    </div>
                    
                    <button
                        @click="imprimirReporte"
                        class="px-4 py-2 rounded-md font-medium flex items-center gap-2 transition-all duration-200 hover:scale-105"
                        style="background-color: var(--button-primary-bg); color: var(--button-primary-text);"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Exportar PDF
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Grid Principal -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Columna Principal (2/3) -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Gráfico Principal de Ingresos -->
                        <div class="bg-white rounded-lg shadow-sm p-6" style="background-color: var(--card-bg)">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-base font-medium" style="color: var(--text-primary)">Ingresos Totales</h3>
                                    <p class="text-xs mt-1" style="color: var(--text-secondary)">
                                        desde {{ formatearFecha(filtrosLocales.fecha_inicio) }} 
                                        <span class="mx-1">•</span>
                                        hasta {{ formatearFecha(filtrosLocales.fecha_fin) }}
                                    </p>
                                </div>
                                <div class="flex gap-2 print:hidden">
                                    <input 
                                        type="date" 
                                        v-model="filtrosLocales.fecha_inicio" 
                                        @change="aplicarFiltros"
                                        class="text-xs px-2 py-1 rounded border" 
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    >
                                    <input 
                                        type="date" 
                                        v-model="filtrosLocales.fecha_fin" 
                                        @change="aplicarFiltros"
                                        class="text-xs px-2 py-1 rounded border" 
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    >
                                </div>
                            </div>
                            
                            <!-- Valor destacado -->
                            <div class="mb-6">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-semibold" style="color: var(--text-primary)">{{ formatearMoneda(kpis.total_general) }}</span>
                                </div>
                            </div>
                            
                            <!-- Gráfico -->
                            <div style="height: 280px;">
                                <canvas ref="chartLinea"></canvas>
                            </div>
                        </div>

                        <!-- Sección de Rankings (2 columnas) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Rutas Más Usadas -->
                            <div class="bg-white rounded-lg shadow-sm p-6" style="background-color: var(--card-bg)">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-base font-medium" style="color: var(--text-primary)">Rutas Más Usadas</h3>
                                    <span class="text-xs" style="color: var(--text-secondary)">Mes actual</span>
                                </div>
                                
                                <div v-if="rutas_top.length > 0" class="space-y-4">
                                    <div v-for="(ruta, index) in rutas_top.slice(0, 4)" :key="index">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-3">
                                                <div 
                                                    class="w-10 h-10 rounded-full flex items-center justify-center text-white font-medium"
                                                    :style="{ backgroundColor: getColorForIndex(index) }"
                                                >
                                                    {{ index + 1 }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium" style="color: var(--text-primary)">{{ ruta.nombre_ruta }}</p>
                                                    <p class="text-xs" style="color: var(--text-secondary)">{{ ruta.total_ventas }} ventas</p>
                                                </div>
                                            </div>
                                            <span class="text-sm font-semibold" style="color: var(--text-primary)">{{ formatearMoneda(ruta.total_ingresos) }}</span>
                                        </div>
                                        <!-- Barra de progreso -->
                                        <div class="w-full rounded-full h-1.5" style="background-color: var(--card-bg);">
                                            <div 
                                                class="h-1.5 rounded-full transition-all" 
                                                :style="{ 
                                                    width: calcularPorcentaje(ruta.total_ingresos, rutas_top[0].total_ingresos) + '%',
                                                    backgroundColor: getColorForIndex(index)
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else class="text-center py-8" style="color: var(--text-secondary);">
                                    <div class="text-4xl mb-2">🛣️</div>
                                    <p class="text-sm">Sin datos</p>
                                </div>
                            </div>

                            <!-- Vehículos con Más Viajes -->
                            <div class="bg-white rounded-lg shadow-sm p-6" style="background-color: var(--card-bg)">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-base font-medium" style="color: var(--text-primary)">Vehículos Más Activos</h3>
                                    <span class="text-xs" style="color: var(--text-secondary)">Mes actual</span>
                                </div>
                                
                                <div v-if="vehiculos_top.length > 0" class="space-y-4">
                                    <div v-for="(vehiculo, index) in vehiculos_top.slice(0, 4)" :key="index">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-3">
                                                <div 
                                                    class="w-10 h-10 rounded-full flex items-center justify-center text-white font-medium"
                                                    :style="{ backgroundColor: getColorForIndex(index) }"
                                                >
                                                    {{ index + 1 }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium" style="color: var(--text-primary)">{{ vehiculo.placa }}</p>
                                                    <p class="text-xs" style="color: var(--text-secondary)">{{ vehiculo.marca }}</p>
                                                </div>
                                            </div>
                                            <span class="text-sm font-semibold" style="color: var(--text-primary)">{{ vehiculo.total_viajes }} viajes</span>
                                        </div>
                                        <!-- Barra de progreso -->
                                        <div class="w-full rounded-full h-1.5" style="background-color: var(--card-bg);">
                                            <div 
                                                class="h-1.5 rounded-full transition-all" 
                                                :style="{ 
                                                    width: calcularPorcentaje(vehiculo.total_viajes, vehiculos_top[0].total_viajes) + '%',
                                                    backgroundColor: getColorForIndex(index)
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else class="text-center py-8" style="color: var(--text-secondary);">
                                    <div class="text-4xl mb-2">🚌</div>
                                    <p class="text-sm">Sin datos</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Columna Lateral (1/3) -->
                    <div class="space-y-6">
                        
                        <!-- Tarjeta de Resumen -->
                        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-sm p-6 text-white">
                            <h3 class="text-sm font-medium opacity-90 mb-2">Periodo Seleccionado</h3>
                            <p class="text-xs opacity-75 mb-4">{{ formatearRangoFechas() }}</p>
                            
                            <div class="space-y-3">
                                <div class="bg-white bg-opacity-10 rounded-lg p-3">
                                    <div class="text-xs opacity-75">Ventas Totales</div>
                                    <div class="text-2xl font-bold">{{ kpis.cantidad_boletos + kpis.cantidad_encomiendas }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- KPIs Compactos -->
                        <div class="space-y-3">
                            <div class="bg-white rounded-lg shadow-sm p-4" style="background-color: var(--card-bg)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs" style="color: var(--text-secondary)">Boletos</p>
                                        <p class="text-xl font-semibold mt-1 text-blue-600">{{ formatearMoneda(kpis.total_boletos) }}</p>
                                        <p class="text-xs mt-1" style="color: var(--text-secondary)">{{ kpis.cantidad_boletos }} vendidos</p>
                                    </div>
                                    <div class="text-3xl">🎫</div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg shadow-sm p-4" style="background-color: var(--card-bg)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs" style="color: var(--text-secondary)">Encomiendas</p>
                                        <p class="text-xl font-semibold mt-1 text-purple-600">{{ formatearMoneda(kpis.total_encomiendas) }}</p>
                                        <p class="text-xs mt-1" style="color: var(--text-secondary)">{{ kpis.cantidad_encomiendas }} paquetes</p>
                                    </div>
                                    <div class="text-3xl">📦</div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico de Torta -->
                        <div class="bg-white rounded-lg shadow-sm p-6" style="background-color: var(--card-bg)">
                            <h3 class="text-base font-medium mb-4" style="color: var(--text-primary)">Distribución de Ventas</h3>
                            <div style="height: 220px;">
                                <canvas ref="chartTorta"></canvas>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Diseño moderno y limpio */
:deep(.bg-white) {
    transition: all 0.3s ease;
}

:deep(.bg-white:hover) {
    transform: translateY(-2px);
}

/* Animaciones suaves */
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}

/* Mejoras en barras de progreso - ahora usa variables del tema */

@media print {
    .print\:hidden {
        display: none !important;
    }
    
    :deep(.bg-white) {
        box-shadow: none !important;
    }
}
</style>
