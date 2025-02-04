<template>
    <Modal :show="show" @close="$emit('close')">
        <form @submit.prevent="printUlangLabel" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex flex-col gap-4">
                <!-- Header modal -->
                <h1 class="text-xl font-bold text-center text-gray-800 dark:text-gray-200">
                    Cetak Ulang / Ganti Data Rim
                </h1>

                <!-- Input untuk data rim (Kiri/Kanan) -->
                <TextInput
                    id="dataRim"
                    name="dataRim"
                    type="text"
                    class="text-base font-semibold text-center uppercase bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 dark:text-gray-200"
                    v-model="formPrintUlang.dataRim"
                    required
                    disabled
                />

                <!-- Tombol pemilihan posisi rim -->
                <div class="flex justify-center gap-3">
                    <button
                        type="button"
                        @click="dataRimKiri"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 dark:bg-blue-700 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-600"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        KIRI
                    </button>
                    <button
                        type="button"
                        @click="dataRimKanan"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 dark:bg-blue-700 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-600"
                    >
                        KANAN
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Grid daftar rim yang tersedia -->
                <div class="grid grid-cols-5 gap-2">
                    <template v-for="n in dataPrintUlang" :key="n.no_rim">
                        <RimButton
                            :rim-data="n"
                            @select-rim="pilihRim"
                        />
                    </template>
                </div>

                <!-- Form input nomor rim dan NP petugas -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="noRimPU" value="Nomor Rim" class="mb-1 text-sm dark:text-gray-300" />
                        <template v-if="formPrintUlang.noRim === 999">
                            <TextInput
                                id="noRimPU"
                                type="hidden"
                                v-model="formPrintUlang.noRim"
                                required
                            />
                            <TextInput
                                type="text"
                                value="Inschiet"
                                class="w-full bg-gray-50 dark:bg-gray-700 text-center text-sm dark:text-gray-300"
                                disabled
                            />
                        </template>
                        <template v-else>
                            <TextInput
                                id="noRimPU"
                                type="number"
                                v-model="formPrintUlang.noRim"
                                class="w-full bg-gray-50 dark:bg-gray-700 text-center text-sm dark:text-gray-300"
                                required
                                disabled
                            />
                        </template>
                    </div>

                    <div>
                        <InputLabel for="npPetugasPU" value="NP Petugas" class="mb-1 text-sm dark:text-gray-300" />
                        <TextInput
                            id="npPetugasPU"
                            type="text"
                            v-model="formPrintUlang.npPetugas"
                            class="w-full uppercase text-sm dark:bg-gray-700 dark:text-gray-300"
                            maxlength="4"
                            required
                        />
                    </div>
                </div>

                <!-- Tombol aksi -->
                <div class="flex justify-between mt-4">
                    <button
                        type="button"
                        class="px-6 py-2.5 text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900 hover:bg-red-100 dark:hover:bg-red-800 rounded-lg transition-colors"
                    >
                        Hapus
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        :class="[
                            'px-6 py-2.5 text-white bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 rounded-lg transition-colors',
                            loading ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                    >
                        {{ loading ? 'Memproses...' : 'Cetak' }}
                    </button>
                </div>
            </div>
        </form>
    </Modal>
</template>

<script setup>
/**
 * Modal untuk mencetak ulang label atau mengubah data rim
 *
 * Fitur:
 * - Pemilihan posisi rim (kiri/kanan)
 * - Pemilihan nomor rim dari daftar yang tersedia
 * - Input NP petugas
 * - Cetak ulang label dengan data yang diperbarui
 *
 * @component PrintUlangModal
 */

import { ref, reactive, watch } from 'vue';
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import RimButton from './RimButton.vue';
import axios from 'axios';
import { singleLabel } from "@/Components/PrintPages/index";

// Props yang diterima komponen
const props = defineProps({
    show: Boolean, // Status tampilan modal
    productData: Object, // Data produk (PO, OBC)
    colorObc: String, // Warna OBC untuk styling
    team: Number, // ID tim
});

// Event yang dapat dipancarkan
const emit = defineEmits(['close', 'success']);

// State untuk loading dan data rim
const loading = ref(false);
const dataPrintUlang = ref([]); // Menyimpan daftar rim yang tersedia

// State form dengan reactive untuk two-way binding
const formPrintUlang = reactive({
    dataRim: "Kiri", // Posisi rim (Kiri/Kanan)
    noRim: "", // Nomor rim yang dipilih
    npPetugas: "", // NP petugas yang mengerjakan
    po: props.productData?.no_po, // Nomor PO
    obc: props.productData?.no_obc, // Nomor OBC
    team: props.team, // ID tim
});

/**
 * Mengubah posisi rim ke kanan dan memperbarui data
 */
const dataRimKanan = async () => {
    formPrintUlang.dataRim = "Kanan";
    getDataRim();
};

/**
 * Mengubah posisi rim ke kiri dan memperbarui data
 */
const dataRimKiri = async () => {
    formPrintUlang.dataRim = "Kiri";
    getDataRim();
};

/**
 * Mengambil data rim dari server berdasarkan posisi yang dipilih
 */
const getDataRim = async () => {
    try {
        const { data } = await axios.post("/api/order-besar/cetak-label/edit", formPrintUlang);
        dataPrintUlang.value = data;
    } catch (error) {
        console.error('Error saat mengambil data rim:', error);
    }
};

/**
 * Menangani pemilihan rim dan mengisi form
 * @param {number} noRim - Nomor rim yang dipilih
 * @param {string} np - NP petugas dari rim yang dipilih
 */
const pilihRim = (noRim, np) => {
    formPrintUlang.noRim = noRim;
    formPrintUlang.npPetugas = np;
};

/**
 * Menangani proses cetak ulang label
 * - Memvalidasi input
 * - Memperbarui data di server
 * - Mencetak label baru
 */
const printUlangLabel = async () => {
    try {
        loading.value = true;

        if (!formPrintUlang.noRim || !formPrintUlang.npPetugas) {
            emit('error', 'Mohon lengkapi semua field');
            return;
        }

        // Update data di server
        await axios.post('/api/order-besar/cetak-label/update', {
            po: formPrintUlang.po,
            dataRim: formPrintUlang.dataRim,
            noRim: formPrintUlang.noRim,
            npPetugas: formPrintUlang.npPetugas,
            team: formPrintUlang.team
        });

        // Generate dan cetak label
        const printLabel = singleLabel(
            formPrintUlang.obc,
            formPrintUlang.noRim !== 999 ? formPrintUlang.noRim : "INS",
            props.colorObc,
            formPrintUlang.dataRim === "Kiri" ? "(*)" : "(**)",
            formPrintUlang.npPetugas,
            undefined,
            500,
        );

        emit('print', printLabel);
        emit('success');
        emit('close');

    } catch (error) {
        console.error('Error:', error);
        emit('error', 'Gagal mencetak label');
    } finally {
        loading.value = false;
    }
};

// Memuat data rim saat modal ditampilkan
watch(() => props.show, (newVal) => {
    if (newVal) {
        getDataRim();
    }
});
</script>
