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

const baseClasses = computed(() => 
    'block w-full ps-3 pe-4 py-2 border-l-4 text-start text-base font-medium focus:outline-none transition duration-150 ease-in-out'
);

const activeStyle = computed(() => ({
    borderColor: 'var(--primary-500)',
    color: 'var(--primary-600)',
    backgroundColor: 'var(--card-bg)',
}));

const inactiveStyle = computed(() => ({
    borderColor: 'transparent',
    color: 'var(--text-secondary)',
    backgroundColor: 'transparent',
}));

const handleMouseEnter = (event) => {
    if (!props.active) {
        event.target.style.color = 'var(--text-primary)';
        event.target.style.backgroundColor = 'var(--card-bg)';
        event.target.style.borderColor = 'var(--border-color)';
    }
};

const handleMouseLeave = (event) => {
    if (!props.active) {
        event.target.style.color = 'var(--text-secondary)';
        event.target.style.backgroundColor = 'transparent';
        event.target.style.borderColor = 'transparent';
    }
};

const handleFocus = (event) => {
    if (!props.active) {
        event.target.style.color = 'var(--text-primary)';
        event.target.style.backgroundColor = 'var(--card-bg)';
        event.target.style.borderColor = 'var(--border-color)';
    }
};

const handleBlur = (event) => {
    if (!props.active) {
        event.target.style.color = 'var(--text-secondary)';
        event.target.style.backgroundColor = 'transparent';
        event.target.style.borderColor = 'transparent';
    }
};
</script>

<template>
    <Link 
        :href="href" 
        :class="baseClasses"
        :style="props.active ? activeStyle : inactiveStyle"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
        @focus="handleFocus"
        @blur="handleBlur"
    >
        <slot />
    </Link>
</template>
