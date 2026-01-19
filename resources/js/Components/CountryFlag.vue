<script setup>
import { computed } from 'vue';
import { getPaisPorNombre } from '@/utils/paises.js';

const props = defineProps({
    country: {
        type: String,
        required: true,
    },
    showName: {
        type: Boolean,
        default: true,
    },
    showCurrency: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg, xl
        validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
    },
    squared: {
        type: Boolean,
        default: false, // true = cuadrada, false = rectangular
    },
});

const pais = computed(() => getPaisPorNombre(props.country));

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'text-sm',
        md: 'text-base',
        lg: 'text-lg',
        xl: 'text-xl',
    };
    return sizes[props.size];
});

const flagSizeStyle = computed(() => {
    const sizes = {
        sm: '1em',
        md: '1.25em',
        lg: '1.5em',
        xl: '2em',
    };
    return {
        width: sizes[props.size],
        height: props.squared ? sizes[props.size] : `calc(${sizes[props.size]} * 0.75)`,
    };
});

const flagClass = computed(() => {
    if (!pais.value) return '';
    const baseClass = `fi fi-${pais.value.iso.toLowerCase()}`;
    return props.squared ? `${baseClass} fis` : baseClass;
});
</script>

<template>
    <span v-if="pais" :class="['inline-flex items-center gap-2', sizeClasses]">
        <span 
            :class="flagClass"
            :style="flagSizeStyle"
            class="flex-shrink-0"
        ></span>
        <span v-if="showName" class="whitespace-nowrap">{{ pais.nombre }}</span>
        <span v-if="showCurrency" class="text-muted whitespace-nowrap" style="color: var(--text-secondary);">
            ({{ pais.simbolo }} {{ pais.moneda }})
        </span>
    </span>
    <span v-else :class="sizeClasses">{{ country }}</span>
</template>

<style scoped>
/* Asegura que las banderas se alineen correctamente */
.fi {
    display: inline-block;
    vertical-align: middle;
    border-radius: 2px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}
</style>
