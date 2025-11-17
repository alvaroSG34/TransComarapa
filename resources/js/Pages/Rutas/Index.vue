<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    rutas: {
        type: Array,
        required: true
    }
});

const deleteRuta = (id) => {
    if (confirm('¿Estás seguro de eliminar esta ruta?')) {
        router.delete(route('rutas.destroy', id));
    }
};
</script>

<template>
    <Head title="Gestionar Rutas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight" style="color: var(--text-primary)">
                    Gestionar Rutas
                </h2>
                <Link
                    :href="route('rutas.create')"
                    class="px-4 py-2 rounded-lg text-white font-semibold transition-all hover:opacity-90"
                    style="background-color: var(--primary-600);"
                >
                    + Nueva Ruta
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--bg-secondary);">
                    <div class="p-6">
                        <!-- Tabla de rutas -->
                        <div v-if="rutas.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-primary);">
                                <thead>
                                    <tr style="background-color: var(--bg-primary);">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">
                                            ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">
                                            Nombre
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">
                                            Origen
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">
                                            Destino
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-primary);">
                                    <tr v-for="ruta in rutas" :key="ruta.id" class="transition-colors" style="background-color: var(--bg-secondary); &:hover { background-color: var(--bg-primary); }">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-primary);">
                                            {{ ruta.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary);">{{ ruta.nombre }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-secondary);">
                                            {{ ruta.origen }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-secondary);">
                                            {{ ruta.destino }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2">
                                                <Link
                                                    :href="route('rutas.edit', ruta.id)"
                                                    class="px-3 py-1 rounded text-white transition-all hover:opacity-80"
                                                    style="background-color: var(--secondary-600);"
                                                >
                                                    Editar
                                                </Link>
                                                <button
                                                    @click="deleteRuta(ruta.id)"
                                                    class="px-3 py-1 rounded text-white transition-all hover:opacity-80"
                                                    style="background-color: #dc2626;"
                                                >
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Sin rutas -->
                        <div v-else class="text-center py-12">
                            <div class="text-6xl mb-4">🗺️</div>
                            <h3 class="text-lg font-semibold mb-2" style="color: var(--text-primary);">
                                No hay rutas registradas
                            </h3>
                            <p class="mb-4" style="color: var(--text-secondary);">
                                Comienza creando tu primera ruta
                            </p>
                            <Link
                                :href="route('rutas.create')"
                                class="inline-block px-4 py-2 rounded-lg text-white font-semibold transition-all hover:opacity-90"
                                style="background-color: var(--primary-600);"
                            >
                                + Nueva Ruta
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
