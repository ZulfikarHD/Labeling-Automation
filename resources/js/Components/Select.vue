<script setup>
import { onMounted, ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
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

const emit = defineEmits(['update:modelValue']);

const select = ref(null);

onMounted(() => {
    if (select.value.hasAttribute('autofocus')) {
        select.value.focus();
    }
});

defineExpose({ focus: () => select.value.focus() });
</script>

<template>
    <select
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        ref="select"
        :disabled="disabled"
        class="w-full px-4 mb-2 transition-all duration-200 ease-in-out
        bg-white dark:bg-gray-900
        border border-gray-300 dark:border-gray-600
        rounded-lg
        focus:border-sky-500 dark:focus:border-sky-400
        focus:ring-2 focus:ring-sky-500/30 dark:focus:ring-sky-400/30
        focus:outline-none
        placeholder-gray-600 dark:placeholder-gray-400

        disabled:opacity-75
        disabled:cursor-not-allowed
        disabled:bg-gray-200 dark:disabled:bg-gray-800
        disabled:border-gray-300 dark:disabled:border-gray-600
        disabled:text-gray-700 dark:disabled:text-gray-300
        disabled:placeholder-gray-500 dark:disabled:placeholder-gray-400

        enabled:hover:border-gray-500 dark:enabled:hover:border-gray-400

        text-base leading-normal
        sm:text-sm md:text-base

        appearance-none
        bg-no-repeat
        bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2012%2012%22%3E%3Cpath%20fill%3D%22%23666%22%20d%3D%22M6%208L1%203h10z%22%2F%3E%3C%2Fsvg%3E')]
        bg-[center_right_1rem]
        pr-10"
        :class="[
            {
                'text-sm py-2': size === 'sm',
                'text-base py-3': size === 'base',
                'text-lg py-4': size === 'lg'
            },
            $attrs.class // This will allow custom classes to be passed through
        ]"
    >
        <slot></slot>
    </select>
</template>
