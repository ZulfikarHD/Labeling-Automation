<script setup>
import { ref, inject, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Select from "@/Components/Select.vue";
import Button from "@/Components/Button.vue";
import InputError from "@/Components/InputError.vue";
import { router } from '@inertiajs/vue3';
import LoadingOverlay from "@/Components/LoadingOverlay.vue";
import { batchSingleLabel } from "@/Components/PrintPages/index";
import axios from 'axios';
import {
    Scan,
    FileText,
    Car,
    Hash,
    Users,
    Printer,
    RotateCcw,
    CheckCircle,
    AlertCircle
} from 'lucide-vue-next';

// Constants
const PRINT_TIMEOUT_BASE = 1000;
const DEBOUNCE_DELAY = 500;
const VALIDATION_DELAY = 300;

// Computed properties
const isDataFetched = computed(() => !!specMmea.value.no_obc);
const obcColor = "#1d4ed8";
const printTimeout = computed(() =>
    form.jumlah_label > 10
        ? Math.round(PRINT_TIMEOUT_BASE * (form.jumlah_label / 5))
        : PRINT_TIMEOUT_BASE
);

const isLoading = ref(false);
const specMmea = ref({
    produk: "-",
    no_obc: "-",
    no_plat: "-",
    jml_lbr: 0,
});

const form = useForm({
    no_po: 0,
    no_rim: {
        no_1: 1,
    },
    periksa1: {
        np_1: "",
    },
    periksa2: {
        np_1: "",
    },
    jml_kemas: {
        no_1: 300,
    },
});

const errors = ref({
    poNotFound: '',
});


// API calls
const apiService = {
    async getSpecification(noPo) {
        const response = await axios.get(`https://sirine.peruri.co.id/sirine/api/detail-order-mmea/${noPo}`);
        return response.data;
    },

    async submitForm(formData) {
        const response = await axios.post('/api/print-label/inspeksi/store', formData);
        return response.data;
    }
};

// Data management
const dataManager = {
    async fetchSpecification() {
        let nomor_po = form.no_po;
        form.reset();
        if (!nomor_po || nomor_po < 3) {
            this.resetSpecification();
            return;
        }

        form.no_po = nomor_po;
        isLoading.value = true;
        errors.value.poNotFound = '';

        try {
            const [specData] = await Promise.all([
                apiService.getSpecification(nomor_po),
            ]);

            const jml_label = Math.ceil(specData.rencet / 300);
            const last_jml_kemas = specData.rencet % 300 == 0 ? 300 : specData.rencet % 300;


            specMmea.value = {
                no_obc: specData.no_obc,
                produk: specData.produk,
                jml_lbr: specData.rencet,
                // nomor_plat: specData.nomor_plat,
                nomor_plat: "-", //temporary
            };

            if (jml_label == 1) {
                form.jml_kemas.no_1 = specData.rencet;
            }

            //Dynamic Form Untuk Pemeriksa
            for (let i = 2; i < jml_label; i++) {
                form.periksa1[`np_${i}`] = "";
                form.periksa2[`np_${i}`] = "";
                form.jml_kemas[`no_${i}`] = 300;
                form.no_rim[`no_${i}`] = i;
            }

            // Last Field For Form
            form.periksa1[`np_${jml_label}`] = "";
            form.periksa2[`np_${jml_label}`] = "";
            form.jml_kemas[`no_${jml_label}`] = last_jml_kemas;
            form.no_rim[`no_${jml_label}`] = jml_label;

            console.log(form);

        } catch (error) {
            console.error('Error fetching specification:', error);
            this.resetSpecification();
            errors.value.poNotFound = 'Nomor Po Tidak Ditemukan Harap Hubungi admin untuk memperbaharui data order';
        } finally {
            isLoading.value = false;
        }
    },

    resetSpecification() {
        specMmea.value = {
            no_obc: '',
            nomor_plat: '',
            produk: '',
            jml_lbr: 0,
        };
        errors.value.poNotFound = '';
        errors.value.labelQuantity = '';
    },

    clearForm() {
        form.reset();
        this.resetSpecification();

        if (!isLoading.value) {
            swal.fire({
                icon: 'info',
                title: 'Form Telah Direset',
                text: 'Semua data telah dihapus',
                timer: 1500,
                showConfirmButton: false
            });
        }
    }
};
console.log(form);

// Debounced handlers
let debounceTimer;
const handlePoInput = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        dataManager.fetchSpecification();
    }, DEBOUNCE_DELAY);
};

const handlePoInputChange = () => {
    errors.value.poNotFound = '';
    handlePoInput();
};

// const handleLabelQuantityInput = () => {
//     setTimeout(() => {
//         validation.validateLabelQuantity();
//     }, VALIDATION_DELAY);
// };

let npDebounceTimer;
const handleNpInput = () => {
    clearTimeout(npDebounceTimer);
    errors.value.labelQuantity = '';
};
</script>

