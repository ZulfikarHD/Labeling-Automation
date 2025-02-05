/**
 * Komponen untuk mencetak label Order Kecil
 *
 * Fitur utama:
 * - Input dan validasi nomor PO
 * - Kalkulasi otomatis jumlah rim
 * - Validasi format OBC
 * - Konfirmasi submit dengan SweetAlert
 * - Handling error dengan pesan yang informatif
 * - Print label functionality
 *
 * @requires vue
 * @requires @inertiajs/vue3
 * @requires axios
 */

<script setup>
import { inject, ref } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from "@/Components/InputLabel.vue";
import TableVerifikasiPegawai from "@/Components/TableVerifikasiPegawai.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, router, Head } from "@inertiajs/vue3";
import axios from "axios";
import { batchSingleLabel } from "@/Components/PrintPages/index";
import BaseCard from "@/Components/BaseCard.vue";
import Select from "@/Components/Select.vue";
import LoadingOverlay from "@/Components/LoadingOverlay.vue";
import Button from "@/Components/Button.vue";

// Injeksi dependency
const swal = inject('$swal');

// State management
const isLoading = ref(false);
const errorPo = ref("");
const timeoutDuration = ref(1000);
const printFrame = ref(null);
const isDataFetched = ref(false);

// Props definition dengan validasi
const props = defineProps({
    listTeam: {
        type: Object,
        required: true
    },
    currentTeam: {
        type: Number,
        required: true
    }
});

const obc_color = ref();

/**
 * Form state menggunakan Inertia useForm
 * Menyimpan semua input yang diperlukan untuk cetak label
 */
const form = useForm({
    team: props.currentTeam,
    po: "",
    obc: "",
    start_rim: 1,
    end_rim: 1,
    produk: "PCHT",
    jml_lembar: "",
    jml_label: "",
    seri: "",
    periksa1: "",
    periksa2: "",
    jml_rim: "",
});

const fetchData = () => {
    errorPo.value = "";
    isLoading.value = true;

    axios.get(`/api/order-kecil/fetch-spec/${form.po}`).then((res) => {
        let total_label = Math.ceil(res.data.rencet / 500);

        form.obc = res.data.no_obc;
        form.jml_rim = `${res.data.rencet} / ${total_label} Rim`;
        form.jml_lembar = res.data.rencet;
        form.jml_label = total_label;
        form.end_rim = Math.max(1, Math.floor(res.data.rencet / 500 / 2));
        form.seri = res.data.no_obc.substr(4, 1) > 3 ? 1 : res.data.no_obc.substr(4, 1);
        obc_color.value = form.seri == 3 ? "#b91c1c" : "#1d4ed8";
        isDataFetched.value = true;
    }).catch(() => {
        errorPo.value = "Nomor PO Tidak Ditemukan";
        isDataFetched.value = false;
    }).finally(() => {
        isLoading.value = false;
    });
};

/**
 * Debounce function to limit the rate of API calls.
 * @param {Function} func - The function to debounce.
 * @param {number} delay - The delay in milliseconds.
 * @returns {Function} - The debounced function.
 */
 const debounce = (func, delay) => {
    let timeout;
    return () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this), delay);
    };
};

const debouncedFetchData = debounce(fetchData, 500); // Debounced fetch function

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

const submit = () => {
    swal.fire({
        title: 'Periksa Kembali Spesifikasi',
        text: "Pastikan data yang dimasukkan sudah benar",
        icon: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Buat Label',
    }).then((result) => {
        if (result.isConfirmed) {
            isLoading.value = true;

            // Use axios instead of router for better error handling
            axios.post("/api/order-kecil/cetak-label", form) // untuk debug error ganti axioss ke router
                .then(response => {
                    let printLabel = batchSingleLabel(
                        form.obc,
                        undefined,
                        obc_color.value,
                        undefined,
                        form.periksa1,
                        form.periksa2,
                        form.jml_label,
                        500
                    );
                    printWithoutDialog(printLabel);

                    isLoading.value = true;

                    if(form.jml_label > 10 ) {
                        timeoutDuration.value = Math.round(1000 * (form.jml_label / 5));
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
                        });
                        isLoading.value = false;
                    }, timeoutDuration.value);

                    form.reset();
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
                })
                .finally(() => {
                    isLoading.value = false;
                });
        }
    });
};

