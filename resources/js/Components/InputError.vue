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
         class="flex items-center gap-2 mt-2 p-2 rounded-lg
                bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800
                animate-fade-in transition-all duration-200 ease-in-out">
        <AlertCircle
            :class="{
                'w-4 h-4': size === 'sm',
                'w-5 h-5': size === 'base',
                'w-6 h-6': size === 'lg'
            }"
            class="text-red-600 dark:text-red-400 flex-shrink-0"
            aria-hidden="true"
        />
        <div class="flex flex-col">
            <p class="font-semibold transition-colors
                      text-red-700 dark:text-red-300"
               :class="{
                   'text-sm': size === 'sm',
                   'text-base': size === 'base',
                   'text-lg': size === 'lg'
               }"
               role="alert">
                {{ message }}
            </p>
            <p class="text-red-600 dark:text-red-400"
               :class="{
                   'text-xs': size === 'sm',
                   'text-sm': size === 'base',
                   'text-base': size === 'lg'
               }">
                Pastikan data yang dimasukkan sudah benar dan terdaftar.
            </p>
        </div>
    </div>
</template>
