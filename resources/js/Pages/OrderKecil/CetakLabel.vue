<script setup>
import { inject, ref } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from "@/Components/InputLabel.vue";
import TableVerifikasiPegawai from "@/Components/TableVerifikasiPegawai.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, router, Head } from "@inertiajs/vue3";
import axios from "axios";
import { batchFullPageLabel } from "@/Components/PrintPages/PrintLabel";
import NavigateBackButton from "@/Components/NavigateBackButton.vue";

const swal = inject('$swal');
const isLoading = ref(false);

const props = defineProps({
    listTeam: Object,
    currentTeam: Number,
});

const obc_color = ref();

const form = useForm({
    team: props.currentTeam,
    no_po: "",
    obc: "",
    jml_lembar: "",
    jml_label: "",
    seri: "",
    periksa1: "",
    periksa2: "",
    jml_rim: "",
});

const fetchData = () => {
    isLoading.value = true;

    axios.get(`/api/order-kecil/fetch-spec/${form.no_po}`).then((res) => {
        let total_label = Math.ceil(res.data.rencet / 500);

        form.obc = res.data.no_obc;
        form.jml_rim = `${res.data.rencet} / ${total_label} Rim`;
        form.jml_lembar = res.data.rencet;
        form.jml_label = total_label;
        form.seri = res.data.no_obc.substr(4, 1) > 3 ? 1 : res.data.no_obc.substr(4, 1);
        obc_color.value = form.seri == 3 ? "#b91c1c" : "#1d4ed8";
    }).finally(() => {
        isLoading.value = false;
    });
};

const printFrame = ref(null);

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
            router.post("/api/order-kecil/cetak-label", form, {
                onSuccess: () => {
                    let printLabel = batchFullPageLabel(
                        form.obc,
                        undefined,
                        obc_color.value,
                        undefined,
                        form.periksa1,
                        form.periksa2,
                        form.jml_label
                    );
                    printWithoutDialog(printLabel);

                    swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Label Berhasil Dibuat',
                    });
                    form.reset();
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Cetak Label" />

    <!-- Loading Indicator -->
    <div class="bg-black bg-opacity-40 w-screen h-screen absolute z-50 flex justify-center items-center" v-if="isLoading">
        <div class="rounded-lg p-4 flex flex-col gap-2 justify-center items-center">
            <svg class="animate-spin h-10 w-10 brightness-110" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25 drop-shadow-md shadow-md text-blue-50" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75 shadow-md text-blue-500" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-white font-semibold animate-pulse">Sedang Memproses...</span>
        </div>
    </div>

    <AuthenticatedLayout>
        <div class="w-full max-w-5xl bg-white rounded-lg shadow-md py-12 px-6 mx-auto mt-10 flex flex-col gap-3 mb-10">
            <h1 class="text-3xl font-bold text-[#4B5563] my-auto text-center mb-4 pb-4 border-b border-sky-600">Cetak Label Order Kecil</h1>

            <form @submit.prevent="submit" class="flex flex-col text-lg">
                <!-- Team Selection -->
                <div class="flex flex-col">
                    <InputLabel for="team" value="Team Periksa" />
                    <select
                        id="team"
                        v-model="form.team"
                        class="mb-2 text-center bg-gray-50 text-indigo-600 border focus:border-transparent border-gray-300 sm:text-sm rounded-lg ring ring-transparent focus:ring-1 focus:outline-none focus:ring-sky-400 block w-full p-2.5 rounded-l-lg py-3 px-4 font-semibold"
                    >
                        <option v-for="team in props.listTeam" :key="team.id" :value="team.id">
                            {{ team.workstation }}
                        </option>
                    </select>
                </div>

                <!-- Production Order -->
                <div class="flex flex-col mt-4">
                    <InputLabel for="no_po" value="Nomor Production Order" />
                    <TextInput
                        id="no_po"
                        v-model="form.no_po"
                        @input="fetchData"
                        type="number"
                        placeholder="Masukkan Nomor PO"
                        class="placeholder:text-center text-center text-xl font-bold"
                        required
                        autofocus
                    />
                </div>

                <!-- Order Details -->
                <div class="flex gap-3 mt-4">
                    <div class="flex flex-col flex-grow">
                        <InputLabel for="obc" value="Nomor OBC" />
                        <TextInput
                            id="obc"
                            v-model="form.obc"
                            type="text"
                            placeholder="Order Bea Cukai"
                            required
                        />
                    </div>

                    <div class="flex flex-col flex-grow">
                        <InputLabel for="jml_rim" value="Lembar / Rim" />
                        <TextInput
                            id="jml_rim"
                            v-model="form.jml_rim"
                            type="text"
                            class="bg-gray-200"
                            disabled
                        />
                    </div>

                    <div class="flex flex-col flex-grow">
                        <InputLabel for="jml_label" value="Jumlah Label" />
                        <TextInput
                            id="jml_label"
                            v-model="form.jml_label"
                            type="number"
                            placeholder="Jumlah Label"
                            required
                        />
                    </div>
                </div>

                <!-- Inspector Details -->
                <div class="flex gap-3 mt-4">
                    <div class="flex flex-col flex-grow">
                        <InputLabel for="periksa1" value="NP Periksa 1" />
                        <TextInput
                            id="periksa1"
                            v-model="form.periksa1"
                            type="text"
                            placeholder="Nomor Pegawai"
                            required
                        />
                    </div>

                    <div class="flex flex-col flex-grow">
                        <InputLabel for="periksa2" value="NP Periksa 2" />
                        <TextInput
                            id="periksa2"
                            v-model="form.periksa2"
                            type="text"
                            placeholder="Nomor Pegawai"
                        />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 mt-8">
                    <button
                        type="submit"
                        class="bg-green-500 text-white font-bold py-2 px-4 rounded-md mt-4 hover:bg-green-600 transition ease-in-out duration-150 flex-auto"
                    >
                        Buat Label
                    </button>
                    <button
                        @click="form.reset()"
                        type="button"
                        class="bg-violet-500 text-white font-bold py-2 px-4 rounded-md mt-4 hover:bg-violet-600 transition ease-in-out duration-150 flex-auto"
                    >
                        Clear
                    </button>
                </div>
            </form>
        </div>

        <TableVerifikasiPegawai :team="form.team" />
    </AuthenticatedLayout>
    <iframe ref="printFrame" style="display: none"></iframe>
</template>
