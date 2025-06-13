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
    <div class="px-1">
        <Link
            :href="href"
            class="group flex w-full items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 hover:shadow-sm"
            :class="{
                'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800':
                    active,
                'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-slate-900 dark:hover:text-slate-100':
                    !active,
            }"
        >
            <slot />
        </Link>
    </div>
</template>
