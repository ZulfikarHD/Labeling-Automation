<script setup>
/**
 * This script handles the registration of Nomor PO (Production Order Number).
 * It utilizes Vue's Composition API and Inertia.js for form handling and API requests.
 */

import { inject, ref } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import LoadingOverlay from "@/Components/LoadingOverlay.vue";
import BaseCard from "@/Components/BaseCard.vue";
import Button from "@/Components/Button.vue";
import Select from "@/Components/Select.vue";

const swal = inject('$swal'); // Injecting SweetAlert for notifications
const isLoading = ref(false); // Loading state for API calls
const errorPo = ref(""); // Error message for PO input
const errorObc = ref(""); // Error message for OBC input
const confirmationMessage = ref("tests"); // Confirmation message for submission

const props = defineProps({
    workstation: Object, // Workstation data passed as props
    currentTeam: Number, // Current team ID passed as props
});

const seri = ref(""); // Serial number state
const seriColor = ref(""); // Serial color state

// Form state using Inertia's useForm
const form = useForm({
    po: null,
    obc: "",
    jml_rim: 0,
    jml_lembar: 0,
    seri: 1,
    start_rim: 1,
    produk: "PCHT",
    end_rim: 40,
    inschiet: 0,
    team: props.currentTeam,
});

/**
 * Fetch data based on the PO number.
 * Updates form fields and handles errors.
 */
