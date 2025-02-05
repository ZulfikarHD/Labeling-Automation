<script setup>
defineProps({
    value: {
        type: String,
    },
    required: {
        type: Boolean,
        default: false
    },
    size: {
        type: String,
        default: 'base',
        validator: (value) => ['sm', 'base', 'lg'].includes(value)
    }
});
</script>

<template>
    <label class="block mb-2 transition-all duration-200 ease-in-out
                  text-gray-900 dark:text-gray-100
                  font-semibold tracking-wide text-center
                  group-focus-within:text-sky-600 dark:group-focus-within:text-sky-400"
           :class="{
               'text-sm': size === 'sm',
               'text-base': size === 'base',
               'text-lg': size === 'lg'
           }">
        <span v-if="value">
            {{ value }}
            <span v-if="required"
                  class="text-red-600 dark:text-red-400 ml-1"
                  aria-label="required field">*</span>
        </span>
        <span v-else>
            <slot />
            <span v-if="required"
                  class="text-red-600 dark:text-red-400 ml-1"
                  aria-label="required field">*</span>
        </span>
    </label>
</template>
