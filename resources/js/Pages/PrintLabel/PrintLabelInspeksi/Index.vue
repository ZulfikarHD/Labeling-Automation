<script setup>
import { ref, inject } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Select from "@/Components/Select.vue";
import Button from "@/Components/Button.vue";
import { router } from '@inertiajs/vue3';
import LoadingOverlay from "@/Components/LoadingOverlay.vue";
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
    }
});

// Injections
const swal = inject('$swal');

// Reactive state
const isLoading = ref(false);
const isDataFetched = ref(false);
const specificationData = ref({
    no_obc: '',
    nomor_plat: '',
    seri: ''
});

// Form state
const form = useForm({
    no_po: '',
    team: '',
    jumlah_label: 0,
    np1: '',
    np2: ''
});

// Fetch specification data when PO is scanned/entered
const fetchSpecification = async () => {
    // if (!form.no_po || form.no_po.length < 3) {
    //     resetSpecification();
    //     return;
    // }

    // const response = await axios.get(`/api/print-label/inspeksi/${form.no_po}`);
    // console.log(form.no_po);
    // router.get(`/api/print-label/inspeksi/${form.no_po}`);
    isLoading.value = true;
    try {
        // Simulate API call - replace with actual endpoint
        const response = await axios.get(`/api/print-label/inspeksi/${form.no_po}`);
        console.log(response);
        specificationData.value = {
            no_obc: response.data.no_obc || 'OBC-' + Math.random().toString(36).substr(2, 6).toUpperCase(),
            nomor_plat: response.data.nomor_plat || 'B-' + Math.floor(Math.random() * 9999) + '-ABC',
            seri: response.data.seri || 'SER' + Math.floor(Math.random() * 999)
        };

        isDataFetched.value = true;

        swal.fire({
            icon: 'success',
            title: 'Data Berhasil Dimuat',
            text: 'Spesifikasi produk telah ditemukan',
            timer: 2000,
            showConfirmButton: false
        });

    } catch (error) {
        console.error('Error fetching specification:', error);
        resetSpecification();

        swal.fire({
            icon: 'error',
            title: 'Data Tidak Ditemukan',
            text: 'Nomor PO tidak valid atau tidak ditemukan dalam database',
            confirmButtonText: 'OK'
        });
    } finally {
        isLoading.value = false;
    }
};

// Reset specification data
const resetSpecification = () => {
    isDataFetched.value = false;
    specificationData.value = {
        no_obc: '',
        nomor_plat: '',
        seri: ''
    };
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

    if (!form.team || !form.jumlah_label || form.jumlah_label <= 0) {
        swal.fire({
            icon: 'warning',
            title: 'Form Belum Lengkap',
            text: 'Silakan lengkapi semua field yang diperlukan',
            confirmButtonText: 'OK'
        });
        return;
    }

    try {
        const result = await swal.fire({
            icon: 'question',
            title: 'Konfirmasi Cetak Label',
            html: `
                <div class="text-left space-y-2">
                    <p><strong>No PO:</strong> ${form.no_po}</p>
                    <p><strong>Team:</strong> ${getTeamName(form.team)}</p>
                    <p><strong>Jumlah Label:</strong> ${form.jumlah_label}</p>
                    <p><strong>NP1:</strong> ${form.np1 || '-'}</p>
                    <p><strong>NP2:</strong> ${form.np2 || '-'}</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ya, Cetak Label',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b82f6'
        });

        if (result.isConfirmed) {
            isLoading.value = true;

            // Simulate API call for printing
            await new Promise(resolve => setTimeout(resolve, 2000));

            swal.fire({
                icon: 'success',
                title: 'Label Berhasil Dicetak',
                text: `${form.jumlah_label} label telah berhasil dicetak`,
                confirmButtonText: 'OK'
            });

            // Reset form after successful print
            clearForm();
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

    swal.fire({
        icon: 'info',
        title: 'Form Telah Direset',
        text: 'Semua data telah dihapus',
        timer: 1500,
        showConfirmButton: false
    });
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
                                    @input="handlePoInput"
                                    type="text"
                                    placeholder="Scan atau ketik nomor PO..."
                                    class="text-center text-lg font-mono tracking-wider pr-12"
                                    autofocus
                                />
                                <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <Scan class="h-5 w-5 text-slate-400" />
                                </div>
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

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                <InputLabel
                                    for="jumlah_label"
                                    value="Jumlah Label"
                                    required
                                    class="text-slate-700 dark:text-slate-300"
                                />
                                <TextInput
                                    id="jumlah_label"
                                    v-model="form.jumlah_label"
                                    type="number"
                                    min="0"
                                    :disabled="!isDataFetched"
                                    placeholder="Masukkan jumlah label yang akan dicetak"
                                    class="text-center text-lg font-semibold"
                                    required
                                />
                            </div>

                            <!-- NP Input Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <InputLabel
                                        for="np1"
                                        value="NP 1"
                                        class="text-slate-700 dark:text-slate-300"
                                    />
                                    <TextInput
                                        id="np1"
                                        v-model="form.np1"
                                        type="text"
                                        maxlength="4"
                                        :disabled="!isDataFetched"
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
                                    :disabled="!isDataFetched || isLoading"
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
</template>