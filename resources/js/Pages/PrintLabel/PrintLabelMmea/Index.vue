<script setup>
/**
 * TODO : Cleanup Code
 * TODO : Add Checklist For Print Selected Item Only
 * TODO : Get Array From No Rim
 * 
 */
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
import { LabelMmea } from "@/Components/PrintPages/index";
import { fetchDataOrder, fetchQcData, storeLabelData } from "./ApiServices"
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
import { initDataQc, initOrderSpec, resetOrderSpec } from './InitDataLabel';

// Constants
const PRINT_TIMEOUT_BASE = 1000;
const DEBOUNCE_DELAY = 500;
const VALIDATION_DELAY = 300;
const printFrame = ref(null);
// Injections
const swal = inject('$swal');

// Computed properties
const isDataFetched = computed(() => !!specMmea.value.no_obc);
const obcColor = "#1d4ed8";
const printTimeout = computed(() =>
    form.jumlah_label > 10
        ? Math.round(PRINT_TIMEOUT_BASE * (form.jumlah_label / 5))
        : PRINT_TIMEOUT_BASE
);

const isLoading = ref(false);
const nomorPo = ref(0);

const specMmea = ref({
    produk: "-",
    no_obc: "-",
    jml_lbr: 0,
    no_plat: "-",
});

const form = useForm({
    no_po: nomorPo.value,
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
    jml_label: 0,
});

const singleLabelForm = useForm({
    no_po: nomorPo.value,
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
    jml_label: 1,
});

const errors = ref({
    poNotFound: '',
});

const InitDataLabel = async () => {
    isLoading.value = true;
    errors.value.poNotFound = '';

    try {
        // Ambil Data Order MMEA
        const dataOrder = await (fetchDataOrder(nomorPo.value));

        // Initialize Spesifikasi Berdasarkan Data Order
        const specOrder = await (initOrderSpec(dataOrder));

        // Ambil Data Qc Jika Sudah Ada Di Database
        const dataQc = await (initDataQc(dataOrder));

        // Update Form Label Berdasarkan Data QC
        updateFormContent(dataQc);

        // Update Form Spesifikasi Order
        specMmea.value = specOrder;
    } catch (error) {
        resetSpecMmea();
        form.reset();
        console.error('Error fetching specification:', error);
        errors.value.poNotFound = 'Nomor Po Tidak Ditemukan Harap Hubungi admin untuk memperbaharui data order';
    } finally {
        isLoading.value = false;
    }
}

const updateFormContent = (dataQc) => {
    form.no_po = dataQc.no_po;
    form.no_rim = dataQc.no_rim;
    form.periksa1 = dataQc.periksa1;
    form.periksa2 = dataQc.periksa2;
    form.jml_kemas = dataQc.jml_kemas;
    form.jml_label = dataQc.jml_label;
}

const clearForm = () => {
    form.reset();
    resetSpecMmea();
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

const resetSpecMmea = () => {
    specMmea.value.produk = "-";
    specMmea.value.no_obc = "-";
    specMmea.value.jml_lbr    = 0;
    specMmea.value.no_plat    = "-";
}

const printSingleLabel = (no_rim) => {
    // Update Form Berdasarkan Nomor PO
    singleLabelForm.no_po = form.no_po;
    singleLabelForm.no_rim = { no_1: form.no_rim[`no_${no_rim}`] };
    singleLabelForm.periksa1 = { np_1: form.periksa1[`np_${no_rim}`] };
    singleLabelForm.periksa2 = { np_1: form.periksa2[`np_${no_rim}`] };
    singleLabelForm.jml_kemas = { no_1: form.jml_kemas[`no_${no_rim}`] };
    singleLabelForm.jml_label = 1;

    formHandler.submitForm('single');
}

// Form submission
const formHandler = {
    async submitForm(print_type) {
        // if (!validation.validateForm()) {
        //     return;
        // }

        const confirmed = await this.showConfirmationDialog();
        if (!confirmed) return;

        isLoading.value = true;

        try {
            if (print_type == "batch") {
                await storeLabelData(form);
            } else if (print_type == "single") {
                await storeLabelData(singleLabelForm);
            }

            const printContent = printService.generatePrintContent(print_type);
            printService.printWithoutDialog(printContent);

            setTimeout(() => {
                this.showSuccessMessage();
                if (print_type == "batch") {
                    clearForm();
                }
                isLoading.value = false;
            }, printTimeout.value);

        } catch (error) {
            this.handleSubmissionError(error);
            isLoading.value = false;
        }
    },

    async showConfirmationDialog() {
        const result = await swal.fire({
            icon: 'question',
            title: 'Konfirmasi Cetak Label',
            showCancelButton: true,
            confirmButtonText: 'Ya, Cetak Label',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b82f6'
        });

        return result.isConfirmed;
    },

    showSuccessMessage() {
        swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Label Berhasil Dibuat',
            customClass: {
                popup: 'rounded-lg',
                title: 'text-xl font-bold text-green-600 mb-4',
                htmlContainer: 'text-base text-gray-600',
                confirmButton: 'bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg px-4 py-2'
            },
            iconColor: '#22c55e'
        });
    },

    handleSubmissionError(error) {
        let errorMessage = 'Terjadi kesalahan';

        if (error.response) {
            if (error.response.status === 422) {
                const errors = error.response.data.errors;
                errorMessage = Object.values(errors).flat().join('<br>');
            } else {
                errorMessage = error.response.data.message || 'Terjadi kesalahan pada server';
            }
        }

        swal.fire({
            icon: 'error',
            title: 'Gagal',
            html: `<div class="text-left">
                <p class="text-red-500 font-medium text-lg mb-2">Error:</p>
                <ul class="text-gray-700 text-base space-y-1 list-disc pl-5">
                    ${errorMessage.split('<br>').map(error => `<li>${error}</li>`).join('')}
                </ul>
            </div>`,
            customClass: {
                popup: 'rounded-lg',
                title: 'text-xl font-bold text-red-600 mb-4',
                htmlContainer: 'p-4',
                confirmButton: 'bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg px-4 py-2'
            },
            iconColor: '#ef4444'
        });
    }
};

