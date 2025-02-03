<script setup>
defineProps({
    value: {
        type: String,
    },
    required: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <label class="block mb-2 text-sm font-medium tracking-wide transition-colors duration-200
                  dark:text-gray-200 text-gray-700
                  md:text-base
                  group-focus-within:text-blue-600 dark:group-focus-within:text-blue-400">
        <span v-if="value">
            {{ value }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </span>
        <span v-else>
            <slot />
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </span>
    </label>
</template>
