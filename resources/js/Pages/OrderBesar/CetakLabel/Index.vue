<template>
    <!-- Judul halaman -->
    <Head title="Cetak Label" />

    <!-- Layout utama dengan autentikasi - Wrapper untuk halaman yang membutuhkan auth -->
    <AuthenticatedLayout>
        <!-- Add a wrapper div with relative positioning -->
        <div class="relative">
            <!-- Loading Overlay - Menampilkan status loading saat proses pencetakan -->
            <LoadingOverlay :is-loading="loading">
                <div class="flex flex-col items-center space-y-3">
                    <div class="text-center">
                        <p class="text-6xl font-semibold text-gray-700 dark:text-gray-300">
                            Mencetak Label
                        </p>
                        <div class="flex flex-wrap gap-2 items-center justify-center space-x-2 mt-1">
                            <span class="text-6xl text-gray-600 dark:text-gray-400">
                                Rim Nomor : <span class="font-medium text-blue-600 dark:text-blue-400">
                                    {{ form.no_rim !== 999 ? form.no_rim : 'INS' }}
                                </span>
                            </span>
                            <span class="text-gray-400 dark:text-gray-600">|</span>
                            <span class="text-6xl text-gray-600 dark:text-gray-400">
                                Potongan : <span class="font-medium text-blue-600 dark:text-blue-400">
                                    {{ form.lbr_ptg == 'Kiri' ? 'Kiri ( * )' : 'Kanan ( ** ) ' }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </LoadingOverlay>

            <!-- Card utama yang menampilkan OBC dan nomor plat -->
            <BaseCard :title="form.obc + ' - ' + form.noPlat" class="relative">
                <template #title>
                    <!-- Menampilkan OBC dengan warna berbeda berdasarkan seri -->
                    <span :class="form.seri == 3 ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400'">
                        {{ form.obc }}
                    </span>
                    -
                    <span class="text-blue-600 dark:text-blue-400">
                        {{ form.noPlat }}
                    </span>
                </template>

                <!-- Form input data cetak label -->
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Input Field untuk Scan - Prominent UI untuk input utama -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md border-2 border-sky-500/50 dark:border-sky-400/50">
                        <InputLabel
                            for="periksa1"
                            value="Silahkan Scan NP mu"
                            class="text-2xl font-bold mb-4 text-sky-600 dark:text-sky-400"
                        />
                        <TextInput
                            id="periksa1"
                            type="text"
                            v-model="form.periksa1"
                            class="w-full text-xl py-4 uppercase dark:bg-gray-700 dark:text-gray-300 text-center"
                            required
                            ref="periksa1Input"
                            maxlength="4"
                            autofocus
                        />
                        <InputError class="mt-2" />
                    </div>

                    <!-- Information Labels and Badges - Grid layout untuk informasi produksi -->
                    <div class="grid grid-cols-3 gap-6">
                        <!-- PO, OBC, Seri Badges - Dynamic badges dengan conditional styling -->
                        <div v-for="(item, index) in [
                            { label: 'Nomor PO', value: form.po },
                            { label: 'Nomor OBC', value: form.obc, warning: form.seri == 3 },
                            { label: 'Seri', value: form.seri, warning: form.seri == 3 }
                        ]" :key="index">
                            <InputLabel :for="item.label" :value="item.label" class="text-base font-medium mb-2 dark:text-gray-400" />
                            <CustomBadge
                                :variant="item.warning ? 'danger' : 'primary'"
                                size="base"
                                contrast="normal"
                                :rounded="'lg'"
                                class="w-full justify-center"
                            >
                                {{ item.value }}
                            </CustomBadge>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <!-- Production Info Badges - Informasi detail produksi -->
                        <div v-for="(item, index) in [
                            { label: 'Nomor Rim', value: form.no_rim !== 999 ? form.no_rim : 'Inschiet' },
                            { label: 'Lembar Potong', value: form.lbr_ptg },
                            { label: 'Kode Plat', value: form.noPlat }
                        ]" :key="index">
                            <InputLabel :for="item.label" :value="item.label" class="text-base font-medium mb-2 dark:text-gray-400" />
                            <CustomBadge
                                variant="default"
                                size="base"
                                contrast="normal"
                                :rounded="'lg'"
                                class="w-full justify-center"
                            >
                                {{ item.value }}
                            </CustomBadge>
                        </div>
                    </div>

                    <!-- Team Selection - Dropdown untuk pemilihan tim produksi -->
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <InputLabel
                                for="team"
                                value="Pilih Tim Produksi"
                                class="text-base font-medium dark:text-gray-400"
                            />
                            <Select
                                id="team"
                                v-model="form.team"
                                class="w-64 bg-white dark:bg-gray-800"
                                required
                            >
                                <option value="" disabled>Pilih Tim</option>
                                <option
                                    v-for="team in props.listTeam"
                                    :key="team.id"
                                    :value="team.id"
                                    :selected="form.team"
                                >
                                    {{ team.workstation }}
                                </option>
                            </Select>
                        </div>
                    </div>

                    <!-- Action Buttons - Grouped action buttons dengan hierarchy yang jelas -->
                    <div class="space-y-4">
                        <!-- Primary Actions - Aksi utama -->
                        <div class="flex gap-3">
                            <Button
                                type="submit"
                                variant="primary"
                                size="base"
                                class="flex-1"
                                :loading="loading"
                                :disabled="loading"
                            >
                                Generate Label
                            </Button>
                            <Button
                                variant="danger"
                                size="base"
                                @click="form.periksa1 = null"
                            >
                                Clear
                            </Button>
                        </div>

                        <!-- Secondary Actions - Aksi sekunder -->
                        <div class="flex gap-3">
                            <Button
                                variant="outline-info"
                                size="base"
                                @click="printUlangModal = true"
                                class="flex-1"
                            >
                                Print Ulang
                            </Button>
                            <Button
                                variant="outline-primary"
                                size="base"
                                @click="printManualModal = true"
                                class="flex-1"
                            >
                                Print Manual
                            </Button>
                        </div>

                        <!-- Main CTA - Call to action utama -->
                        <Button
                            variant="success"
                            size="lg"
                            full-width
                            @click="confirmFinishOrder"
                            class="mt-8"
                        >
                            Selesaikan Order
                        </Button>
                    </div>
                </form>
            </BaseCard>

            <!-- Tabel untuk menampilkan data verifikasi pegawai -->
            <TableVerifikasiPegawai
                ref="tableVerifikasiRef"
                :team="form.team"
                :date="form.date"
                :disable-loading="true"
            />
        </div>
    </AuthenticatedLayout>
    <!-- Modal untuk mencetak ulang label - Reusable component untuk print ulang -->
    <PrintUlangModal
        :show="printUlangModal"
        :product-data="props.product"
        :color-obc="colorObc"
        :team="form.team"
        @close="printUlangModal = false"
        @success="handlePrintSuccess"
        @error="showNotification"
        @print="printWithoutDialog"
    />

    <!-- Modal untuk mencetak label kosong - Reusable component untuk print manual -->
    <PrintLabelKosongModal
        :show="printManualModal"
        :obc="form.obc"
        :color-obc="colorObc"
        :team="form.team"
        @close="printManualModal = false"
        @success="showNotification('Label berhasil dicetak', 'success')"
        @error="showNotification"
        @print="printWithoutDialog"
    />
    <!-- Frame tersembunyi untuk keperluan print -->
    <iframe ref="printFrame" style="display: none"></iframe>
