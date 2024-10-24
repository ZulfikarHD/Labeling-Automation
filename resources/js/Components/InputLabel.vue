<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block mb-2 font-medium text-[#111827] text-center">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
