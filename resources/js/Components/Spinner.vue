<template>
    <div class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm flex items-center justify-center z-10">
        <Loader class="w-8 h-8 text-blue-600 animate-spin" />
    </div>
</template>

<script setup>
import { Loader } from 'lucide-vue-next';

const props = defineProps({
    class: {
        type: String,
        default: 'w-8 h-8'
    }
});
</script>