// Print functionality
const printService = {
    printWithoutDialog(content) {
        const iframe = printFrame.value;
        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(`<style>
            @media print {
                @page {
                    margin-left: 3rem;
                    margin-right: 3rem;
                    margin-top: 0rem;
                }
                body { margin: 0; }
                header, footer { display: none !important; }
                * {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
        </style>
        ${content}`);
        doc.close();
        iframe.contentWindow.focus();

        setTimeout(() => {
            iframe.contentWindow.print();
        }, 1000);
    },

    generatePrintContent(print_type) {
        if (print_type == "batch") {
            return LabelMmea(
                specMmea.value.no_obc,
                obcColor,
                form.periksa1,
                form.periksa2,
                form.jml_label,
                form.jml_kemas,
            );
        } else if (print_type == "single") {
            return LabelMmea(
                specMmea.value.no_obc,
                obcColor,
                singleLabelForm.periksa1,
                singleLabelForm.periksa2,
                singleLabelForm.jml_label,
                singleLabelForm.jml_kemas,
            );
        }
    }
};


// Debounced handlers
let debounceTimer;
const handlePoInput = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        // dataManager.fetchSpecification();
        InitDataLabel();
    }, DEBOUNCE_DELAY);
};

const handlePoInputChange = () => {
    errors.value.poNotFound = '';
    handlePoInput();
};

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
                            <TextInput id="no_po" v-model="nomorPo" @input="handlePoInputChange" type="text"
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

                        <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                            <InputLabel value="Nomor Rim" required class="text-slate-700 dark:text-slate-300" />
                            <InputLabel value="Periksa 1" required
                                class="text-slate-700 dark:text-slate-300 col-span-1 md:col-span-2" />
                            <InputLabel value="Periksa 2" required
                                class="text-slate-700 dark:text-slate-300 col-span-1 md:col-span-2" />
                            <InputLabel value="Print Satuan" required
                                class="text-slate-700 dark:text-slate-300 col-span-1 md:col-span-1" />
                        </div>
                        <!-- NP Input Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                            <div class="flex flex-col gap-2 col-span-1">
                                <template v-for="(nomorRim, key) in form.no_rim">
                                    <div class="space-y-2">
                                        <TextInput v-model="form.no_rim[key]" @input="handleNpInput" disabled
                                            :value="nomorRim" @keydown.enter.prevent type="text" maxlength="4"
                                            :disabled="!isDataFetched || specMmea.no_obc == '-'" required placeholder="Nomor Rim"
                                            class="text-center font-mono tracking-wider" />
                                    </div>
                                </template>
                            </div>
                            <div class="flex flex-col gap-2 col-span-1 md:col-span-2">
                                <template v-for="(pemeriksa1, key) in form.periksa1">
                                    <div class="space-y-2">
                                        <TextInput v-model="form.periksa1[key]" @input="handleNpInput" :key="key"
                                            required @keydown.enter.prevent type="text" maxlength="4"
                                            :disabled="!isDataFetched || specMmea.no_obc == '-'" placeholder="Max 4 karakter"
                                            class="text-center font-mono tracking-wider" />
                                    </div>
                                </template>
                            </div>
                            <div class="flex flex-col gap-2 col-span-1 md:col-span-2">
                                <template v-for="(pemeriksa2, key) in form.periksa2">
                                    <div class="space-y-2">
                                        <TextInput v-model="form.periksa2[key]" @input="handleNpInput" required
                                            @keydown.enter.prevent type="text" maxlength="4" :disabled="!isDataFetched || specMmea.no_obc == '-'"
                                            placeholder="Max 4 karakter" class="text-center font-mono tracking-wider" />
                                    </div>
                                </template>
                            </div>
                            <div class="flex flex-col gap-2 col-span-1">
                                <template v-for="(nomorRim, key) in form.no_rim">
                                    <Button type="button" @click="printSingleLabel(nomorRim)" variant="primary"
                                        size="sm"
                                        :disabled="!isDataFetched || isLoading || form.periksa1['np_'+nomorRim] == '' || form.periksa2['np_'+nomorRim] == ''"
                                        :loading="isLoading" :icon="Printer" class="flex-1 mb-3 mt-0.5 ml-2 mr-2">
                                    </Button>
                                </template>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6">
                            <Button type="button" @click="formHandler.submitForm('batch')" variant="primary" size="lg"
                                :disabled="!isDataFetched || isLoading"
                                :loading="isLoading" :icon="Printer" class="flex-1">
                                Cetak Label
                            </Button>

                            <Button type="button" variant="outline-secondary" size="lg" :icon="RotateCcw"
                                @click="clearForm" class="flex-1">
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
