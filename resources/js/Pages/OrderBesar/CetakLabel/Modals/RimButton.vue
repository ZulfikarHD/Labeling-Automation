<template>
    <!-- Status: Dalam Proses
         Menampilkan rim yang sedang dikerjakan (start=true, finish=false)
         dengan warna amber sebagai indikator -->
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

    <!-- Status: Selesai
         Menampilkan rim yang sudah selesai dikerjakan (start=true, finish=true)
         dengan warna emerald sebagai indikator -->
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

    <!-- Status: Inschiet
         Menampilkan rim khusus untuk inschiet (no_rim=999)
         dengan warna violet sebagai indikator -->
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

    <!-- Status: Tidak Tersedia
         Menampilkan rim yang belum memiliki petugas atau belum dimulai
         dengan warna abu-abu dan disabled -->
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
/**
 * Komponen RimButton
 *
 * Komponen ini digunakan untuk menampilkan status rim dalam bentuk button.
 * Setiap button memiliki warna yang berbeda sesuai dengan statusnya:
 * - Amber: Dalam proses pengerjaan
 * - Emerald: Selesai dikerjakan
 * - Violet: Rim Inschiet
 * - Abu-abu: Belum tersedia/belum dimulai
 *
 * Props:
 * @property {Object} rimData - Data rim yang akan ditampilkan
 * @property {number} rimData.no_rim - Nomor rim
 * @property {string} rimData.np_users - NP petugas yang mengerjakan
 * @property {boolean} rimData.start - Status mulai pengerjaan
 * @property {boolean} rimData.finish - Status selesai pengerjaan
 *
 * Events:
 * @emits select-rim - Event yang dipancarkan saat button diklik, membawa no_rim dan np_users
 */
defineProps({
    rimData: {
        type: Object,
        required: true
    }
});

defineEmits(['select-rim']);
</script>