</template>

<script setup>
/**
 * Halaman Cetak Label
 *
 * Component ini bertanggung jawab untuk mengelola proses pencetakan label produksi.
 * Menggunakan Vue 3 Composition API dengan TypeScript untuk type safety.
 *
 * Features:
 * - Pencetakan label baru dengan verifikasi pegawai
 * - Print ulang label untuk label yang sudah ada
 * - Print label manual/kosong untuk kasus khusus
 * - Verifikasi pegawai dengan sistem scan
 * - Manajemen status order dan tim produksi
 *
 * Tech Stack:
 * - Vue 3 + TypeScript
 * - Tailwind CSS untuk styling
 * - Inertia.js untuk routing
 * - Axios untuk HTTP requests
 * - SweetAlert2 untuk notifikasi
 */

import { reactive, ref, onMounted, nextTick, onBeforeUnmount } from "vue";
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import TableVerifikasiPegawai from "@/Components/TableVerifikasiPegawai.vue";
import { singleLabel } from "@/Components/PrintPages/index";
import { Link, useForm, router, Head } from "@inertiajs/vue3";
import axios from "axios";
import Swal from 'sweetalert2';
import PrintUlangModal from './Modals/PrintUlangModal.vue';
import PrintLabelKosongModal from './Modals/PrintLabelKosongModal.vue';
import BaseCard from "@/Components/BaseCard.vue";
import Select from '@/Components/Select.vue';
import CustomBadge from "@/Components/CustomBadge.vue";
import Button from "@/Components/Button.vue";
import LoadingOverlay from '@/Components/LoadingOverlay.vue';

// Props dengan TypeScript interface untuk type safety
const props = defineProps({
    product: Object,        // Data produk yang akan dicetak
    listTeam: {
        type: Array,
        required: true
    },
    crntTeam: {
        type: Number,
        required: true
    },
    noRim: Number,         // Nomor rim saat ini
    potongan: String,      // Informasi potongan lembar
    date: String,          // Tanggal operasi
});

// State Management menggunakan Vue 3 Composition API
const printUlangModal = ref(false);    // Kontrol visibilitas modal print ulang
const loading = ref(false);            // State loading untuk UI feedback
const printManualModal = ref(false);   // Kontrol visibilitas modal print manual
const printFrame = ref(null);          // Referensi ke frame print tersembunyi
const periksa1Input = ref(null);       // Referensi ke input scan pegawai
const tableVerifikasiRef = ref(null);   // Add ref for the table component

