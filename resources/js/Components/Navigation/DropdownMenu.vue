<script setup>
defineProps({
  show: Boolean
});
</script>

<template>
  <transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="opacity-0 translate-y-1"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-1"
  >
    <div
      v-show="show"
      class="absolute z-50 mt-2 w-64 bg-white dark:bg-slate-800 rounded-xl shadow-lg ring-1 ring-slate-200 dark:ring-slate-700 border border-slate-100 dark:border-slate-700 overflow-hidden backdrop-blur-sm"
      :class="$attrs.class"
    >
      <div class="py-1">
        <slot />
      </div>
    </div>
  </transition>
</template>
