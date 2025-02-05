<script setup>
defineProps({
  show: Boolean
});
</script>

<template>
  <div
    v-show="show"
    class="absolute py-2 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 z-[110]"
  >
    <slot />
  </div>
</template>
