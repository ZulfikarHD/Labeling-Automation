<template>
  <Transition
    enter-active-class="transition-opacity duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition-opacity duration-200"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="isLoading"
      class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm flex items-center justify-center z-50 flex-col">
      <slot />
      <Loader class="w-8 h-8 text-blue-600 dark:text-blue-400 animate-spin mt-4" />
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { Loader } from 'lucide-vue-next';

defineProps({
  isLoading: {
    type: Boolean,
    default: false
  }
});
</script>
