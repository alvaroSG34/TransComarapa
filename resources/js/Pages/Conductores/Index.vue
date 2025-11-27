<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    conductores: Array
});

// Log de las rutas de imágenes para debugging
onMounted(() => {
    console.log('=== RUTAS DE IMÁGENES DE CONDUCTORES ===');
    props.conductores.forEach((conductor, index) => {
        console.log(`Conductor ${index + 1}: ${conductor.nombre} ${conductor.apellido}`);
        console.log(`  - img_url (BD): ${conductor.img_url || 'null'}`);
        console.log(`  - img_url_full (generada): ${conductor.img_url_full || 'null'}`);
        console.log('---');
    });
    console.log('========================================');
});

const eliminarConductor = (id, nombre, apellido) => {
    if (confirm(`¿Está seguro de eliminar al conductor ${nombre} ${apellido}?`)) {
        router.delete(route('conductores.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Conductores" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                    Conductores
                </h2>
                <a
                    :href="route('conductores.create')"
                    class="px-4 py-2 rounded-md text-sm font-medium"
                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                >
                    + Nuevo Conductor
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- DEBUG: Info de URLs de imágenes -->
                <div class="mb-4 p-4 rounded-lg" style="background-color: #1e293b; color: #e2e8f0; font-family: monospace; font-size: 12px;">
                    <div class="font-bold mb-2">🔍 DEBUG - Rutas de Imágenes:</div>
                    <div v-for="(conductor, index) in conductores" :key="conductor.id" class="mb-2 border-b border-gray-600 pb-2">
                        <div><strong>{{ index + 1 }}. {{ conductor.nombre }} {{ conductor.apellido }}</strong></div>
                        <div class="pl-4">
                            <div>📁 img_url (BD): <span class="text-yellow-300">{{ conductor.img_url || 'null' }}</span></div>
                            <div>🌐 img_url_full (generada): <span class="text-green-300">{{ conductor.img_url_full || 'null' }}</span></div>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <!-- Lista de Conductores -->
                        <div v-if="conductores.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead>
                                    <tr style="background-color: var(--header-bg)">
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Foto
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Nombre Completo
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            CI
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Teléfono
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Correo
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="conductor in conductores" :key="conductor.id" class="hover:bg-opacity-50" style="background-color: var(--card-bg)">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-16 w-16 flex-shrink-0 relative">
                                                    <img
                                                        v-if="conductor.img_url_full"
                                                        :src="conductor.img_url_full"
                                                        :alt="conductor.nombre + ' ' + conductor.apellido"
                                                        class="h-16 w-16 rounded-full object-cover border-2"
                                                        style="border-color: var(--border-color)"
                                                        @error="$event.target.style.display='none'"
                                                    />
                                                    <div
                                                        v-if="!conductor.img_url_full"
                                                        class="h-16 w-16 rounded-full flex items-center justify-center"
                                                        style="background-color: var(--input-bg)"
                                                    >
                                                        <svg class="h-6 w-6" style="color: var(--text-secondary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ conductor.nombre }} {{ conductor.apellido }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ conductor.ci }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" style="color: var(--text-primary)">
                                                {{ conductor.telefono || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm" style="color: var(--text-secondary)">
                                                {{ conductor.correo }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a
                                                :href="route('conductores.edit', conductor.id)"
                                                class="inline-block px-3 py-1 rounded text-sm"
                                                style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                            >
                                                Editar
                                            </a>
                                            <button
                                                @click="eliminarConductor(conductor.id, conductor.nombre, conductor.apellido)"
                                                class="inline-block px-3 py-1 rounded text-sm bg-red-600 text-white hover:bg-red-700"
                                            >
                                                Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Estado Vacío -->
                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 opacity-50" style="color: var(--text-secondary)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium" style="color: var(--text-primary)">
                                No hay conductores registrados
                            </h3>
                            <p class="mt-1 text-sm" style="color: var(--text-secondary)">
                                Comienza registrando un nuevo conductor.
                            </p>
                            <div class="mt-6">
                                <a
                                    :href="route('conductores.create')"
                                    class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                >
                                    + Nuevo Conductor
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
