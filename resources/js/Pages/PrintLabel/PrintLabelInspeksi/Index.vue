<script setup>
import { ref, inject } from 'vue';
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
import axios from 'axios';

// Props
const props = defineProps({
    listTeam: {
        type: Array,
        default: () => []
    },
    currentTeam: {
        type: Number,
        default: 0
    }
});

// Injections
const swal = inject('$swal');

// Reactive state
const isLoading = ref(false);
const isDataFetched = ref(false);
const timeoutDuration = ref(1000);
const printFrame = ref(null);
const obc_color = ref('#1d4ed8');
const remainingLabels = ref(0);
const labelQuantityError = ref('');
const poNotFoundError = ref('');
const specificationData = ref({
    no_obc: '',
    nomor_plat: '',
    seri: ''
});

// Form state
const form = useForm({
    no_po: '',
    team: props.currentTeam,
    jumlah_label: 0,
    np1: '',
    np2: ''
});

// Fetch specification data when PO is scanned/entered
const fetchSpecification = async () => {
    if (!form.no_po || form.no_po.length < 3) {
        resetSpecification();
        return;
    }

    isLoading.value = true;
    poNotFoundError.value = ''; // Clear previous error

    try {
        // Fetch specification data
        const response = await axios.get(`/api/print-label/inspeksi/${form.no_po}`);
        specificationData.value = {
            no_obc: response.data.no_obc,
            nomor_plat: response.data.nomor_plat,
            seri: response.data.seri
        };

        // Fetch remaining label count
        const remainingLabelResponse = await axios.get(`/api/print-label/inspeksi/count-remaining-label/${form.no_po}`);
        remainingLabels.value = remainingLabelResponse.data;
        form.jumlah_label = remainingLabelResponse.data;

        // Set OBC color based on seri
        const seri = response.data.seri;
        obc_color.value = seri == 3 ? "#b91c1c" : "#1d4ed8";

        // Clear any previous errors
        labelQuantityError.value = '';

        isDataFetched.value = true;

    } catch (error) {
        console.error('Error fetching specification:', error);
        resetSpecification();

        // Show error message below PO input instead of SweetAlert
        poNotFoundError.value = 'Nomor Po Tidak Ditemukan Harap Hubungi admin untuk memperbaharui data order';
    } finally {
        isLoading.value = false;
    }
};

// Reset specification data
const resetSpecification = () => {
    isDataFetched.value = false;
    remainingLabels.value = 0;
    labelQuantityError.value = '';
    poNotFoundError.value = '';
    specificationData.value = {
        no_obc: '',
        nomor_plat: '',
        seri: ''
    };
};

// Validate label quantity
const validateLabelQuantity = () => {
    labelQuantityError.value = '';

    if (!form.jumlah_label || form.jumlah_label <= 0) {
        labelQuantityError.value = 'Jumlah label harus lebih dari 0';
        return false;
    }

    if (form.jumlah_label > remainingLabels.value) {
        labelQuantityError.value = `Jumlah label tidak boleh melebihi sisa label yang tersedia (${remainingLabels.value})`;
        return false;
    }

    return true;
};

