<script setup>
import { ref, computed } from 'vue';
import { PlusCircle, Trash2 } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
    labels: {
        type: Object,
        required: true
    }
});

// Batch delete functionality
const batchDeleteMode = ref(false);
const selectedLabels = ref(new Set());
const hasSelectedLabels = computed(() => selectedLabels.value.size > 0);

const toggleBatchDeleteMode = () => {
    batchDeleteMode.value = !batchDeleteMode.value;
    if (!batchDeleteMode.value) {
        selectedLabels.value.clear();
    }
};

const toggleLabelSelection = (labelId) => {
    if (selectedLabels.value.has(labelId)) {
        selectedLabels.value.delete(labelId);
    } else {
        selectedLabels.value.add(labelId);
    }
};

const emit = defineEmits(['editLabel', 'addRim', 'deleteLabels', 'refreshData']);

// Batch delete confirmation
const confirmBatchDelete = async () => {
    try {
        const result = await Swal.fire({
            title: 'Konfirmasi Penghapusan',
            html: `
                <p class="mb-4">Anda akan menghapus ${selectedLabels.value.size} label. Tindakan ini tidak dapat dibatalkan.</p>
                <input
                    type="password"
                    id="password"
                    class="swal2-input"
                    placeholder="Masukkan password Anda"
                >
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const password = document.getElementById('password').value;
                if (!password) {
                    Swal.showValidationMessage('Password harus diisi');
                }
                return password;
            }
        });

        if (result.isConfirmed) {
            emit('deleteLabels', Array.from(selectedLabels.value));
            toggleBatchDeleteMode();
        }
    } catch (error) {
        console.error('Error in batch delete:', error);
        await Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menghapus label',
            icon: 'error'
        });
    }
};

// Status class helper
const getStatusClasses = (label) => {
    if (label.finish) {
        return 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-200 dark:border-emerald-700';
    }
    if (label.start && !label.finish) {
        return 'bg-amber-50 dark:bg-amber-900/30 border-amber-200 dark:border-amber-700';
    }
    return 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-600';
};

// Computed properties for filtered labels
const leftLabels = computed(() =>
    props.labels.filter(label => label.potongan === 'Kiri')
);

const rightLabels = computed(() =>
    props.labels.filter(label => label.potongan === 'Kanan')
);
</script>

<template>
    <div class="mt-8 bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 border border-slate-100 dark:border-slate-700">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                        Edit Label Produksi
                    </h1>
                    <p class="mt-2 text-slate-600 dark:text-slate-400">
                        Edit Nomor Rim Manual
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        @click="$emit('addRim')"
                        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <PlusCircle class="w-5 h-5" />
                        Tambah Rim
                    </button>
                    <button
                        @click="toggleBatchDeleteMode"
                        class="flex items-center gap-2 px-4 py-2 text-white transition-all duration-200 rounded-xl"
                        :class="batchDeleteMode ? 'bg-red-500 hover:bg-red-600' : 'bg-slate-500 hover:bg-slate-600'"
                    >
                        <Trash2 class="w-5 h-5" />
                        {{ batchDeleteMode ? 'Batal' : 'Hapus Batch' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col lg:flex-row justify-between gap-8 mb-12">
            <slot name="legend" />

            <!-- Left Labels -->
            <div class="flex-1">
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white text-center mb-6">
                    Lembar Kiri
                </h3>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                    <template v-for="label in leftLabels" :key="`${label.id}-${label.updated_at}`">
                        <div
                            :class="[
                                'relative transition-all duration-300 hover:shadow-md cursor-pointer rounded-xl',
                                batchDeleteMode ? 'hover:scale-100' : 'hover:scale-105'
                            ]"
                            @click="batchDeleteMode ? toggleLabelSelection(label.id) : $emit('editLabel', label)"
                        >
                            <!-- Selection overlay -->
                            <div
                                v-if="batchDeleteMode"
                                class="absolute inset-0 z-10 rounded-xl"
                                :class="selectedLabels.has(label.id) ? 'bg-red-500/20 ring-2 ring-red-500' : 'hover:bg-slate-500/10'"
                            />

                            <!-- Label card -->
                            <div :class="['rounded-xl p-3 border', getStatusClasses(label)]">
                                <div class="flex flex-col items-center gap-1">
                                    <span :class="[
                                        'text-sm font-bold',
                                        label.finish ? 'text-emerald-700 dark:text-emerald-400' :
                                        label.start ? 'text-amber-700 dark:text-amber-400' :
                                        'text-slate-400 dark:text-slate-500'
                                    ]">
                                        {{ label.np_users || '-' }}
                                    </span>
                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ label.no_rim }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Right Labels -->
            <div class="flex-1">
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white text-center mb-6">
                    Lembar Kanan
                </h3>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                    <template v-for="label in rightLabels" :key="`${label.id}-${label.updated_at}`">
                        <!-- Similar structure as Left Labels -->
                        <div
                            :class="[
                                'relative transition-all duration-300 hover:shadow-md cursor-pointer rounded-xl',
                                batchDeleteMode ? 'hover:scale-100' : 'hover:scale-105'
                            ]"
                            @click="batchDeleteMode ? toggleLabelSelection(label.id) : $emit('editLabel', label)"
                        >
                            <div
                                v-if="batchDeleteMode"
                                class="absolute inset-0 z-10 rounded-xl"
                                :class="selectedLabels.has(label.id) ? 'bg-red-500/20 ring-2 ring-red-500' : 'hover:bg-slate-500/10'"
                            />

                            <div :class="['rounded-xl p-3 border', getStatusClasses(label)]">
                                <div class="flex flex-col items-center gap-1">
                                    <span :class="[
                                        'text-sm font-bold',
                                        label.finish ? 'text-emerald-700 dark:text-emerald-400' :
                                        label.start ? 'text-amber-700 dark:text-amber-400' :
                                        'text-slate-400 dark:text-slate-500'
                                    ]">
                                        {{ label.np_users || '-' }}
                                    </span>
                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ label.no_rim }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Batch Delete Button -->
        <div
            v-if="batchDeleteMode"
            class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50"
        >
            <button
                @click="confirmBatchDelete"
                :disabled="!hasSelectedLabels"
                class="flex items-center gap-2 px-6 py-3 text-white bg-red-500 rounded-xl shadow-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-red-600 transition-colors"
            >
                <Trash2 class="w-5 h-5" />
                Hapus {{ selectedLabels.size }} Label
            </button>
        </div>
    </div>
</template>
