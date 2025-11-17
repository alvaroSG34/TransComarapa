<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useTheme } from '@/composables/useTheme';
import { computed } from 'vue';

const page = usePage();
const { activeTheme, effectiveMode, isAutoMode } = useTheme();

const userName = computed(() => page.props.auth.user?.name || 'Usuario');
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight" style="color: var(--text-primary)">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Welcome Card -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--bg-secondary);">
                    <div class="p-6" style="color: var(--text-primary);">
                        <h3 class="text-2xl font-bold mb-2">¡Bienvenido, {{ userName }}! 👋</h3>
                        <p style="color: var(--text-secondary);">Has iniciado sesión correctamente en TransComarapa.</p>
                    </div>
                </div>

                <!-- Theme Info Card -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--bg-secondary);">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">
                            {{ activeTheme.icon }} Tema Activo: {{ activeTheme.name }}
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Current Theme -->
                            <div class="p-4 rounded-lg" style="background-color: var(--bg-primary); border: 1px solid var(--border-primary);">
                                <div class="text-sm font-medium mb-1" style="color: var(--text-secondary);">Tema</div>
                                <div class="text-lg font-bold" style="color: var(--primary-600);">{{ activeTheme.name }}</div>
                                <div class="text-xs mt-1" style="color: var(--text-tertiary);">{{ activeTheme.description }}</div>
                            </div>

                            <!-- Current Mode -->
                            <div class="p-4 rounded-lg" style="background-color: var(--bg-primary); border: 1px solid var(--border-primary);">
                                <div class="text-sm font-medium mb-1" style="color: var(--text-secondary);">Modo</div>
                                <div class="text-lg font-bold" style="color: var(--secondary-600);">
                                    {{ effectiveMode === 'dark' ? '🌙 Oscuro' : '☀️ Claro' }}
                                </div>
                                <div class="text-xs mt-1" style="color: var(--text-tertiary);">
                                    {{ isAutoMode ? 'Automático' : 'Manual' }}
                                </div>
                            </div>

                            <!-- Server Time -->
                            <div class="p-4 rounded-lg" style="background-color: var(--bg-primary); border: 1px solid var(--border-primary);">
                                <div class="text-sm font-medium mb-1" style="color: var(--text-secondary);">Hora Servidor</div>
                                <div class="text-lg font-bold" style="color: var(--accent-600);">
                                    {{ $page.props.currentHour }}:00
                                </div>
                                <div class="text-xs mt-1" style="color: var(--text-tertiary);">
                                    Bolivia (UTC-4)
                                </div>
                            </div>
                        </div>

                        <!-- Color Palette Preview -->
                        <div class="mt-6">
                            <h4 class="text-sm font-semibold mb-3" style="color: var(--text-primary);">Paleta de Colores</h4>
                            <div class="flex gap-2 flex-wrap">
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-lg shadow-md" style="background-color: var(--primary-500);"></div>
                                    <span class="text-xs mt-1" style="color: var(--text-tertiary);">Primario</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-lg shadow-md" style="background-color: var(--secondary-500);"></div>
                                    <span class="text-xs mt-1" style="color: var(--text-tertiary);">Secundario</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-lg shadow-md" style="background-color: var(--accent-500);"></div>
                                    <span class="text-xs mt-1" style="color: var(--text-tertiary);">Acento</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-lg shadow-md border" style="background-color: var(--bg-primary); border-color: var(--border-primary);"></div>
                                    <span class="text-xs mt-1" style="color: var(--text-tertiary);">Fondo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--bg-secondary);">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4" style="color: var(--text-primary);">
                            Acciones Rápidas
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <button class="p-4 rounded-lg text-left transition-all hover:shadow-md" style="background-color: var(--primary-500); color: white;">
                                <div class="text-2xl mb-2">🎫</div>
                                <div class="font-semibold">Vender Boleto</div>
                                <div class="text-sm opacity-90 mt-1">Registrar venta de boleto</div>
                            </button>
                            <button class="p-4 rounded-lg text-left transition-all hover:shadow-md" style="background-color: var(--secondary-500); color: white;">
                                <div class="text-2xl mb-2">📦</div>
                                <div class="font-semibold">Nueva Encomienda</div>
                                <div class="text-sm opacity-90 mt-1">Registrar encomienda</div>
                            </button>
                            <button class="p-4 rounded-lg text-left transition-all hover:shadow-md" style="background-color: var(--accent-500); color: white;">
                                <div class="text-2xl mb-2">🚌</div>
                                <div class="font-semibold">Gestionar Rutas</div>
                                <div class="text-sm opacity-90 mt-1">Ver y editar rutas</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
