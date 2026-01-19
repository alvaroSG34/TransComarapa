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
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const paisSeleccionado = computed(() => {
    return props.paises.find(p => p.codigo === props.modelValue) || null;
});

const handleChange = (codigo) => {
    emit('update:modelValue', codigo);
    emit('change', codigo);
};
</script>

<template>
    <Listbox :modelValue="modelValue" @update:modelValue="handleChange">
        <div class="relative">
            <ListboxButton
                class="relative w-[120px] cursor-pointer rounded-lg py-3 pl-10 pr-8 text-left border transition-all focus:outline-none focus:ring-2 text-xs"
                style="
                    background-color: var(--bg-secondary);
                    color: var(--text-primary);
                    border-color: var(--border-primary);
                "
            >
                <!-- Bandera seleccionada -->
                <span
                    v-if="paisSeleccionado"
                    :class="`fi fi-${paisSeleccionado.iso?.toLowerCase()}`"
                    class="absolute left-2 top-1/2 -translate-y-1/2 text-xl"
                    aria-hidden="true"
                ></span>

                <!-- Código seleccionado -->
                <span v-if="paisSeleccionado" class="block truncate">
                    {{ paisSeleccionado.codigo }}
                </span>

                <!-- Icono flecha -->
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <ChevronUpDownIcon class="h-4 w-4" style="color: var(--text-secondary);" aria-hidden="true" />
                </span>
            </ListboxButton>

            <!-- Opciones dropdown -->
            <transition
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <ListboxOptions
                    class="absolute z-50 mt-1 max-h-72 w-80 overflow-auto rounded-lg py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                    style="background-color: var(--bg-secondary);"
                >
                    <ListboxOption
                        v-slot="{ active, selected }"
                        v-for="pais in paises"
                        :key="pais.codigo"
                        :value="pais.codigo"
                        as="template"
                    >
                        <li
                            :class="[
                                active ? 'bg-primary-100 dark:bg-primary-900/20' : '',
                                'relative cursor-pointer select-none py-2.5 pl-3 pr-9 transition-colors'
                            ]"
                            style="color: var(--text-primary);"
                        >
                            <!-- Layout: Código + Bandera + Nombre -->
                            <div class="flex items-center gap-3">
                                <!-- Código (ancho fijo) -->
                                <span :class="[selected ? 'font-semibold' : 'font-normal', 'text-xs w-12 flex-shrink-0']">
                                    {{ pais.codigo }}
                                </span>
                                
                                <!-- Bandera -->
                                <span
                                    :class="`fi fi-${pais.iso?.toLowerCase()}`"
                                    class="text-xl flex-shrink-0"
                                    aria-hidden="true"
                                ></span>
                                
                                <!-- Nombre del país -->
                                <span class="text-xs opacity-70 truncate flex-1">
                                    {{ pais.nombre }}
                                </span>
                            </div>

                            <!-- Check si está seleccionado -->
                            <span
                                v-if="selected"
                                class="absolute inset-y-0 right-0 flex items-center pr-2"
                                style="color: var(--primary-600);"
                            >
                                <CheckIcon class="h-4 w-4" aria-hidden="true" />
                            </span>
                        </li>
                    </ListboxOption>
                </ListboxOptions>
            </transition>
        </div>
    </Listbox>
</template>
