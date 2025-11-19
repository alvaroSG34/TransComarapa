<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    clientes: Object,
    filters: Object
});

const search = ref(props.filters.search);

// Implementación simple de debounce
const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

watch(search, debounce((value) => {
    router.get(route('clientes.index'), { search: value }, {
        preserveState: true,
        replace: true
    });
}, 300));

const banearCliente = (id, nombre) => {
    if (confirm(`¿Estás seguro de banear al cliente ${nombre}? No podrá realizar nuevas compras.`)) {
        router.delete(route('clientes.destroy', id));
    }
};

const reactivarCliente = (id, nombre) => {
    if (confirm(`¿Reactivar al cliente ${nombre}?`)) {
        router.post(route('clientes.restore', id));
    }
};
</script>

<template>
    <Head title="Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight" style="color: var(--text-primary)">
                Gestión de Clientes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6">
                        <!-- Buscador -->
                        <div class="mb-6">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Buscar por nombre, apellido o CI..."
                                class="w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                            />
                        </div>

                        <!-- Tabla -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y" style="border-color: var(--border-color)">
                                <thead style="background-color: var(--header-bg)">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">CI</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">Contacto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary)">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--border-color)">
                                    <tr v-for="cliente in clientes.data" :key="cliente.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium" style="color: var(--text-primary)">
                                                {{ cliente.nombre }} {{ cliente.apellido }}
                                            </div>
                                            <div class="text-xs" style="color: var(--text-secondary)">
                                                {{ cliente.correo }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-primary)">
                                            {{ cliente.ci }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: var(--text-primary)">
                                            {{ cliente.telefono }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span v-if="cliente.deleted_at" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Baneado
                                            </span>
                                            <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Activo
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <Link
                                                :href="route('clientes.show', cliente.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Ver Historial
                                            </Link>
                                            
                                            <button
                                                v-if="!cliente.deleted_at"
                                                @click="banearCliente(cliente.id, cliente.nombre)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Banear
                                            </button>
                                            
                                            <button
                                                v-else
                                                @click="reactivarCliente(cliente.id, cliente.nombre)"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Reactivar
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div v-if="clientes.links.length > 3" class="mt-6 flex justify-center">
                            <div class="flex space-x-1">
                                <Link
                                    v-for="(link, index) in clientes.links"
                                    :key="index"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="px-3 py-1 rounded-md text-sm"
                                    :class="{
                                        'bg-indigo-600 text-white': link.active,
                                        'bg-gray-200 text-gray-700 hover:bg-gray-300': !link.active,
                                        'opacity-50 cursor-not-allowed': !link.url
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