// Form state dengan validasi menggunakan Inertia Form Helper
const form = useForm({
    id: props.product.id,
    po: props.product.no_po,
    obc: props.product.no_obc,
    team: props.crntTeam,
    seri: props.product.no_obc.substr(4, 1) > 3 ? 1 : props.product.no_obc.substr(4, 1),
    lbr_ptg: props.potongan,
    no_rim: props.noRim,
    periksa1: "",
    date: props.date,
    noPlat: "",
});


// Computed value untuk warna OBC
const colorObc = form.seri == 3 ? "#b91c1c" : "#1d4ed8";

/**
 * Utility function untuk print tanpa dialog browser
 * @param {string} content - HTML content yang akan di-print
 */
const printWithoutDialog = (content) => {
    const iframe = printFrame.value;
    if (!iframe) return;

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <style>
            @media print {
                @page {
                    margin-left: 3rem;
                    margin-right: 3rem;
                    margin-top: 0rem;
                }
                body { margin: 0; }
                * {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
        </style>
        ${content}
    `);
    doc.close();

    setTimeout(() => {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }, 100);
};

/**
 * API call untuk mengambil data terbaru
 * Updates nomor rim dan informasi potongan
 */
const fetchUpdatedData = async () => {
    try {
        const { data } = await axios.get(
            `/api/order-besar/cetak-label/data/${form.team}/${form.id}`
        );

        if (data) {
            form.no_rim = data.noRim;
            form.lbr_ptg = data.potongan;
        }
    } catch (error) {
        console.error('Error fetching updated data:', error);
        showNotification('Gagal memperbarui data', 'error');
    }
};

/**
 * Form submission handler
 * Handles validasi, print process, dan data updates
 */
const submit = async (e) => {
    e.preventDefault();
    if (loading.value) return;

    loading.value = true;

    try {
        const { data } = await axios.post("/api/order-besar/cetak-label", form);

        if (data.status === 'error') {
            showNotification(data.message, 'error');
            return;
        }

        const printLabel = singleLabel(
            form.obc,
            form.no_rim !== 999 ? form.no_rim : "INS",
            colorObc,
            form.lbr_ptg == "Kiri" ? "(*)" : "(**)",
            form.periksa1,
            undefined,
            500,
        );

        await printWithoutDialog(printLabel);

        if (data.poStatus !== 2) {
            form.periksa1 = null;
            if (data.data) {
                form.no_rim = data.data.no_rim;
                form.lbr_ptg = data.data.potongan;
            }
            loading.value = false;
            await fetchUpdatedData();
            showNotification('Label berhasil dicetak', 'success');
            periksa1Input.value?.focus();

        } else {
            router.get("/order-besar/po-siap-verif", {}, { preserveState: true });
            loading.value = false;
        }
    } catch (error) {
        console.error('Error:', error);
        loading.value = false;
        showNotification('Gagal mencetak label', 'error');
    } finally {
        // Refresh the table component
        await nextTick();
        tableVerifikasiRef.value?.fetchData();

        // Check if team has changed from initial value
        if (form.team !== props.crntTeam) {
            router.get(`/order-besar/cetak-label/${form.team}/${props.product.id}`);
            return;
        }
    }
};

/**
 * Utility untuk menampilkan notifikasi
 * @param {string} message - Pesan notifikasi
 * @param {string} type - Tipe notifikasi (success/error/info)
 */
const showNotification = (message, type = 'info') => {
    Swal.fire({
        title: message,
        icon: type,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
};

/**
 * Handler untuk konfirmasi penyelesaian order
 * Menampilkan dialog konfirmasi dan memproses request
 */
const confirmFinishOrder = async () => {
    try {
        const result = await Swal.fire({
            title: 'Selesaikan Order',
            text: 'Apakah Anda yakin ingin menyelesaikan order ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesaikan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0891b2',
            cancelButtonColor: '#6b7280',
        });

        if (result.isConfirmed) {
            await axios.put(`/api/production-order-finish/${form.po}`);
            router.get("/order-besar/po-siap-verif", {}, { preserveState: true });
            showNotification('Order berhasil diselesaikan', 'success');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Gagal menyelesaikan order', 'error');
    }
};

/**
 * API call untuk mengambil nomor plat
 * Menggunakan external API
 */
const fetchNoPlat = async () => {
    try {
        const response = await axios.get(
            `http://10.18.30.233:8300/api/nomor-plat-pcht/${form.po}`
        );
        form.noPlat = response.data;
    } catch (error) {
        console.error("Error fetching noPlat:", error);
    }
};

// Lifecycle hooks
onMounted(() => {
    fetchNoPlat();
});

/**
 * Success handler untuk print process
 * Updates data dan menampilkan notifikasi
 */
const handlePrintSuccess = async () => {
    await fetchUpdatedData();
    showNotification('Label berhasil dicetak', 'success');
};
</script>