<template>

    <Head title="Print Label Inspeksi" />
    <LoadingOverlay :is-loading="isLoading" />

    <AuthenticatedLayout>
        <div class="container mx-auto px-4 max-w-4xl">

            <!-- Main Form Card -->
            <div
                class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">

                <!-- Barcode Input Section -->
                <div
                    class="p-8 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700">
                    <div class="flex items-center gap-4 mb-4">
                        <Scan class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">
                            Scan Nomor PO
                        </h2>
                    </div>

                    <div class="relative">
                        <InputLabel for="no_po" value="Nomor Production Order" required
                            class="text-slate-700 dark:text-slate-300" />
                        <div class="relative">
                            <TextInput id="no_po" v-model="form.no_po" @input="handlePoInputChange" type="text"
                                placeholder="Scan atau ketik nomor PO..."
                                class="text-center text-lg font-mono tracking-wider pr-12"
                                :class="errors.poNotFound ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                autofocus />
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <Scan class="h-5 w-5 text-slate-400" />
                            </div>
                        </div>
                        <!-- PO Not Found Error Message -->
                        <div v-if="errors.poNotFound"
                            class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center gap-2">
                            <AlertCircle class="h-4 w-4" />
                            {{ errors.poNotFound }}
                        </div>
                    </div>
                </div>

                <!-- Specification Display Section -->
                <div class="p-8 border-b border-slate-200 dark:border-slate-700"
                    :class="isDataFetched ? 'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-slate-700 dark:to-slate-600' : 'bg-gradient-to-r from-slate-50 to-gray-50 dark:from-slate-800 dark:to-slate-700'">
                    <div class="flex items-center gap-4 mb-6">
                        <CheckCircle v-if="isDataFetched" class="h-6 w-6 text-green-600 dark:text-green-400" />
                        <AlertCircle v-else class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">
                            Spesifikasi Produk
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        <!-- Jenis Produk Badge -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-600"
                            :class="!isDataFetched ? 'opacity-50' : ''">
                            <div class="flex items-center gap-3">
                                <div class="bg-orange-100 dark:bg-orange-900/30 p-2 rounded-lg">
                                    <Hash class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Produk</p>
                                    <p class="font-semibold text-slate-900 dark:text-white">
                                        {{ isDataFetched ? (specMmea.produk || '---') : '---' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- No OBC Badge -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-600"
                            :class="!isDataFetched ? 'opacity-50' : ''">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg">
                                    <FileText class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">No OBC</p>
                                    <p class="font-semibold text-slate-900 dark:text-white">
                                        {{ isDataFetched ? (specMmea.no_obc || '---') : '---' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Nomor Plat Badge -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-600"
                            :class="!isDataFetched ? 'opacity-50' : ''">
                            <div class="flex items-center gap-3">
                                <div class="bg-purple-100 dark:bg-purple-900/30 p-2 rounded-lg">
                                    <Car class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Nomor Plat</p>
                                    <p class="font-semibold text-slate-900 dark:text-white">
                                        {{ isDataFetched ? (specMmea.nomor_plat || '---') : '---' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Jumlah Lembar Barang Badge -->
                        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-600"
                            :class="!isDataFetched ? 'opacity-50' : ''">
                            <div class="flex items-center gap-3">
                                <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded-lg">
                                    <Printer class="h-5 w-5 text-green-600 dark:text-green-400" />
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 dark:text-slate-400">Lembar Cetak</p>
                                    <p class="font-semibold text-slate-900 dark:text-white"
                                        :class="specMmea.jml_lbr === 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                        {{ isDataFetched ? specMmea.jml_lbr : '---' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Input Section -->
                <div class="p-8">
                    <form @submit.prevent="formHandler.submitForm" class="space-y-6">

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                            <InputLabel value="Nomor Rim" required class="text-slate-700 dark:text-slate-300" />
                            <InputLabel value="Periksa 1"
                                class="text-slate-700 dark:text-slate-300 col-span-1 md:col-span-2" />
                            <InputLabel value="Periksa 2" required
                                class="text-slate-700 dark:text-slate-300 col-span-1 md:col-span-2" />
                        </div>
                        <!-- NP Input Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="flex flex-col gap-2 col-span-1">
                                <template v-for="(nomorRim, key) in form.no_rim">
                                    <div class="space-y-2">
                                        <TextInput v-model="form.no_rim.key" @input="handleNpInput" disabled :value="nomorRim"
                                            @keydown.enter.prevent type="text" maxlength="4" :disabled="!isDataFetched"
                                            required placeholder="Nomor Rim"
                                            class="text-center font-mono tracking-wider" />
                                    </div>
                                </template>
                            </div>
                            <div class="flex flex-col gap-2 col-span-1 md:col-span-2">
                                <template v-for="(pemeriksa1, key) in form.periksa1">
                                    <div class="space-y-2">
                                        <TextInput v-model="form.periksa1.key" @input="handleNpInput"
                                            @keydown.enter.prevent type="text" maxlength="4" :disabled="!isDataFetched"
                                            placeholder="Max 4 karakter" class="text-center font-mono tracking-wider" />
                                    </div>
                                </template>
                            </div>
                            <div class="flex flex-col gap-2 col-span-1 md:col-span-2">
                                <template v-for="(pemeriksa2, key) in form.periksa1">
                                    <div class="space-y-2">
                                        <TextInput v-model="form.periksa2.key" @input="handleNpInput"
                                            @keydown.enter.prevent type="text" maxlength="4" :disabled="!isDataFetched"
                                            placeholder="Max 4 karakter" class="text-center font-mono tracking-wider" />
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6">
                            <Button type="submit" variant="primary" size="lg"
                                :disabled="!isDataFetched || isLoading || !!errors.labelQuantity || !form.np1"
                                :loading="isLoading" :icon="Printer" class="flex-1">
                                Cetak Label
                            </Button>

                            <Button type="button" variant="outline-secondary" size="lg" :icon="RotateCcw"
                                @click="dataManager.clearForm" class="flex-1">
                                Clear Form
                            </Button>
                        </div>

                    </form>
                </div>

                <!-- Status Information -->
                <div v-if="!isDataFetched && form.no_po"
                    class="p-6 bg-amber-50 dark:bg-amber-900/20 border-t border-amber-200 dark:border-amber-800">
                    <div class="flex items-center gap-3">
                        <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        <p class="text-amber-800 dark:text-amber-200">
                            Menunggu data spesifikasi... Pastikan nomor PO sudah benar.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
    <iframe ref="printFrame" class="hidden"></iframe>
</template>
