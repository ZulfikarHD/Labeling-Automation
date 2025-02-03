<script setup>
defineProps({
    title: {
        type: [String, Object],
        default: ''
    },
    subtitle: {
        type: String,
        default: ''
    },
    noPadding: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div class="w-full max-w-5xl mx-auto mt-6">
        <div class="bg-white dark:bg-gray-800 bg-opacity-95 dark:bg-opacity-95 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden p-6">
            <div v-if="title" class="px-6 py-4 border-b border-sky-600 dark:border-sky-500 text-center">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    <slot name="title">
                        {{ title }}
                    </slot>
                </h2>
                <p v-if="subtitle" class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    {{ subtitle }}
                </p>
            </div>

            <div :class="[
                'transition-all duration-200',
                noPadding ? '' : 'p-6'
            ]">
                <slot></slot>
            </div>
        </div>
    </div>
</template>
