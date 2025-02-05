<template>
    <!-- Judul halaman -->
    <Head title="Cetak Label" />

    <!-- Loading Overlay -->
    <LoadingOverlay :is-loading="loading">
        <div class="flex flex-col items-center space-y-3">
            <div class="text-center">
                <p class="text-6xl font-semibold text-gray-700 dark:text-gray-300">
                    Mencetak Label
                </p>
                <div class="flex items-center justify-center space-x-2 mt-1">
                    <span class="text-6xl text-gray-600 dark:text-gray-400">
                        Rim Nomor : <span class="font-medium text-blue-600 dark:text-blue-400">
                            {{ form.no_rim !== 999 ? form.no_rim : 'INS' }}
                        </span>
                    </span>
                    <span class="text-gray-400 dark:text-gray-600">|</span>
                    <span class="text-6xl text-gray-600 dark:text-gray-400">
                        Potongan : <span class="font-medium text-blue-600 dark:text-blue-400">
                            {{ form.lbr_ptg }}
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </LoadingOverlay>

    <!-- Modal untuk mencetak ulang label -->
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

    <!-- Modal untuk mencetak label kosong -->
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

    <!-- Layout utama dengan autentikasi -->
    <AuthenticatedLayout>
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
                <!-- Scan Input Field - Made Prominent -->
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

                <!-- Information Labels - Reduced Visual Weight -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- PO, OBC, Seri Badges -->
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
                    <!-- Production Info Badges -->
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

                <!-- Action Buttons - Reorganized -->
                <div class="space-y-4">
                    <!-- Primary Actions -->
                    <div class="flex gap-3">
                        <Button
                            type="submit"
                            variant="primary"
                            size="base"
                            :loading="loading"
                            :disabled="loading"
                            class="flex-1"
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

                    <!-- Secondary Actions -->
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

                    <!-- Main CTA -->
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
        <TableVerifikasiPegawai :team="form.team" :date="form.date"/>
    </AuthenticatedLayout>
    <!-- Frame tersembunyi untuk keperluan print -->
    <iframe ref="printFrame" style="display: none"></iframe>
</template>

<script setup>
/**
 * Halaman Cetak Label
 *
 * Komponen ini bertanggung jawab untuk mengelola proses pencetakan label produksi.
 * Fitur utama meliputi:
 * - Pencetakan label baru
 * - Pencetakan ulang label
 * - Pencetakan label manual/kosong
 * - Verifikasi pegawai
 * - Manajemen status order
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

// Props yang diterima dari parent component
const props = defineProps({
    product: Object,        // Data produk yang akan dicetak
    listTeam: Object,      // Daftar tim yang tersedia
    crntTeam: Number,      // ID tim yang aktif
    noRim: Number,         // Nomor rim saat ini
    potongan: String,      // Informasi potongan lembar
    date: String,          // Tanggal operasi
});

// State management menggunakan ref
const printUlangModal = ref(false);    // Kontrol visibilitas modal cetak ulang
const loading = ref(false);            // State loading saat proses
const printManualModal = ref(false);   // Kontrol visibilitas modal cetak manual
const printFrame = ref(null);          // Referensi ke frame cetak tersembunyi
const periksa1Input = ref(null);       // Referensi ke input nomor pegawai

// Inisialisasi form dengan data produk
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

// Menentukan warna OBC berdasarkan seri
const colorObc = form.seri == 3 ? "#b91c1c" : "#1d4ed8";

/**
 * Mencetak konten tanpa menampilkan dialog print browser
 * @param {string} content - Konten HTML yang akan dicetak
 */
const printWithoutDialog = (content) => {
    const iframe = printFrame.value;
    if (!iframe) return;

    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <style>
            @media print {
                @page { margin-left: 3rem; margin-right:3rem; margin-top:1rem; }
                * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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
 * Mengambil data terbaru dari server
 * Memperbarui nomor rim dan informasi potongan
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
 * Menangani submit form cetak label
 * Melakukan validasi, pencetakan, dan pembaruan data
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
            await fetchUpdatedData();
            showNotification('Label berhasil dicetak', 'success');
            periksa1Input.value?.focus();
        } else {
            router.get("/order-besar/po-siap-verif", {}, { preserveState: true });
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Gagal mencetak label', 'error');
    } finally {
        loading.value = false;
    }
};

/**
 * Menampilkan notifikasi kepada pengguna
 * @param {string} message - Pesan yang akan ditampilkan
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
 * Menampilkan konfirmasi dan memproses penyelesaian order
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
 * Mengambil nomor plat dari API eksternal
 */
const fetchNoPlat = async () => {
    try {
        const response = await axios.get(
            `http://10.18.30.233:8300/api/nomor-plat-pcht/${form.obc}`
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
 * Handler untuk keberhasilan pencetakan
 */
const handlePrintSuccess = async () => {
    await fetchUpdatedData();
    showNotification('Label berhasil dicetak', 'success');
};
</script>
