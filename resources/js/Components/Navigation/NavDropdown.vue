<script setup>
import { ChevronDown } from "lucide-vue-next";

defineProps({
  label: String,
  icon: Object,
  isOpen: Boolean,
  active: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle']);
</script>

<template>
  <button
    @click.stop="$emit('toggle')"
    class="dropdown-trigger group inline-flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition-all duration-200 hover:shadow-sm"
    :class="[
      active
        ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 shadow-sm'
        : 'text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/10',
      isOpen
        ? 'ring-2 ring-blue-500/20 dark:ring-blue-400/20 bg-blue-50 dark:bg-blue-900/20'
        : '',
    ]"
  >
    <component
      :is="icon"
      class="h-4 w-4 flex-shrink-0 transition-colors duration-200"
      :class="[
        active || isOpen
          ? 'text-blue-600 dark:text-blue-400'
          : 'text-slate-500 dark:text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400',
      ]"
    />
    <span
      class="font-medium transition-colors duration-200"
      :class="[
        active || isOpen
          ? 'text-blue-600 dark:text-blue-400'
          : 'text-slate-700 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400',
      ]"
    >{{ label }}</span>
            <ChevronDown
            class="h-5 w-5 transition-all duration-200"
            :class="[
                isOpen ? 'rotate-180' : '',
                active || isOpen
                    ? 'text-blue-600 dark:text-blue-400'
                    : 'text-slate-400 dark:text-slate-500 group-hover:text-blue-600 dark:group-hover:text-blue-400',
            ]"
        />
  </button>
</template>
