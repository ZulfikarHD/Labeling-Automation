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
    icon: {
        type: Object,
        default: null,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-4 py-2.5 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg transition-all duration-200'
        : 'inline-flex items-center px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-all duration-200'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <component
            v-if="icon"
            :is="icon"
            class="w-4.5 h-4.5 mr-2.5"
            :class="active ? 'text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400'"
        />
        <slot />
    </Link>
</template>
