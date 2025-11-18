<script setup>
/**
 * TODO : Cleanup Code
 * TODO : Add Checklist For Print Selected Item Only
 * TODO : Get Array From No Rim
 *
 */
import { ref, inject, computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Select from "@/Components/Select.vue";
import Button from "@/Components/Button.vue";
import LoadingOverlay from "@/Components/LoadingOverlay.vue";
import { LabelMmea } from "@/Components/PrintPages/index";
import { fetchDataOrder, storeLabelData, storeProductData } from "./ApiServices"
import { initDataQc, initOrderSpec } from './InitDataLabel';
import {
    Scan,
    FileText,
    Car,
    Hash,
    Users,
    Printer,
    RotateCcw,
    CheckCircle,
    AlertCircle,
    Plus,
    Trash2
} from 'lucide-vue-next';

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
const printMode = ref("both");
const rowCount = ref(1);

const specMmea = ref({
    no_po: nomorPo.value,
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
    form.jml_label = dataQc.jml_label;

    // Initialize form with data from QC or set defaults
    rowCount.value = Object.keys(dataQc.no_rim).length || 1;

    // Reset form objects
    form.no_rim = {};
    form.periksa1 = {};
    form.periksa2 = {};
    form.jml_kemas = {};

    singleLabelForm.no_rim = {};
    singleLabelForm.periksa1 = {};
    singleLabelForm.periksa2 = {};
    singleLabelForm.jml_kemas = {};

    // Populate form with QC data or defaults
    for (let i = 1; i <= rowCount.value; i++) {
        form.no_rim[`no_${i}`] = dataQc.no_rim[`no_${i}`] || i;
        form.periksa1[`np_${i}`] = dataQc.periksa1[`np_${i}`] || "";
        form.periksa2[`np_${i}`] = dataQc.periksa2[`np_${i}`] || "";
        form.jml_kemas[`no_${i}`] = dataQc.jml_kemas[`no_${i}`] || 300;

        singleLabelForm.no_rim[`no_${i}`] = dataQc.no_rim[`no_${i}`] || i;
        singleLabelForm.periksa1[`np_${i}`] = dataQc.periksa1[`np_${i}`] || "";
        singleLabelForm.periksa2[`np_${i}`] = dataQc.periksa2[`np_${i}`] || "";
        singleLabelForm.jml_kemas[`no_${i}`] = dataQc.jml_kemas[`no_${i}`] || 300;
    }
}

const clearForm = () => {
    nomorPo.value = '';
    rowCount.value = 1;
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
    specMmea.value.jml_lbr = 0;
    specMmea.value.no_plat = "-";
}

const addRow = () => {
    rowCount.value++;
    const rowIndex = rowCount.value;

    // Add new entries to form objects
    form.no_rim[`no_${rowIndex}`] = rowIndex;
    form.periksa1[`np_${rowIndex}`] = "";
    form.periksa2[`np_${rowIndex}`] = "";
    form.jml_kemas[`no_${rowIndex}`] = 300;

    // Update jml_label to match row count
    form.jml_label = rowCount.value;

    // Also add to singleLabelForm for consistency
    singleLabelForm.no_rim[`no_${rowIndex}`] = rowIndex;
    singleLabelForm.periksa1[`np_${rowIndex}`] = "";
    singleLabelForm.periksa2[`np_${rowIndex}`] = "";
    singleLabelForm.jml_kemas[`no_${rowIndex}`] = 300;
}

const removeRow = (rowIndex) => {
    if (rowCount.value <= 1) {
        // Don't allow removing the last row
        return;
    }

    // Remove entries from form objects
    delete form.no_rim[`no_${rowIndex}`];
    delete form.periksa1[`np_${rowIndex}`];
    delete form.periksa2[`np_${rowIndex}`];
    delete form.jml_kemas[`no_${rowIndex}`];

    // Also remove from singleLabelForm
    delete singleLabelForm.no_rim[`no_${rowIndex}`];
    delete singleLabelForm.periksa1[`np_${rowIndex}`];
    delete singleLabelForm.periksa2[`np_${rowIndex}`];
    delete singleLabelForm.jml_kemas[`no_${rowIndex}`];

    // Reindex the remaining rows
    const newNoRim = {};
    const newPeriksa1 = {};
    const newPeriksa2 = {};
    const newJmlKemas = {};

    const newSingleNoRim = {};
    const newSinglePeriksa1 = {};
    const newSinglePeriksa2 = {};
    const newSingleJmlKemas = {};

    let newIndex = 1;
    for (let i = 1; i <= rowCount.value; i++) {
        if (i !== rowIndex) {
            newNoRim[`no_${newIndex}`] = newIndex;
            newPeriksa1[`np_${newIndex}`] = form.periksa1[`np_${i}`] || "";
            newPeriksa2[`np_${newIndex}`] = form.periksa2[`np_${i}`] || "";
            newJmlKemas[`no_${newIndex}`] = form.jml_kemas[`no_${i}`] || 300;

            newSingleNoRim[`no_${newIndex}`] = newIndex;
            newSinglePeriksa1[`np_${newIndex}`] = singleLabelForm.periksa1[`np_${i}`] || "";
            newSinglePeriksa2[`np_${newIndex}`] = singleLabelForm.periksa2[`np_${i}`] || "";
            newSingleJmlKemas[`no_${newIndex}`] = singleLabelForm.jml_kemas[`no_${i}`] || 300;

            newIndex++;
        }
    }

    form.no_rim = newNoRim;
    form.periksa1 = newPeriksa1;
    form.periksa2 = newPeriksa2;
    form.jml_kemas = newJmlKemas;

    singleLabelForm.no_rim = newSingleNoRim;
    singleLabelForm.periksa1 = newSinglePeriksa1;
    singleLabelForm.periksa2 = newSinglePeriksa2;
    singleLabelForm.jml_kemas = newSingleJmlKemas;

    rowCount.value--;

    // Update jml_label to match row count
    form.jml_label = rowCount.value;
}

const printSingleLabel = (key) => {
    // Extract row index from key (e.g., "no_1" -> 1)
    const rowIndex = key.replace('no_', '');

    // Update Form Berdasarkan Nomor PO
    singleLabelForm.no_po = form.no_po;
    singleLabelForm.no_rim = { no_1: form.no_rim[key] };
    singleLabelForm.periksa1 = { np_1: convNp(form.periksa1[`np_${rowIndex}`] || '') };
    singleLabelForm.periksa2 = { np_1: convNp(form.periksa2[`np_${rowIndex}`] || '') };
    singleLabelForm.jml_kemas = { no_1: form.jml_kemas[key] };
    singleLabelForm.jml_label = 1;

    formHandler.submitForm('single');
}

const convNp = (np) => {
    const lengthNp = np.length;
    if(lengthNp > 4) {
        const firstLetter = np.slice(0, 1);
        const lastFour = np.slice((lengthNp - 4), lengthNp);
        np = firstLetter + lastFour;
    }
    return np;
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
            await storeProductData(specMmea.value);

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
                printMode.value
            );
        } else if (print_type == "single") {
            return LabelMmea(
                specMmea.value.no_obc,
                obcColor,
                singleLabelForm.periksa1,
                singleLabelForm.periksa2,
                singleLabelForm.jml_label,
                singleLabelForm.jml_kemas,
                printMode.value
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

                    <div class="relative mt-4">
                        <InputLabel for="no_po" value="Mode Cetak" required
                            class="text-slate-700 dark:text-slate-300" />
                        <div class="relative">
                            <Select id="print_mode" v-model="printMode">
                                <option value="both">Periksa 1 & Periksa 2</option>
                                <option value="p1_only">Periksa 1</option>
                                <option value="p2_only">Periksa 2</option>
                            </Select>
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

                        <div class="grid grid-cols-1 md:grid-cols-7 gap-6">
                            <InputLabel value="Nomor Rim" required class="text-slate-700 dark:text-slate-300" />
                            <InputLabel value="Lbr Kirim" required class="text-slate-700 dark:text-slate-300" />
                            <InputLabel value="Periksa 1" required
                                class="text-slate-700 dark:text-slate-300 col-span-1 md:col-span-2" />
                            <InputLabel value="Periksa 2" required
                                class="text-slate-700 dark:text-slate-300 col-span-1 md:col-span-2" />
                            <InputLabel value="Print Satuan" required
                                class="text-slate-700 dark:text-slate-300 col-span-1 md:col-span-1" />
                        </div>
                        <!-- NP Input Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                            <div class="flex flex-col gap-2 col-span-1">
                                <template v-for="(nomorRim, key) in form.no_rim" :key="key">
                                    <div class="space-y-2">
                                        <TextInput v-model="form.no_rim[key]" disabled
                                            :value="nomorRim" @keydown.enter.prevent type="text"
                                            :disabled="!isDataFetched || specMmea.no_obc == '-'" required
                                            placeholder="Nomor Rim" class="text-center font-mono tracking-wider" />
                                    </div>
                                </template>
                            </div>
                            <div class="flex flex-col gap-2 col-span-1">
                                <template v-for="(lbrKirim, key) in form.jml_kemas" :key="key">
                                    <div class="space-y-2">
                                        <TextInput v-model="form.jml_kemas[key]"
                                            :value="lbrKirim" @keydown.enter.prevent type="text"
                                            :disabled="!isDataFetched || specMmea.no_obc == '-'" required
                                            placeholder="Lembar Kirim" class="text-center font-mono tracking-wider" />
                                    </div>
                                </template>
                            </div>
                            <div class="flex flex-col gap-2 col-span-2 md:col-span-4">
                                <template v-for="(pemeriksa1, key) in form.periksa1" :key="key">
                                    <div class="space-y-2 flex gap-2">
                                        <TextInput v-model="form.periksa1[key]" @input="handleNpInput"
                                            required @keydown.enter.prevent type="text"
                                            :disabled="!isDataFetched
                                                || specMmea.no_obc == '-'
                                                || (form.periksa1['np_' + (key.substring(3, 4) - 1)] == '' && key !== 'np_0')
                                                || (form.periksa2['np_' + (key.substring(3, 4) - 1)] == '' && key !== 'np_0')" placeholder="Max 4 karakter"
                                            class="text-center font-mono tracking-wider" />

                                        <TextInput v-model="form.periksa2[key]" @input="handleNpInput" required
                                            @keydown.enter.prevent type="text"
                                            :disabled="!isDataFetched
                                                || specMmea.no_obc == '-'
                                                || (form.periksa1['np_' + (key.substring(3, 4) - 1)] == '' && key !== 'np_0')
                                                || (form.periksa2['np_' + (key.substring(3, 4) - 1)] == '' && key !== 'np_0')" placeholder="Max 4 karakter"
                                            class="text-center font-mono tracking-wider" />
                                    </div>
                                </template>
                            </div>
                            <div class="flex flex-col gap-2 col-span-1">
                                <template v-for="(nomorRim, key) in form.no_rim" :key="key">
                                    <Button type="button" @click="printSingleLabel(key)" variant="primary"
                                        size="sm"
                                        :disabled="!isDataFetched || isLoading || !form.periksa1['np_' + key.replace('no_', '')] || !form.periksa2['np_' + key.replace('no_', '')]"
                                        :loading="isLoading" :icon="Printer" class="flex-1 mb-3 mt-0.5 ml-2 mr-2">
                                    </Button>
                                </template>
                            </div>
                        </div>

                        <!-- Row Management Buttons -->
                        <div class="relative pt-6 pb-2">
                            <div class="flex items-center justify-between gap-4 p-4 bg-gradient-to-r from-slate-50 to-gray-50 dark:from-slate-800/50 dark:to-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                        <Hash class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Jumlah Rim</p>
                                        <p class="text-lg font-bold text-slate-900 dark:text-white">{{ rowCount }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button
                                        type="button"
                                        @click="addRow"
                                        :disabled="!isDataFetched || isLoading || rowCount >= 5"
                                        class="group relative inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-green-500 hover:from-emerald-600 hover:to-green-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-emerald-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:hover:shadow-md"
                                    >
                                        <Plus class="h-5 w-5 transition-transform group-hover:rotate-90" />
                                        <span>Tambah Baris</span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="removeRow(rowCount)"
                                        :disabled="!isDataFetched || isLoading || rowCount <= 1"
                                        class="group relative inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 ease-in-out focus:outline-none focus:ring-4 focus:ring-red-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:hover:shadow-md"
                                    >
                                        <Trash2 class="h-5 w-5 transition-transform group-hover:scale-110" />
                                        <span>Hapus Baris</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6">
                            <Button type="button" @click="formHandler.submitForm('batch')" variant="primary" size="lg"
                                :disabled="!isDataFetched || isLoading" :loading="isLoading" :icon="Printer"
                                class="flex-1">
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
