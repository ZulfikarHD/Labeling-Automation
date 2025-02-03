<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: {
        type: String,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false
    },
    size: {
        type: String,
        default: 'base',
        validator: (value) => ['sm', 'base', 'lg'].includes(value)
    }
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full px-4 py-3 mb-2 transition-all duration-200 ease-in-out
        bg-white dark:bg-gray-900
        text-gray-900 dark:text-gray-100
        border border-gray-300 dark:border-gray-600
        rounded-lg
        focus:border-sky-500 dark:focus:border-sky-400
        focus:ring-2 focus:ring-sky-500/30 dark:focus:ring-sky-400/30
        focus:outline-none
        placeholder-gray-600 dark:placeholder-gray-400

        disabled:opacity-60
        disabled:cursor-not-allowed
        disabled:bg-gray-100 dark:disabled:bg-gray-800
        disabled:border-gray-200 dark:disabled:border-gray-700
        disabled:text-gray-500 dark:disabled:text-gray-400
        disabled:placeholder-gray-400 dark:disabled:placeholder-gray-500

        enabled:hover:border-gray-500 dark:enabled:hover:border-gray-400

        text-base leading-normal
        sm:text-sm md:text-base"
        :class="{
            'text-sm py-2': size === 'sm',
            'text-base py-3': size === 'base',
            'text-lg py-4': size === 'lg'
        }"
        :value="modelValue"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="input"
    />
</template>
