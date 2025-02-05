<script setup>
import { AlertCircle } from 'lucide-vue-next';

defineProps({
    message: {
        type: String,
    },
    size: {
        type: String,
        default: 'base',
        validator: (value) => ['sm', 'base', 'lg'].includes(value)
    }
});
</script>

<template>
    <div v-show="message"
         class="flex items-center gap-2 mt-2
                animate-fade-in transition-all duration-200 ease-in-out">
        <AlertCircle
            :class="{
                'w-3.5 h-3.5': size === 'sm',
                'w-4 h-4': size === 'base',
                'w-5 h-5': size === 'lg'
            }"
            class="text-red-600 dark:text-red-400"
            aria-hidden="true"
        />
        <p class="font-medium transition-colors
                  text-red-600 dark:text-red-400"
           :class="{
               'text-xs': size === 'sm',
               'text-sm': size === 'base',
               'text-base': size === 'lg'
           }"
           role="alert">
            {{ message }}
        </p>
    </div>
</template>
