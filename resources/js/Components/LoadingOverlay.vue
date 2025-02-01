<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
    <div class="rounded-lg p-4 flex flex-col gap-2 justify-center items-center">
      <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25 drop-shadow-md text-blue-50" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75 text-blue-500" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span class="text-white font-semibold animate-pulse">{{ message || 'Sedang Memproses...' }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  message: String
});
</script>
