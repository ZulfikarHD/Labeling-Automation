<template>
    <!-- In Progress -->
    <button
        v-if="rimData.np_users && rimData.start && !rimData.finish"
        type="button"
        @click="$emit('select-rim', rimData.no_rim, rimData.np_users)"
        class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900 hover:bg-amber-200 dark:hover:bg-amber-800 transition-colors"
    >
        <div class="flex flex-col items-center">
            <span class="text-sm font-medium text-amber-800 dark:text-amber-200">{{ rimData.np_users }}</span>
            <span class="text-sm font-bold text-green-800 dark:text-green-200">{{ rimData.no_rim }}</span>
        </div>
    </button>

    <!-- Completed -->
    <button
        v-else-if="rimData.np_users && rimData.start && rimData.finish"
        type="button"
        @click="$emit('select-rim', rimData.no_rim, rimData.np_users)"
        class="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900 hover:bg-emerald-200 dark:hover:bg-emerald-800 transition-colors"
    >
        <div class="flex flex-col items-center">
            <span class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ rimData.np_users }}</span>
            <span class="text-sm font-bold text-indigo-800 dark:text-indigo-200">{{ rimData.no_rim }}</span>
        </div>
    </button>

    <!-- Inschiet -->
    <button
        v-else-if="rimData.no_rim === 999"
        type="button"
        @click="$emit('select-rim', rimData.no_rim, rimData.np_users)"
        class="p-2 rounded-lg bg-violet-100 dark:bg-violet-900 hover:bg-violet-200 dark:hover:bg-violet-800 transition-colors"
    >
        <div class="flex flex-col items-center">
            <span class="text-sm font-medium text-violet-800 dark:text-violet-200">{{ rimData.np_users }}</span>
            <span class="text-sm font-bold text-violet-900 dark:text-violet-100">Inschiet</span>
        </div>
    </button>

    <!-- Unavailable -->
    <button
        v-else
        type="button"
        disabled
        class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 cursor-not-allowed"
    >
        <div class="flex flex-col items-center">
            <span class="text-sm font-medium text-gray-400 dark:text-gray-500">-</span>
            <span class="text-sm font-bold text-gray-500 dark:text-gray-400">{{ rimData.no_rim }}</span>
        </div>
    </button>
</template>

<script setup>
defineProps({
    rimData: {
        type: Object,
        required: true
    }
});

defineEmits(['select-rim']);
</script>
