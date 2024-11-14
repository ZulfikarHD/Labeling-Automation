<script setup>
import { AlertCircle } from 'lucide-vue-next';

defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message" class="flex items-center gap-2 mt-1 animate-fade-in">
        <AlertCircle class="w-4 h-4 text-red-500" />
        <p class="text-sm font-medium text-red-500 dark:text-red-400 transition-colors">
            {{ message }}
        </p>
    </div>
</template>
