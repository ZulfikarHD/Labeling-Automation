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
        ? "group inline-flex items-center gap-3 px-4 py-2.5 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 shadow-sm transition-all duration-200 hover:shadow-md"
        : "group inline-flex items-center gap-3 px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/10 rounded-xl transition-all duration-200 hover:shadow-sm"
);
</script>

<template>
    <Link :href="href" :class="classes">
    <component v-if="icon" :is="icon" class="h-4 w-4 flex-shrink-0 transition-colors duration-200" :class="active
            ? 'text-blue-600 dark:text-blue-400'
            : 'text-slate-500 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400'
        " />
    <slot />
    </Link>
</template>
