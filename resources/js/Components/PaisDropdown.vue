<script setup>
import { computed } from 'vue';
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    modelValue: {
        type: String,
        required: true
    },
    paises: {
        type: Array,
        required: true
    },
    error: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const paisSeleccionado = computed(() => {
    return props.paises.find(p => p.nombre === props.modelValue) || null;
});

const handleChange = (nombrePais) => {
    emit('update:modelValue', nombrePais);
    emit('change', nombrePais);
};
</script>

<template>
    <Listbox :modelValue="modelValue" @update:modelValue="handleChange">
        <div class="relative">
            <ListboxButton
                class="relative w-full cursor-pointer rounded-lg py-3 px-3 pr-10 text-left transition-all focus:outline-none focus:ring-2 border"
                :class="error ? 'border-red-500 focus:ring-red-500' : ''"
                style="
                    background-color: var(--bg-secondary);
                    color: var(--text-primary);
                    border-color: var(--border-primary);
                "
            >
                <!-- Layout horizontal: Bandera + Texto -->
                <div class="flex items-center gap-3">
                    <!-- Bandera seleccionada -->
                    <span
                        v-if="paisSeleccionado"
                        :class="`fi fi-${paisSeleccionado.iso?.toLowerCase()}`"
                        class="text-2xl flex-shrink-0"
                        aria-hidden="true"
                    ></span>

                    <!-- Texto seleccionado -->
                    <span v-if="paisSeleccionado" class="block truncate">
                        {{ paisSeleccionado.nombre }}
                    </span>
                    <span v-else class="block truncate" style="color: var(--text-secondary);">
                        Selecciona un país
                    </span>
                </div>

                <!-- Icono flecha -->
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <ChevronUpDownIcon class="h-5 w-5" style="color: var(--text-secondary);" aria-hidden="true" />
                </span>
            </ListboxButton>

            <!-- Opciones dropdown -->
            <transition
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <ListboxOptions
                    class="absolute z-50 mt-1 max-h-72 w-full overflow-auto rounded-lg py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                    style="background-color: var(--bg-secondary);"
                >
                    <ListboxOption
                        v-slot="{ active, selected }"
                        v-for="pais in paises"
                        :key="pais.iso"
                        :value="pais.nombre"
                        as="template"
                    >
                        <li
                            :class="[
                                active ? 'bg-primary-100 dark:bg-primary-900/20' : '',
                                'relative cursor-pointer select-none py-3 pl-3 pr-9 transition-colors'
                            ]"
                            style="color: var(--text-primary);"
                        >
                            <!-- Layout horizontal: Bandera + Nombre -->
                            <div class="flex items-center gap-3">
                                <!-- Bandera -->
                                <span
                                    :class="`fi fi-${pais.iso?.toLowerCase()}`"
                                    class="text-2xl flex-shrink-0"
                                    aria-hidden="true"
                                ></span>

                                <!-- Nombre del país -->
                                <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">
                                    {{ pais.nombre }}
                                </span>
                            </div>

                            <!-- Check si está seleccionado -->
                            <span
                                v-if="selected"
                                class="absolute inset-y-0 right-0 flex items-center pr-3"
                                style="color: var(--primary-600);"
                            >
                                <CheckIcon class="h-5 w-5" aria-hidden="true" />
                            </span>
                        </li>
                    </ListboxOption>
                </ListboxOptions>
            </transition>
        </div>
    </Listbox>
</template>
