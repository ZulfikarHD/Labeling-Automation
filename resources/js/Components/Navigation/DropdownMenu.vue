<script setup>
defineProps({
  show: Boolean
});
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="transform opacity-0 scale-95 translate-y-1"
    enter-to-class="transform opacity-100 scale-100 translate-y-0"
    leave-active-class="transition ease-in duration-150"
    leave-from-class="transform opacity-100 scale-100 translate-y-0"
    leave-to-class="transform opacity-0 scale-95 translate-y-1"
  >
    <div
      v-show="show"
      class="absolute mt-2 origin-top-left rounded-xl bg-white dark:bg-slate-800 shadow-xl ring-1 ring-black/5 dark:ring-white/10 border border-slate-200 dark:border-slate-700 backdrop-blur-sm z-[110] min-w-[200px]"
    >
      <div class="py-2">
        <slot />
      </div>
    </div>
  </Transition>
</template>
