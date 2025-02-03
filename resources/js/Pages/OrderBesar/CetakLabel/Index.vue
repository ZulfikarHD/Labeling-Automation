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

    <!-- Tambahkan baris ini setelah PrintUlangModal -->
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

    <!-- Tata letak utama -->
    <AuthenticatedLayout>
        <BaseCard :title="form.obc + ' - ' + form.noPlat">
            <template #title>
                <span :class="form.seri == 3 ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400'">
                    {{ form.obc }}
                </span>
                -
                <span class="text-blue-600 dark:text-blue-400">
                    {{ form.noPlat }}
                </span>
            </template>

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

                <!-- Detail pesanan grid -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- Nomor PO -->
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

                    <!-- Nomor OBC -->
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

                    <!-- Seri -->
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

                <!-- Detail tambahan grid -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- Nomor Rim -->
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

                    <!-- Cut Sheet -->
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

                    <!-- Kode Plat -->
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

                <!-- Operator ID -->
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

                <!-- Tombol aksi -->
                <div class="flex justify-center gap-4">
                    <!-- hapus -->
                    <button
                        type="button"
                        @click="form.periksa1 = null"
                        class="px-6 py-3 text-white bg-violet-600 dark:bg-violet-700 hover:bg-violet-700 dark:hover:bg-violet-800 rounded-lg transition-colors"
                    >
                        Clear
                    </button>

                    <!-- generate label -->
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

                    <!-- Tambahkan tombol Print Ulang di sini -->
                    <button
                        type="button"
                        @click="printUlangModal = true"
                        class="px-6 py-3 text-blue-600 bg-inherit dark:bg-inherit border border-blue-600 dark:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-800 rounded-lg transition-colors"
                    >
                        Print Ulang
                    </button>

                    <!-- print label manual -->
                    <button
                        type="button"
                        @click="printManualModal = true"
                        class="px-6 py-3 text-green-600 bg-inherit dark:bg-inherit border border-green-600 dark:border-green-700 hover:bg-green-50 dark:hover:bg-green-800 rounded-lg transition-colors"
                    >
                        Print Manual
                    </button>
                </div>

                <button
                    type="button"
                    @click="confirmFinishOrder"
                    class="w-full px-6 py-3 text-white bg-cyan-600 dark:bg-cyan-700 hover:bg-cyan-700 dark:hover:bg-cyan-800 rounded-lg transition-colors"
                >
                    Selesaikan Order
                </button>
            </form>
        </BaseCard>
        <TableVerifikasiPegawai :team="form.team" :date="form.date"/>
    </AuthenticatedLayout>
    <iframe ref="printFrame" style="display: none"></iframe>
</template>
<script setup>
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

// Definisikan props
const props = defineProps({
    product: Object,        // Detail produk
    listTeam: Object,      // Daftar tim yang tersedia
    crntTeam: Number,      // ID tim saat ini
    noRim: Number,         // Nomor rim
    potongan: String,      // Informasi lembar potong
    date: String,          // Tanggal saat ini
});

// Inisialisasi variabel yang tidak digunakan
const printUlangModal = ref(false);
const loading = ref(false);
const printManualModal = ref(false);
const printFrame = ref(null);
const periksa1Input = ref(null);

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

// Tentukan warna berdasarkan seri produk
const colorObc = form.seri == 3 ? "#b91c1c" : "#1d4ed8";

// Fungsi untuk mencetak tanpa dialog
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

// Fungsi untuk mengambil data terbaru
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

// Fungsi untuk mengirimkan form
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

// Fungsi untuk menampilkan notifikasi
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

// Fungsi untuk menyelesaikan order
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

// Fungsi untuk mengambil nomor plat
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

// Inisialisasi awal
onMounted(() => {
    fetchNoPlat();
});

// Handler untuk keberhasilan mencetak
const handlePrintSuccess = async () => {
    await fetchUpdatedData();
    showNotification('Label berhasil dicetak', 'success');
};
</script>
