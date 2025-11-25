<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const activeClasses = computed(() => ({
    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out': true,
    'border-primary-500 text-primary-600 focus:border-primary-700': props.active,
}));

const inactiveClasses = computed(() => ({
    'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out': true,
    'hover:border-gray-300 focus:border-gray-300': true,
}));

const activeStyle = computed(() => ({
    borderColor: props.active ? 'var(--primary-500)' : 'transparent',
    color: props.active ? 'var(--primary-600)' : 'var(--text-secondary)',
}));

const handleMouseEnter = (event) => {
    if (!props.active) {
        event.target.style.color = 'var(--text-primary)';
        event.target.style.borderColor = 'var(--border-color)';
    }
};

const handleMouseLeave = (event) => {
    if (!props.active) {
        event.target.style.color = 'var(--text-secondary)';
        event.target.style.borderColor = 'transparent';
    }
};

const handleFocus = (event) => {
    if (!props.active) {
        event.target.style.color = 'var(--text-primary)';
        event.target.style.borderColor = 'var(--border-color)';
    }
};

const handleBlur = (event) => {
    if (!props.active) {
        event.target.style.color = 'var(--text-secondary)';
        event.target.style.borderColor = 'transparent';
    }
};
</script>

<template>
    <Link 
        :href="href" 
        :class="props.active ? activeClasses : inactiveClasses"
        :style="activeStyle"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
        @focus="handleFocus"
        @blur="handleBlur"
    >
        <slot />
    </Link>
</template>