const resetForm = () => {
    form.reset();
    isDataFetched.value = false;
};
</script>

<template>
    <Head title="Cetak Label" />
    <LoadingOverlay :is-loading="isLoading" />

    <AuthenticatedLayout>
        <BaseCard title="Cetak Label Order Kecil">
            <form @submit.prevent="submit" class="flex flex-col space-y-6">
                <!-- Team Selection -->
                <div class="flex flex-col">
                    <InputLabel
                        for="team"
                        value="Team Periksa"
                        required
                        class="dark:text-gray-200"
                    />
                    <Select
                        id="team"
                        v-model="form.team"
                        class="text-center text-fuchsia-600 dark:text-fuchsia-400 font-semibold"
                    >
                        <option v-for="team in props.listTeam" :key="team.id" :value="team.id">
                            {{ team.workstation }}
                        </option>
                    </Select>
                </div>

                <!-- Production Order -->
                <div class="flex flex-col">
                    <InputLabel
                        for="po"
                        value="Nomor Production Order"
                        required
                        class="dark:text-gray-200"
                    />
                    <TextInput
                        id="po"
                        v-model="form.po"
                        @input="debouncedFetchData"
                        type="number"
                        placeholder="Masukkan Nomor PO"
                        class="text-center text-xl font-bold"
                        required
                        autofocus
                    />
                    <InputError :message="errorPo"/>
                </div>

                <!-- Order Details -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex flex-col">
                        <InputLabel
                            for="obc"
                            value="Nomor OBC"
                            required
                            class="dark:text-gray-200"
                        />
                        <TextInput
                            id="obc"
                            v-model="form.obc"
                            type="text"
                            placeholder="Order Bea Cukai"
                            required
                            :disabled="isDataFetched"
                        />
                    </div>

                    <div class="flex flex-col">
                        <InputLabel
                            for="jml_rim"
                            value="Lembar / Rim"
                            class="dark:text-gray-200"
                        />
                        <TextInput
                            id="jml_rim"
                            v-model="form.jml_rim"
                            type="text"
                            disabled
                        />
                    </div>

                    <div class="flex flex-col">
                        <InputLabel
                            for="jml_label"
                            value="Jumlah Label"
                            required
                            class="dark:text-gray-200"
                        />
                        <TextInput
                            id="jml_label"
                            v-model="form.jml_label"
                            type="number"
                            placeholder="Jumlah Label"
                            required
                            :disabled="isDataFetched"
                        />
                    </div>
                </div>

                <!-- Inspector Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col">
                        <InputLabel
                            for="periksa1"
                            value="NP Periksa 1"
                            required
                            class="dark:text-gray-200"
                        />
                        <TextInput
                            id="periksa1"
                            v-model="form.periksa1"
                            type="text"
                            placeholder="Nomor Pegawai"
                            maxlength="4"
                            required
                        />
                    </div>

                    <div class="flex flex-col">
                        <InputLabel
                            for="periksa2"
                            value="NP Periksa 2"
                            class="dark:text-gray-200"
                        />
                        <TextInput
                            id="periksa2"
                            v-model="form.periksa2"
                            type="text"
                            placeholder="Nomor Pegawai"
                            maxlength="4"
                        />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 mt-8">
                    <Button
                        type="submit"
                        variant="primary"
                        size="lg"
                        :full-width="true"
                        :loading="isLoading"
                        :disabled="isLoading"
                    >
                        Buat Label
                    </Button>

                    <Button
                        type="button"
                        variant="outline-primary"
                        size="lg"
                        :full-width="true"
                        @click="resetForm"
                    >
                        Clear
                    </Button>
                </div>
            </form>
        </BaseCard>

        <TableVerifikasiPegawai
            :team="form.team"
            :date="new Date().toISOString().split('T')[0]"
        />
    </AuthenticatedLayout>
    <iframe ref="printFrame" class="hidden"></iframe>
</template>
