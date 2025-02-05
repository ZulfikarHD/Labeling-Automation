<script setup>
import { computed } from 'vue';
import { Loader2 } from 'lucide-vue-next';

const props = defineProps({
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset', 'link'].includes(value)
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => [
      'primary',
      'secondary',
      'danger',
      'warning',
      'success',
      'info',
      'outline-primary',
      'outline-secondary',
      'outline-danger',
      'outline-warning',
      'outline-success',
      'outline-info',
      'ghost',
      'link',
      'dark',
      'light',
      'white',
      'black'
    ].includes(value)
  },
  size: {
    type: String,
    default: 'base',
    validator: (value) => ['sm', 'base', 'lg'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  icon: {
    type: Object,
    default: null
  },
  iconPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value)
  },
  fullWidth: {
    type: Boolean,
    default: false
  }
});

const variantStyles = computed(() => ({
  primary: 'bg-sky-500 dark:bg-sky-600 text-white hover:bg-sky-600 dark:hover:bg-sky-700 focus:ring-sky-500/30 dark:focus:ring-sky-400/30',
  secondary: 'bg-fuchsia-500 dark:bg-fuchsia-600 text-white hover:bg-fuchsia-600 dark:hover:bg-fuchsia-700 focus:ring-fuchsia-500/30 dark:focus:ring-fuchsia-400/30',
  danger: 'bg-red-500 dark:bg-red-600 text-white hover:bg-red-600 dark:hover:bg-red-700 focus:ring-red-500/30 dark:focus:ring-red-400/30',
  warning: 'bg-amber-500 dark:bg-amber-600 text-white hover:bg-amber-600 dark:hover:bg-amber-700 focus:ring-amber-500/30 dark:focus:ring-amber-400/30',
  success: 'bg-green-500 dark:bg-green-600 text-white hover:bg-green-600 dark:hover:bg-green-700 focus:ring-green-500/30 dark:focus:ring-green-400/30',
  info: 'bg-blue-500 dark:bg-blue-600 text-white hover:bg-blue-600 dark:hover:bg-blue-700 focus:ring-blue-500/30 dark:focus:ring-blue-400/30',
  'outline-primary': 'border-2 border-sky-500 dark:border-sky-400 text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950 focus:ring-sky-500/30 dark:focus:ring-sky-400/30',
  'outline-secondary': 'border-2 border-fuchsia-500 dark:border-fuchsia-400 text-fuchsia-600 dark:text-fuchsia-400 hover:bg-fuchsia-50 dark:hover:bg-fuchsia-950 focus:ring-fuchsia-500/30 dark:focus:ring-fuchsia-400/30',
  'outline-danger': 'border-2 border-red-500 dark:border-red-400 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 focus:ring-red-500/30 dark:focus:ring-red-400/30',
  'outline-warning': 'border-2 border-amber-500 dark:border-amber-400 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950 focus:ring-amber-500/30 dark:focus:ring-amber-400/30',
  'outline-success': 'border-2 border-green-500 dark:border-green-400 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-950 focus:ring-green-500/30 dark:focus:ring-green-400/30',
  'outline-info': 'border-2 border-blue-500 dark:border-blue-400 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950 focus:ring-blue-500/30 dark:focus:ring-blue-400/30',
  ghost: 'text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950 focus:ring-sky-500/30 dark:focus:ring-sky-400/30',
  link: 'text-sky-600 dark:text-sky-400 hover:underline focus:ring-sky-500/30 dark:focus:ring-sky-400/30',
  dark: 'bg-gray-800 dark:bg-gray-700 text-white hover:bg-gray-900 dark:hover:bg-gray-800 focus:ring-gray-500/30 dark:focus:ring-gray-400/30',
  light: 'bg-gray-100 dark:bg-gray-200 text-gray-800 hover:bg-gray-200 dark:hover:bg-gray-300 focus:ring-gray-500/30 dark:focus:ring-gray-400/30',
  white: 'bg-white text-gray-800 border border-gray-300 hover:bg-gray-50 focus:ring-gray-500/30',
  black: 'bg-black text-white hover:bg-gray-900 focus:ring-gray-500/30'
}));

const sizeStyles = {
  sm: 'px-3 py-1.5 text-sm gap-1.5',
  base: 'px-4 py-2 text-base gap-2',
  lg: 'px-6 py-3 text-lg gap-2.5'
};

const iconSizes = {
  sm: 'w-4 h-4',
  base: 'w-5 h-5',
  lg: 'w-6 h-6'
};
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center font-semibold rounded-lg transition-all duration-200 ease-in-out
    shadow-sm
    focus:outline-none focus:ring-4
    disabled:opacity-70 disabled:cursor-not-allowed"
    :class="[
      variantStyles[variant],
      sizeStyles[size],
      fullWidth ? 'w-full' : '',
      (disabled || loading) ? 'opacity-70 cursor-not-allowed' : ''
    ]"
  >
    <!-- Loading Spinner -->
    <Loader2
      v-if="loading"
      :class="[iconSizes[size], 'animate-spin']"
    />

    <!-- Left Icon -->
    <component
      :is="icon"
      v-else-if="icon && iconPosition === 'left'"
      :class="iconSizes[size]"
    />

    <!-- Content -->
    <span :class="{ 'opacity-0': loading }">
      <slot></slot>
    </span>

    <!-- Right Icon -->
    <component
      :is="icon"
      v-if="icon && iconPosition === 'right' && !loading"
      :class="iconSizes[size]"
    />
  </button>
</template>
