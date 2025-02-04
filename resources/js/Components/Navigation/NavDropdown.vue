<script setup>
import { ChevronDown } from "lucide-vue-next";

defineProps({
  label: String,
  icon: Object,
  isOpen: Boolean
});

defineEmits(['toggle']);
</script>

<template>
  <button
    @click.stop="$emit('toggle')"
    class="dropdown-trigger group flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all"
  >
    <component
      :is="icon"
      class="w-4 h-4 text-slate-600 dark:text-slate-300 group-hover:text-blue-600"
    />
    <span
      class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600"
    >{{ label }}</span>
    <ChevronDown
      class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition-transform duration-200"
      :class="{ 'rotate-180': isOpen }"
    />
  </button>
</template>
