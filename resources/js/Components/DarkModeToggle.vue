<script setup>
import { Sun, Moon } from "lucide-vue-next";

defineProps({
  isDark: Boolean
});

defineEmits(['toggle']);
</script>

<template>
  <button
    @click="$emit('toggle')"
    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all"
  >
    <Sun
      v-if="!isDark"
      class="w-5 h-5 text-slate-600 hover:text-blue-600"
    />
    <Moon
      v-else
      class="w-5 h-5 text-slate-300 hover:text-blue-600"
    />
  </button>
</template>
