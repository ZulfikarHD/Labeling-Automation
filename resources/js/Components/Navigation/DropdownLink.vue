<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="w-full">
        <Link
            :href="href"
            class="w-full block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-200"
            :class="{
                'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': active,
            }"
        >
            <slot />
        </Link>
    </div>
</template>