// Handle form submission
const submitForm = async () => {
    if (!isDataFetched.value) {
        swal.fire({
            icon: 'warning',
            title: 'Data Belum Lengkap',
            text: 'Silakan scan nomor PO terlebih dahulu',
            confirmButtonText: 'OK'
        });
        return;
    }

    if (!form.team || !form.np1) {
        swal.fire({
            icon: 'warning',
            title: 'Form Belum Lengkap',
            text: 'Silakan lengkapi Team dan NP 1 yang diperlukan',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Validate label quantity
    if (!validateLabelQuantity()) {
        return;
    }

    try {
        const result = await swal.fire({
            icon: 'question',
            title: 'Konfirmasi Cetak Label',
            html: `
                <div class="text-left space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-600 dark:text-slate-400">No PO:</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">${form.no_po}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Team:</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">${getTeamName(form.team)}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Jumlah Label:</span>
                        <span class="text-sm font-bold text-blue-600 dark:text-blue-400">${form.jumlah_label}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-600 dark:text-slate-400">NP1:</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100 font-mono">${form.np1 || '-'}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-600 dark:text-slate-400">NP2:</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100 font-mono">${form.np2 || '-'}</span>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ya, Cetak Label',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b82f6'
        });

        if (result.isConfirmed) {
            isLoading.value = true;

            // Submit form data to API
            await axios.post('/api/print-label/inspeksi/store', form).then(response => {
                    let printLabel = batchSingleLabel(
                        specificationData.value.no_obc,
                        undefined,
                        obc_color.value,
                        undefined,
                        form.np1,
                        form.np2,
                        form.jumlah_label,
                        500
                    );
                    printWithoutDialog(printLabel);

                    if(form.jumlah_label > 10 ) {
                        timeoutDuration.value = Math.round(1000 * (form.jumlah_label / 5));
                    }

                    setTimeout(() => {
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
                        }).then(() => {
                            // Reset form after user acknowledges success
                            clearForm();
                        });

                        isLoading.value = false;
                    }, timeoutDuration.value);
                })
                .catch(error => {
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

                    isLoading.value = false;
                });
        }
    } catch (error) {
        console.error('Error printing labels:', error);
        swal.fire({
            icon: 'error',
            title: 'Gagal Mencetak Label',
            text: 'Terjadi kesalahan saat mencetak label',
            confirmButtonText: 'OK'
        });
    } finally {
        isLoading.value = false;
    }
};

// Clear form
const clearForm = () => {
    form.reset();
    resetSpecification();

    // Simple toast notification for manual clear
    if (!isLoading.value) {
        swal.fire({
            icon: 'info',
            title: 'Form Telah Direset',
            text: 'Semua data telah dihapus',
            timer: 1500,
            showConfirmButton: false
        });
    }
};

// Get team name by ID
const getTeamName = (teamId) => {
    const team = props.listTeam.find(t => t.id == teamId);
    return team ? team.workstation : 'Unknown';
};

// Handle PO input change with debounce
let debounceTimer;
const handlePoInput = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchSpecification();
    }, 500);
};

// Handle NP input changes with debounce to prevent immediate submission
let npDebounceTimer;
const handleNpInput = () => {
    clearTimeout(npDebounceTimer);
    // Clear any existing label quantity errors when NP changes
    labelQuantityError.value = '';
};

// Handle PO input to clear errors
const handlePoInputChange = () => {
    poNotFoundError.value = '';
    handlePoInput();
};

// Handle label quantity input change
const handleLabelQuantityInput = () => {
    // Validate on input change
    setTimeout(() => {
        validateLabelQuantity();
    }, 300);
};

const printWithoutDialog = (content) => {
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
};
</script>

<template>
    <Head title="Print Label Inspeksi" />
    <LoadingOverlay :is-loading="isLoading" />

    <AuthenticatedLayout>
                <div class="min-h-screen py-8 bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-800">
            <div class="container mx-auto px-4 max-w-4xl">

                <!-- Main Form Card -->
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">

                    <!-- Barcode Input Section -->
                    <div class="p-8 border-b border-slate-200 dark:border-slate-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-700">
                        <div class="flex items-center gap-4 mb-4">
                            <Scan class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">
                                Scan Nomor PO
                            </h2>
                        </div>

                        <div class="relative">
                            <InputLabel
                                for="no_po"
                                value="Nomor Production Order"
                                required
                                class="text-slate-700 dark:text-slate-300"
                            />
                            <div class="relative">
                                <TextInput
                                    id="no_po"
                                    v-model="form.no_po"
                                    @input="handlePoInputChange"
                                    type="text"
                                    placeholder="Scan atau ketik nomor PO..."
                                    class="text-center text-lg font-mono tracking-wider pr-12"
                                    :class="poNotFoundError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                    autofocus
                                />
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <Scan class="h-5 w-5 text-slate-400" />
                                </div>
                            </div>
                            <!-- PO Not Found Error Message -->
                            <div v-if="poNotFoundError" class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center gap-2">
                                <AlertCircle class="h-4 w-4" />
                                {{ poNotFoundError }}
                            </div>
                        </div>
                    </div>

                    <!-- Specification Display Section - Always Visible -->
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
                                            {{ specificationData.no_obc || '---' }}
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
                                            {{ specificationData.nomor_plat || '---' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Seri Badge -->
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-600"
                                 :class="!isDataFetched ? 'opacity-50' : ''">
                                <div class="flex items-center gap-3">
                                    <div class="bg-orange-100 dark:bg-orange-900/30 p-2 rounded-lg">
                                        <Hash class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Seri</p>
                                        <p class="font-semibold text-slate-900 dark:text-white">
                                            {{ specificationData.seri || '---' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Remaining Labels Badge -->
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-600"
                                 :class="!isDataFetched ? 'opacity-50' : ''">
                                <div class="flex items-center gap-3">
                                    <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded-lg">
                                        <Printer class="h-5 w-5 text-green-600 dark:text-green-400" />
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">Sisa Label</p>
                                        <p class="font-semibold text-slate-900 dark:text-white"
                                           :class="remainingLabels === 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                            {{ isDataFetched ? remainingLabels : '---' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Input Section -->
                    <div class="p-8">
                        <form @submit.prevent="submitForm" class="space-y-6">

                            <!-- Team Selection -->
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <Users class="h-5 w-5 text-slate-500" />
                                    <InputLabel
                                        for="team"
                                        value="Team Periksa"
                                        required
                                        class="text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                                <Select
                                    id="team"
                                    v-model="form.team"
                                    :disabled="!isDataFetched"
                                    class="text-center"
                                    required
                                >
                                    <option value="" disabled>Pilih Team Periksa</option>
                                    <option
                                        v-for="team in listTeam"
                                        :key="team.id"
                                        :value="team.id"
                                    >
                                        {{ team.workstation }}
                                    </option>
                                </Select>
                            </div>

                            <!-- Label Quantity -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <InputLabel
                                        for="jumlah_label"
                                        value="Jumlah Label"
                                        required
                                        class="text-slate-700 dark:text-slate-300"
                                    />
                                    <span v-if="isDataFetched" class="text-sm text-slate-500 dark:text-slate-400">
                                        Sisa: {{ remainingLabels }}
                                    </span>
                                </div>
                                <TextInput
                                    id="jumlah_label"
                                    v-model="form.jumlah_label"
                                    @input="handleLabelQuantityInput"
                                    type="number"
                                    min="1"
                                    :max="remainingLabels"
                                    :disabled="!isDataFetched"
                                    placeholder="Masukkan jumlah label yang akan dicetak"
                                    class="text-center text-lg font-semibold"
                                    :class="labelQuantityError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                    required
                                />
                                <InputError :message="form.errors.jumlah_label" />
                                <!-- Custom error message for label quantity -->
                                <div v-if="labelQuantityError" class="text-red-600 dark:text-red-400 text-sm mt-1 flex items-center gap-2">
                                    <AlertCircle class="h-4 w-4" />
                                    {{ labelQuantityError }}
                                </div>
                            </div>

                            <!-- NP Input Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <InputLabel
                                        for="np1"
                                        value="NP 1"
                                        required
                                        class="text-slate-700 dark:text-slate-300"
                                    />
                                    <TextInput
                                        id="np1"
                                        v-model="form.np1"
                                        @input="handleNpInput"
                                        @keydown.enter.prevent
                                        type="text"
                                        maxlength="4"
                                        :disabled="!isDataFetched"
                                        required
                                        placeholder="Max 4 karakter"
                                        class="text-center font-mono tracking-wider"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel
                                        for="np2"
                                        value="NP 2"
                                        class="text-slate-700 dark:text-slate-300"
                                    />
                                    <TextInput
                                        id="np2"
                                        v-model="form.np2"
                                        @input="handleNpInput"
                                        @keydown.enter.prevent
                                        type="text"
                                        maxlength="4"
                                        :disabled="!isDataFetched"
                                        placeholder="Max 4 karakter"
                                        class="text-center font-mono tracking-wider"
                                    />
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4 pt-6">
                                <Button
                                    type="submit"
                                    variant="primary"
                                    size="lg"
                                    :disabled="!isDataFetched || isLoading || !!labelQuantityError || !form.np1"
                                    :loading="isLoading"
                                    :icon="Printer"
                                    class="flex-1"
                                >
                                    Cetak Label
                                </Button>

                                <Button
                                    type="button"
                                    variant="outline-secondary"
                                    size="lg"
                                    :icon="RotateCcw"
                                    @click="clearForm"
                                    class="flex-1"
                                >
                                    Clear Form
                                </Button>
                            </div>

                        </form>
                    </div>

                    <!-- Status Information -->
                    <div v-if="!isDataFetched && form.no_po" class="p-6 bg-amber-50 dark:bg-amber-900/20 border-t border-amber-200 dark:border-amber-800">
                        <div class="flex items-center gap-3">
                            <AlertCircle class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            <p class="text-amber-800 dark:text-amber-200">
                                Menunggu data spesifikasi... Pastikan nomor PO sudah benar.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
    <iframe ref="printFrame" class="hidden"></iframe>
</template>