const fetchData = () => {
    isLoading.value = true;
    errorPo.value = "";

    axios.get(`/api/order-besar/register-no-po/${form.po}`).then((response) => {
        const data = response.data;
        form.obc = data.no_obc;
        form.jml_lembar = data.rencet;
        form.jml_rim = Math.ceil(data.rencet / 500);
        form.end_rim = Math.max(1, Math.floor(data.rencet / 500 / 2));
        form.inschiet = (data.rencet % 1000 === 0) ? data.rencet % 500 : data.rencet % 1000;

        updateConfirmationMessage();
    }).catch(() => {
        errorPo.value = "Nomor PO Tidak Ditemukan";
        resetForm();
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

/**
 * Calculate the end rim based on the number of sheets.
 */
const calcEndRim = () => {
    form.end_rim = form.jml_lembar > 500
        ? Math.max(1, parseInt(form.start_rim) + Math.floor(form.jml_lembar / 500 / 2 - 1))
        : Math.max(1, parseInt(form.start_rim));
    form.jml_rim = Math.ceil(form.jml_lembar / 500);
    form.inschiet = form.jml_lembar % 1000 === 0
        ? form.jml_lembar - (Math.floor(form.jml_lembar / 500) * 500)
        : form.jml_lembar % 1000;
};

/**
 * Validate the OBC input and update the confirmation message.
 */
const cekSpec = () => {
    if (typeof form.obc === 'string' && form.obc.length >= 3) {
        const firstThreeLetters = form.obc.substring(0, 3);
        if (!/^[a-zA-Z]+$/.test(firstThreeLetters)) {
            errorObc.value = "Mohon masukan kode Daerah ex.'PST'";
        } else {
            updateConfirmationMessage();
        }
    }
};

/**
 * Update the confirmation message based on the OBC input.
 */
const updateConfirmationMessage = () => {
    const firstThreeLetters = form.obc.substring(0, 3);
    let daerahOrder;
    if (firstThreeLetters === "PST") {
        daerahOrder = "<span class='text-blue-500 dark:text-blue-400 font-semibold'>Pusat</span>";
    } else {
        daerahOrder = "<span class='text-red-500 dark:text-red-400 font-semibold'>Daerah</span>";
    }

    const seriValue = form.obc.substring(5, 4);
    if (seriValue == 3) {
        seri.value = `<span class='text-blue-500 dark:text-blue-400 font-semibold'>${seriValue}</span>`;
    } else if (seriValue == 2) {
        seri.value = `<span class='text-green-500 dark:text-green-400 font-semibold'>${seriValue}</span>`;
    } else {
        seri.value = `<span class='text-red-500 dark:text-red-400 font-semibold'>1</span>`;
    }

    confirmationMessage.value = `Order ${daerahOrder} Seri ${seri.value} ?`;
};

/**
 * Reset the form fields to their initial state.
 */
const resetForm = () => {
    form.obc = null;
    form.jml_lembar = 0;
    form.jml_rim = 0;
    form.end_rim = 1;
    form.inschiet = 0;
    form.team = props.currentTeam;
};

/**
 * Submit the form after confirmation.
 */
function submit() {
    swal.fire({
        html: confirmationMessage.value,
        title: 'Periksa Kembali Spesifikasi',
        icon: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Buat Label',
    }).then((result) => {
        if (result.isConfirmed) {
            isLoading.value = true;

            // Use axios instead of router for better error handling
            axios.post("/api/order-besar/register-no-po", form) // change to router from axios when debug error
                .then(response => {
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
}
</script>

<template>
    <Head title="Register No Po" />
    <LoadingOverlay :is-loading="isLoading" />

    <AuthenticatedLayout>
        <BaseCard title="Register Nomor Production Order">
            <form @submit.prevent="submit" class="flex flex-col text-lg">
                <div class="flex flex-col">
                    <InputLabel for="teamVerif" value="Team Periksa" class="dark:text-gray-300" />
                    <Select
                        id="teamVerif"
                        v-model="form.team"
                        class="text-center text-fuchsia-600 dark:text-fuchsia-400 font-semibold"
                    >
                        <option
                            v-for="workstation in props.workstation"
                            :value="workstation.id"
                            :key="workstation.id"
                        >
                            {{ workstation.workstation }}
                        </option>
                    </Select>
                </div>

                <!-- Input Nomor Po -->
                <div class="flex flex-col">
                    <InputLabel for="nomorPo" value="Nomor Po" class="dark:text-gray-300" />
                    <TextInput
                        @keyup="debouncedFetchData"
                        autofocus
                        id="nomorPo"
                        v-model="form.po"
                        type="number"
                        placeholder="Masukan Nomor PO"
                        class="placeholder:text-center text-center text-xl font-bold dark:bg-gray-700 dark:text-gray-300"
                    />

                    <InputError :message="errorPo" />
                </div>

                <!-- Keterangan Barang -->
                <div class="flex gap-3 mt-4">
                    <!-- Nomor Obc -->
                    <div class="flex flex-col flex-grow">
                        <InputLabel for="nomorObc" value="Nomor OBC" class="dark:text-gray-300" />
                        <TextInput
                            id="nomorObc"
                            @input="cekSpec()"
                            v-model="form.obc"
                            type="text"
                            placeholder="Masukan Nomor OBC"
                            required
                            class="dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>

                    <!-- Jml Cetak -->
                    <div class="flex flex-col flex-grow">
                        <InputLabel for="jmlLembar" value="Jumlah Cetak" class="dark:text-gray-300" />
                        <TextInput
                            @input="calcEndRim()"
                            id="jmlLembar"
                            v-model="form.jml_lembar"
                            type="text"
                            placeholder="Masukan Jumlah Lembar"
                            class="placeholder:text-center text-center text-base font-medium dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>

                    <!-- Inschiet -->
                    <div class="flex flex-col flex-grow">
                        <InputLabel for="inschiet" value="Inschiet" class="dark:text-gray-300" />
                        <TextInput
                            @input="calcEndRim()"
                            id="inschiet"
                            v-model="form.inschiet"
                            type="number"
                            min="0"
                            placeholder="Masukan Inschiet"
                            class="placeholder:text-center text-center text-base font-medium dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>

                    <!-- Jml Rim -->
                    <div class="flex flex-col flex-grow">
                        <InputLabel for="jmlRim" value="Jumlah RIM" class="dark:text-gray-300" />
                        <TextInput
                            id="jmlRim"
                            v-model="form.jml_rim"
                            type="number"
                            min="0"
                            placeholder="Jumlah RIM"
                            class="bg-gray-200 dark:bg-gray-600 text-center dark:text-gray-300"
                            disabled
                        />
                    </div>
                </div>

                <!-- Nomor RIm -->
                <div class="flex gap-3 mt-4">
                    <!-- Start Rim -->
                    <div class="flex flex-col flex-grow">
                        <InputLabel for="rimStart" value="Nomor Rim Awal" class="dark:text-gray-300" />
                        <TextInput
                            @input="calcEndRim()"
                            id="rimStart"
                            v-model="form.start_rim"
                            type="number"
                            min="0"
                            placeholder="Masukan Nomor RIM Pertama"
                            class="placeholder:text-center text-center text-base dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>

                    <!-- End Rim -->
                    <div class="flex flex-col flex-grow">
                        <InputLabel for="rimEnd" value="Nomor Rim Terakhir" class="dark:text-gray-300" />
                        <TextInput
                            id="rimEnd"
                            v-model="form.end_rim"
                            type="number"
                            min="0"
                            placeholder="Masukan Nomor Rim Terakhir"
                            class="placeholder:text-center text-center text-base dark:bg-gray-700 dark:text-gray-300"
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4 mt-8">
                    <Button
                        type="submit"
                        variant="success"
                        :loading="isLoading"
                        :full-width="true"
                    >
                        Buat Label
                    </Button>
                    <Button
                        type="button"
                        variant="secondary"
                        :full-width="true"
                        @click="resetForm"
                    >
                        Clear
                    </Button>
                </div>
            </form>
        </BaseCard>
    </AuthenticatedLayout>
</template>
