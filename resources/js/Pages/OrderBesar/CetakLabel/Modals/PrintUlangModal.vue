<template>
    <Modal :show="show" @close="$emit('close')">
        <form @submit.prevent="printUlangLabel" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex flex-col gap-4">
                <!-- Header modal -->
                <h1 class="text-xl font-bold text-center text-gray-800 dark:text-gray-200">
                    Print Ulang / Ganti Data Rim
                </h1>

                <!-- Input untuk data rim -->
                <TextInput
                    id="dataRim"
                    name="dataRim"
                    type="text"
                    class="text-base font-semibold text-center uppercase bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 dark:text-gray-200"
                    v-model="formPrintUlang.dataRim"
                    required
                    disabled
                />

                <!-- Tombol pemilihan rim kiri/kanan -->
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

                <!-- Grid tombol pemilihan rim -->
                <div class="grid grid-cols-5 gap-2">
                    <template v-for="n in dataPrintUlang" :key="n.no_rim">
                        <!-- ... tombol pemilihan rim yang ada ... -->
                        <RimButton
                            :rim-data="n"
                            @select-rim="pilihRim"
                        />
                    </template>
                </div>

                <!-- Input nomor rim dan NP petugas -->
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
                        {{ loading ? 'Memproses...' : 'Print' }}
                    </button>
                </div>
            </div>
        </form>
    </Modal>
</template>

<script setup>
// Mengimpor library dan komponen yang diperlukan
import { ref, reactive, watch } from 'vue';
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import RimButton from './RimButton.vue';
import axios from 'axios';
import { singleLabel } from "@/Components/PrintPages/index";

// Mendefinisikan props yang diterima
const props = defineProps({
    show: Boolean,
    productData: Object,
    colorObc: String,
    team: Number,
});

// Mendefinisikan emit untuk event
const emit = defineEmits(['close', 'success']);

// Mendefinisikan state loading dan data rim
const loading = ref(false);
const dataPrintUlang = ref([]);

// Mendefinisikan form untuk print ulang
const formPrintUlang = reactive({
    dataRim: "Kiri",
    noRim: "",
    npPetugas: "",
    po: props.productData?.no_po,
    obc: props.productData?.no_obc,
    team: props.team,
});

// Fungsi untuk mengubah data rim ke kanan
const dataRimKanan = async () => {
    formPrintUlang.dataRim = "Kanan";
    getDataRim();
};

// Fungsi untuk mengubah data rim ke kiri
const dataRimKiri = async () => {
    formPrintUlang.dataRim = "Kiri";
    getDataRim();
};

// Fungsi untuk mengambil data rim
const getDataRim = async () => {
    try {
        const { data } = await axios.post("/api/order-besar/cetak-label/edit", formPrintUlang);
        dataPrintUlang.value = data;
    } catch (error) {
        console.error('Error fetching rim data:', error);
    }
};

// Fungsi untuk memilih rim
const pilihRim = (noRim, np) => {
    formPrintUlang.noRim = noRim;
    formPrintUlang.npPetugas = np;
};

// Fungsi untuk mencetak ulang label
const printUlangLabel = async () => {
    try {
        loading.value = true;

        if (!formPrintUlang.noRim || !formPrintUlang.npPetugas) {
            emit('error', 'Mohon lengkapi semua field');
            return;
        }

        await axios.post('/api/order-besar/cetak-label/update', {
            po: formPrintUlang.po,
            dataRim: formPrintUlang.dataRim,
            noRim: formPrintUlang.noRim,
            npPetugas: formPrintUlang.npPetugas,
            team: formPrintUlang.team
        });

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

// Menginisialisasi data saat modal ditampilkan
watch(() => props.show, (newVal) => {
    if (newVal) {
        getDataRim();
    }
});
</script>
