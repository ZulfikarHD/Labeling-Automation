<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: Number,
        required: true,
        validator: (value) => [0, 1].includes(value) // 0: Siap Periksa, 1: Sedang Diperiksa
    }
});

const badgeConfig = computed(() => ({
    0: {
        text: 'Siap Diperiksa',
        colors: {
            bg: 'from-slate-50 to-slate-100 dark:from-slate-900/40 dark:to-slate-900/60',
            text: 'text-slate-700 dark:text-slate-200',
            border: 'border-slate-200/60 dark:border-slate-700/60',
            dot: 'bg-slate-500',
            shadow: 'shadow-slate-100/50 dark:shadow-slate-900/30'
        },
        animate: false
    },
    1: {
        text: 'Sedang Diperiksa',
        colors: {
            bg: 'from-amber-50 to-amber-100 dark:from-amber-900/40 dark:to-amber-900/60',
            text: 'text-amber-700 dark:text-amber-200',
            border: 'border-amber-200/60 dark:border-amber-700/60',
            dot: 'bg-amber-500',
            shadow: 'shadow-amber-100/50 dark:shadow-amber-900/30'
        },
        animate: true
    },
    2: {
        text: 'Selesai Diperiksa',
        colors: {
            bg: 'from-emerald-50 to-emerald-100 dark:from-emerald-900/40 dark:to-emerald-900/60',
            text: 'text-emerald-700 dark:text-emerald-200',
            border: 'border-emerald-200/60 dark:border-emerald-700/60',
            dot: 'bg-emerald-500',
            shadow: 'shadow-emerald-100/50 dark:shadow-emerald-900/30'
        },
        animate: false
    },
}));
</script>

<template>
    <span class="inline-flex items-center gap-2 px-3 py-1.5
                 text-sm font-medium tracking-wide
                 bg-gradient-to-r rounded-full
                 border shadow-sm
                 transition-all duration-200"
          :class="[
              badgeConfig[status].colors.bg,
              badgeConfig[status].colors.text,
              badgeConfig[status].colors.border,
              badgeConfig[status].colors.shadow
          ]">
        <span class="relative flex h-2 w-2">
            <span v-if="badgeConfig[status].animate"
                  class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                  :class="badgeConfig[status].colors.dot">
            </span>
            <span class="relative inline-flex rounded-full h-2 w-2"
                  :class="badgeConfig[status].colors.dot">
            </span>
        </span>
        {{ badgeConfig[status].text }}
    </span>
</template>
