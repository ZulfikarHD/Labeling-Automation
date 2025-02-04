<template>
    <!-- Judul halaman -->
    <Head title="Cetak Label" />

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

    <!-- Layout utama -->
    <AuthenticatedLayout>
        <!-- Card utama dengan judul OBC dan nomor plat -->
        <BaseCard :title="form.obc + ' - ' + form.noPlat">
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

            <!-- Form utama -->
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Pemilihan tim -->
                <div>
                    <InputLabel for="team" value="Tim" class="text-xl font-bold mb-3 dark:text-gray-300" />
                    <Select
                        v-model="form.team"
                        size="base"
                    >
                        <option v-for="team in props.listTeam" :key="team.id" :value="team.id">
                            {{ team.workstation }}
                        </option>
                    </Select>
                </div>

                <!-- Grid informasi pesanan -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- Input nomor PO (disabled) -->
                    <div>
                        <InputLabel for="po" value="Nomor PO" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <TextInput
                            id="po"
                            ref="po"
                            v-model="form.po"
                            type="number"
                            class="w-full bg-gray-50 dark:bg-gray-700 text-center dark:text-gray-300"
                            disabled
                        />
                    </div>

                    <!-- Input nomor OBC (disabled) -->
                    <div>
                        <InputLabel for="obc" value="Nomor OBC" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <TextInput
                            id="obc"
                            ref="obc"
                            v-model="form.obc"
                            type="text"
                            class="w-full bg-gray-50 dark:bg-gray-700 text-center dark:text-gray-300"
                            disabled
                        />
                    </div>

                    <!-- Input seri (disabled) -->
                    <div>
                        <InputLabel for="seri" value="Seri" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <TextInput
                            id="seri"
                            ref="seri"
                            v-model="form.seri"
                            type="number"
                            class="w-full bg-gray-50 dark:bg-gray-700 text-center dark:text-gray-300"
                            disabled
                        />
                    </div>
                </div>

                <!-- Grid informasi tambahan -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- Nomor rim dengan kondisional tampilan -->
                    <div>
                        <InputLabel for="no_rim" value="Nomor Rim" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <template v-if="form.no_rim !== 999">
                            <TextInput
                                id="no_rim"
                                ref="no_rim"
                                v-model="form.no_rim"
                                type="number"
                                class="w-full bg-gray-50 dark:bg-gray-700 text-center font-bold dark:text-gray-300"
                                disabled
                            />
                        </template>
                        <template v-else>
                            <TextInput
                                type="text"
                                value="Inschiet"
                                class="w-full bg-gray-50 dark:bg-gray-700 text-center font-bold dark:text-gray-300"
                                disabled
                            />
                        </template>
                    </div>

                    <!-- Input lembar potong (disabled) -->
                    <div>
                        <InputLabel for="lbr_ptg" value="Lembar Potong" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <TextInput
                            id="lbr_ptg"
                            ref="lbr_ptg"
                            v-model="form.lbr_ptg"
                            type="text"
                            class="w-full bg-gray-50 dark:bg-gray-700 text-center font-bold dark:text-gray-300"
                            disabled
                        />
                    </div>

                    <!-- Input kode plat (disabled) -->
                    <div>
                        <InputLabel for="noPlat" value="Kode Plat" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <TextInput
                            id="noPlat"
                            ref="noPlat"
                            v-model="form.noPlat"
                            type="number"
                            class="w-full bg-gray-50 dark:bg-gray-700 text-center font-bold dark:text-gray-300"
                            disabled
                        />
                    </div>
                </div>

                <!-- Input nomor pegawai -->
                <div>
                    <InputLabel for="periksa1" value="Silahkan Scan NP mu" class="text-xl font-bold mb-3 dark:text-gray-300" />
                    <TextInput
                        id="periksa1"
                        type="text"
                        v-model="form.periksa1"
                        class="w-full uppercase dark:bg-gray-700 dark:text-gray-300"
                        required
                        ref="periksa1Input"
                        autofocus
                    />
                    <InputError class="mt-2" />
                </div>

                <!-- Grup tombol aksi -->
                <div class="flex justify-center gap-4">
                    <!-- Tombol hapus input -->
                    <button
                        type="button"
                        @click="form.periksa1 = null"
                        class="px-6 py-3 text-white bg-violet-600 dark:bg-violet-700 hover:bg-violet-700 dark:hover:bg-violet-800 rounded-lg transition-colors"
                    >
                        Clear
                    </button>

                    <!-- Tombol generate label -->
                    <button
                        type="submit"
                        :disabled="loading"
                        :class="[
                            'px-6 py-3 text-white bg-green-600 dark:bg-green-700 hover:bg-green-700 dark:hover:bg-green-800 rounded-lg transition-colors',
                            loading ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                    >
                        {{ loading ? 'Memproses...' : 'Generate' }}
                    </button>

                    <!-- Tombol cetak ulang -->
                    <button
                        type="button"
                        @click="printUlangModal = true"
                        class="px-6 py-3 text-blue-600 bg-inherit dark:bg-inherit border border-blue-600 dark:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-800 rounded-lg transition-colors"
                    >
                        Print Ulang
                    </button>

                    <!-- Tombol cetak manual -->
                    <button
                        type="button"
                        @click="printManualModal = true"
                        class="px-6 py-3 text-green-600 bg-inherit dark:bg-inherit border border-green-600 dark:border-green-700 hover:bg-green-50 dark:hover:bg-green-800 rounded-lg transition-colors"
                    >
                        Print Manual
                    </button>
                </div>

                <!-- Tombol selesaikan order -->
                <button
                    type="button"
                    @click="confirmFinishOrder"
                    class="w-full px-6 py-3 text-white bg-cyan-600 dark:bg-cyan-700 hover:bg-cyan-700 dark:hover:bg-cyan-800 rounded-lg transition-colors"
                >
                    Selesaikan Order
                </button>
            </form>
        </BaseCard>
        <!-- Tabel verifikasi pegawai -->
        <TableVerifikasiPegawai :team="form.team" :date="form.date"/>
    </AuthenticatedLayout>
    <!-- Frame tersembunyi untuk keperluan print -->
    <iframe ref="printFrame" style="display: none"></iframe>
</template>

<script setup>
/**
 * Halaman Cetak Label
 *
 * Komponen ini menangani proses pencetakan label untuk order produksi.
 * Mendukung pencetakan label baru, pencetakan ulang, dan pencetakan manual.
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

// Props yang diterima dari parent
const props = defineProps({
    product: Object,        // Data produk
    listTeam: Object,      // Daftar tim yang tersedia
    crntTeam: Number,      // ID tim aktif
    noRim: Number,         // Nomor rim saat ini
    potongan: String,      // Info potongan lembar
    date: String,          // Tanggal saat ini
});

// State management
const printUlangModal = ref(false);    // Kontrol modal cetak ulang
const loading = ref(false);            // State loading
const printManualModal = ref(false);   // Kontrol modal cetak manual
const printFrame = ref(null);          // Referensi frame cetak
const periksa1Input = ref(null);       // Referensi input NP

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

// Warna OBC berdasarkan seri
const colorObc = form.seri == 3 ? "#b91c1c" : "#1d4ed8";

/**
 * Mencetak konten tanpa dialog print browser
 * @param {string} content - HTML konten yang akan dicetak
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
 * Menampilkan notifikasi ke user
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
 * Konfirmasi dan proses penyelesaian order
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
 * Handler untuk keberhasilan cetak
 */
const handlePrintSuccess = async () => {
    await fetchUpdatedData();
    showNotification('Label berhasil dicetak', 'success');
};
</script>
