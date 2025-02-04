<script setup>
/**
 * BaseCard Component
 *
 * Komponen card yang dapat digunakan kembali untuk menampilkan konten dengan layout yang konsisten.
 * Mendukung mode gelap (dark mode) dan responsif.
 */

// Definisi props yang dapat diterima komponen
defineProps({
    // Judul card - dapat berupa String atau Object untuk kustomisasi lebih lanjut
    title: {
        type: [String, Object],
        default: ''
    },

    // Subjudul card yang ditampilkan di bawah judul utama
    subtitle: {
        type: String,
        default: ''
    },

    // Flag untuk menghilangkan padding default pada konten
    noPadding: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <!-- Container utama dengan lebar maksimum dan margin -->
    <div class="w-full max-w-5xl mx-auto mt-6">
        <!-- Card dengan background, shadow, dan transisi hover -->
        <div class="bg-white dark:bg-gray-800 bg-opacity-95 dark:bg-opacity-95 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden p-6">
            <!-- Header card dengan border bawah - hanya ditampilkan jika ada title -->
            <div v-if="title" class="px-6 py-4 border-b border-sky-600 dark:border-sky-500 text-center">
                <!-- Judul card dengan slot untuk kustomisasi -->
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    <slot name="title">
                        {{ title }}
                    </slot>
                </h2>
                <!-- Subjudul card - opsional -->
                <p v-if="subtitle" class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    {{ subtitle }}
                </p>
            </div>

            <!-- Konten card dengan padding yang dapat dikustomisasi -->
            <div :class="[
                'transition-all duration-200',
                noPadding ? '' : 'p-6'
            ]">
                <slot></slot>
            </div>
        </div>
    </div>
</template>
