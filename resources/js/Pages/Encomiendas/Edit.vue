<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useHtml5Validation } from '@/composables/useHtml5Validation';

const props = defineProps({
    encomienda: Object,
    rutas: Array,
    clientes: Array
});

useHtml5Validation();

const form = useForm({
    ruta_id: props.encomienda.ruta_id,
    cliente_id: props.encomienda.cliente_id,
    peso: props.encomienda.peso,
    descripcion: props.encomienda.descripcion || '',
    nombre_destinatario: props.encomienda.nombre_destinatario,
    img_url: props.encomienda.img_url || '',
    modalidad_pago: props.encomienda.modalidad_pago,
    precio: props.encomienda.precio
});

const rutaSeleccionada = computed(() => {
    return props.rutas.find(r => r.id === parseInt(form.ruta_id));
});

const submit = () => {
    form.put(route('encomiendas.update', props.encomienda.venta_id), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Editar Encomienda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl" style="color: var(--text-primary)">
                Editar Encomienda
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: var(--card-bg)">
                    <div class="p-6" style="color: var(--text-primary)">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Selección de Ruta -->
                            <div>
                                <label for="ruta_id" class="block text-sm font-medium mb-2">
                                    Seleccionar Ruta de Envío *
                                </label>
                                <select
                                    id="ruta_id"
                                    v-model="form.ruta_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.ruta_id }"
                                >
                                    <option value="">Seleccione una ruta</option>
                                    <option v-for="ruta in rutas" :key="ruta.id" :value="ruta.id">
                                        {{ ruta.nombre }} ({{ ruta.origen }} → {{ ruta.destino }}) - 
                                        {{ ruta.distancia }} km - {{ ruta.duracion_estimada }}
                                    </option>
                                </select>
                                <p v-if="form.errors.ruta_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.ruta_id }}
                                </p>
                            </div>

                            <!-- Información de Ruta Seleccionada -->
                            <div v-if="rutaSeleccionada" class="p-4 rounded-lg" style="background-color: var(--header-bg); border: 1px solid var(--border-color)">
                                <h3 class="text-md font-semibold mb-3" style="color: var(--text-primary)">
                                    Detalles de la Ruta
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span style="color: var(--text-secondary)">Origen:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ rutaSeleccionada.origen }}
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary)">Destino:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ rutaSeleccionada.destino }}
                                        </span>
                                    </div>
                                    <div>
                                        <span style="color: var(--text-secondary)">Duración:</span>
                                        <span class="ml-2 font-medium" style="color: var(--text-primary)">
                                            {{ rutaSeleccionada.duracion_estimada }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Selección de Cliente (Remitente) -->
                            <div>
                                <label for="cliente_id" class="block text-sm font-medium mb-2">
                                    Cliente Remitente *
                                </label>
                                <select
                                    id="cliente_id"
                                    v-model="form.cliente_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.cliente_id }"
                                >
                                    <option value="">Seleccione el cliente que envía</option>
                                    <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
                                        {{ cliente.nombre }} {{ cliente.apellido }} - CI: {{ cliente.ci }} - {{ cliente.telefono }}
                                    </option>
                                </select>
                                <p v-if="form.errors.cliente_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.cliente_id }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Persona que envía el paquete
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Peso del Paquete -->
                                <div>
                                    <label for="peso" class="block text-sm font-medium mb-2">
                                        Peso del Paquete (kg) *
                                    </label>
                                    <input
                                        id="peso"
                                        type="number"
                                        v-model="form.peso"
                                        required
                                        min="0.01"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                        :class="{ 'border-red-500': form.errors.peso }"
                                    />
                                    <p v-if="form.errors.peso" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.peso }}
                                    </p>
                                    <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                        Peso en kilogramos (mínimo 0.01 kg)
                                    </p>
                                </div>

                                <!-- Precio -->
                                <div>
                                    <label for="precio" class="block text-sm font-medium mb-2">
                                        Precio del Envío (Bs) *
                                    </label>
                                    <input
                                        id="precio"
                                        type="number"
                                        v-model="form.precio"
                                        required
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                        :class="{ 'border-red-500': form.errors.precio }"
                                    />
                                    <p v-if="form.errors.precio" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.precio }}
                                    </p>
                                    <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                        Costo total del envío en bolivianos
                                    </p>
                                </div>
                            </div>

                            <!-- Nombre del Destinatario -->
                            <div>
                                <label for="nombre_destinatario" class="block text-sm font-medium mb-2">
                                    Nombre del Destinatario *
                                </label>
                                <input
                                    id="nombre_destinatario"
                                    type="text"
                                    v-model="form.nombre_destinatario"
                                    required
                                    maxlength="150"
                                    placeholder="Nombre completo de quien recibe"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.nombre_destinatario }"
                                />
                                <p v-if="form.errors.nombre_destinatario" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.nombre_destinatario }}
                                </p>
                            </div>

                            <!-- Descripción del Paquete -->
                            <div>
                                <label for="descripcion" class="block text-sm font-medium mb-2">
                                    Descripción del Contenido
                                </label>
                                <textarea
                                    id="descripcion"
                                    v-model="form.descripcion"
                                    maxlength="500"
                                    rows="3"
                                    placeholder="Describa brevemente el contenido del paquete (opcional)"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.descripcion }"
                                ></textarea>
                                <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.descripcion }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Opcional - Máximo 500 caracteres
                                </p>
                            </div>

                            <!-- Modalidad de Pago -->
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Modalidad de Pago *
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <label class="flex items-center p-4 rounded-lg border-2 cursor-pointer transition-colors"
                                        :class="form.modalidad_pago === 'origen' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
                                        style="border-color: var(--border-color)">
                                        <input
                                            type="radio"
                                            v-model="form.modalidad_pago"
                                            value="origen"
                                            class="mr-3"
                                        />
                                        <div>
                                            <div class="font-medium" style="color: var(--text-primary)">Pago en Origen</div>
                                            <div class="text-xs" style="color: var(--text-secondary)">El remitente paga todo</div>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-4 rounded-lg border-2 cursor-pointer transition-colors"
                                        :class="form.modalidad_pago === 'mixto' ? 'border-purple-500 bg-purple-50' : 'border-gray-300'"
                                        style="border-color: var(--border-color)">
                                        <input
                                            type="radio"
                                            v-model="form.modalidad_pago"
                                            value="mixto"
                                            class="mr-3"
                                        />
                                        <div>
                                            <div class="font-medium" style="color: var(--text-primary)">Pago Mixto</div>
                                            <div class="text-xs" style="color: var(--text-secondary)">Dividido entre ambos</div>
                                        </div>
                                    </label>

                                    <label class="flex items-center p-4 rounded-lg border-2 cursor-pointer transition-colors"
                                        :class="form.modalidad_pago === 'destino' ? 'border-orange-500 bg-orange-50' : 'border-gray-300'"
                                        style="border-color: var(--border-color)">
                                        <input
                                            type="radio"
                                            v-model="form.modalidad_pago"
                                            value="destino"
                                            class="mr-3"
                                        />
                                        <div>
                                            <div class="font-medium" style="color: var(--text-primary)">Pago en Destino</div>
                                            <div class="text-xs" style="color: var(--text-secondary)">El destinatario paga</div>
                                        </div>
                                    </label>
                                </div>
                                <p v-if="form.errors.modalidad_pago" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.modalidad_pago }}
                                </p>
                            </div>

                            <!-- URL de Imagen del Paquete -->
                            <div>
                                <label for="img_url" class="block text-sm font-medium mb-2">
                                    URL de Foto del Paquete
                                </label>
                                <input
                                    id="img_url"
                                    type="url"
                                    v-model="form.img_url"
                                    maxlength="255"
                                    placeholder="https://ejemplo.com/foto-paquete.jpg"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color)"
                                    :class="{ 'border-red-500': form.errors.img_url }"
                                />
                                <p v-if="form.errors.img_url" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.img_url }}
                                </p>
                                <p v-else class="mt-1 text-sm" style="color: var(--text-secondary)">
                                    Opcional - URL de la foto del paquete para registro
                                </p>
                                
                                <!-- Vista previa de imagen -->
                                <div v-if="form.img_url" class="mt-3">
                                    <p class="text-sm font-medium mb-2">Vista previa:</p>
                                    <img :src="form.img_url" alt="Vista previa del paquete" class="h-48 w-auto object-cover rounded border" style="border-color: var(--border-color)" @error="$event.target.style.display='none'">
                                </div>
                            </div>

                            <!-- Resumen -->
                            <div v-if="form.ruta_id && form.cliente_id && form.precio" class="p-4 rounded-lg border-2" style="background-color: var(--card-bg); border-color: var(--button-primary-bg)">
                                <h3 class="text-lg font-semibold mb-3" style="color: var(--text-primary)">
                                    Resumen de Cambios
                                </h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span style="color: var(--text-secondary)">Ruta:</span>
                                        <span class="font-medium" style="color: var(--text-primary)">
                                            {{ rutaSeleccionada?.nombre }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span style="color: var(--text-secondary)">Peso:</span>
                                        <span class="font-medium" style="color: var(--text-primary)">
                                            {{ form.peso ? parseFloat(form.peso).toFixed(2) : '0.00' }} kg
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span style="color: var(--text-secondary)">Modalidad de Pago:</span>
                                        <span class="font-medium capitalize" style="color: var(--text-primary)">
                                            {{ form.modalidad_pago }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t" style="border-color: var(--border-color)">
                                        <span class="font-semibold" style="color: var(--text-primary)">Total:</span>
                                        <span class="font-bold text-lg text-green-600">
                                            Bs {{ form.precio ? parseFloat(form.precio).toFixed(2) : '0.00' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex items-center justify-end space-x-4 pt-4 border-t" style="border-color: var(--border-color)">
                                <a
                                    :href="route('encomiendas.index')"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-secondary-bg); color: var(--button-secondary-text)"
                                >
                                    Cancelar
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 rounded-md text-sm font-medium"
                                    style="background-color: var(--button-primary-bg); color: var(--button-primary-text)"
                                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                >
                                    {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
