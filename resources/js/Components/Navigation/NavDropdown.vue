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
    class="dropdown-trigger group flex items-center gap-2.5 px-4 py-2.5 rounded-lg transition-all duration-200"
    :class="[
      active
        ? 'bg-blue-50 dark:bg-blue-900/20'
        : 'hover:bg-blue-50 dark:hover:bg-slate-700/50',
      isOpen
        ? 'ring-2 ring-blue-100 dark:ring-blue-900/30'
        : ''
    ]"
  >
    <component
      :is="icon"
      class="w-4.5 h-4.5 transition-colors"
      :class="[
        active
          ? 'text-blue-600 dark:text-blue-400'
          : 'text-slate-600 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400'
      ]"
    />
    <span
      class="text-sm font-medium transition-colors"
      :class="[
        active
          ? 'text-blue-600 dark:text-blue-400'
          : 'text-slate-700 dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400'
      ]"
    >{{ label }}</span>
    <ChevronDown
      class="w-4 h-4 transition-all duration-200"
      :class="[
        isOpen ? 'rotate-180' : '',
        active
          ? 'text-blue-600 dark:text-blue-400'
          : 'text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400'
      ]"
    />
  </button>
</template>
