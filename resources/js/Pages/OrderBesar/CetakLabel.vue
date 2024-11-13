<template>
    <!-- Page title -->
    <Head title="Cetak Label" />

    <!-- Modal for reprinting labels -->
    <Modal :show="printUlangModal" @close="() => (printUlangModal = !printUlangModal)">
        <form @submit.prevent="printUlangLabel" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex flex-col gap-4">
                <!-- Modal header -->
                <h1 class="text-xl font-bold text-center text-gray-800 dark:text-gray-200">
                    Print Ulang / Ganti Data Rim
                </h1>

                <!-- Input for rim data -->
                <TextInput
                    id="dataRim"
                    name="dataRim"
                    type="text"
                    class="text-base font-semibold text-center uppercase bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 dark:text-gray-200"
                    v-model="formPrintUlang.dataRim"
                    required
                    disabled
                />

                <!-- Left/Right rim selection buttons -->
                <div class="flex justify-center gap-3">
                    <button
                        type="button"
                        @click="dataRimKiri()"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 dark:bg-blue-700 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-600"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        KIRI
                    </button>
                    <button
                        type="button"
                        @click="dataRimKanan()"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 dark:bg-blue-700 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-600"
                    >
                        KANAN
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Grid of rim selection buttons -->
                <div class="grid grid-cols-5 gap-2">
                    <template v-for="n in dataPrintUlang" :key="n.no_rim">
                        <!-- In Progress -->
                        <button
                            v-if="n.np_users && n.start && !n.finish"
                            type="button"
                            @click="pilihRim(n.no_rim, n.np_users)"
                            class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900 hover:bg-amber-200 dark:hover:bg-amber-800 transition-colors"
                        >
                            <div class="flex flex-col items-center">
                                <span class="text-sm font-medium text-amber-800 dark:text-amber-200">{{ n.np_users }}</span>
                                <span class="text-sm font-bold text-green-800 dark:text-green-200">{{ n.no_rim }}</span>
                            </div>
                        </button>

                        <!-- Completed -->
                        <button
                            v-else-if="n.np_users && n.start && n.finish"
                            type="button"
                            @click="pilihRim(n.no_rim, n.np_users)"
                            class="p-2 rounded-lg bg-emerald-100 dark:bg-emerald-900 hover:bg-emerald-200 dark:hover:bg-emerald-800 transition-colors"
                        >
                            <div class="flex flex-col items-center">
                                <span class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ n.np_users }}</span>
                                <span class="text-sm font-bold text-indigo-800 dark:text-indigo-200">{{ n.no_rim }}</span>
                            </div>
                        </button>

                        <!-- Inschiet -->
                        <button
                            v-else-if="n.no_rim === 999"
                            type="button"
                            @click="pilihRim(n.no_rim, n.np_users)"
                            class="p-2 rounded-lg bg-violet-100 dark:bg-violet-900 hover:bg-violet-200 dark:hover:bg-violet-800 transition-colors"
                        >
                            <div class="flex flex-col items-center">
                                <span class="text-sm font-medium text-violet-800 dark:text-violet-200">{{ n.np_users }}</span>
                                <span class="text-sm font-bold text-violet-900 dark:text-violet-100">Inschiet</span>
                            </div>
                        </button>

                        <!-- Unavailable -->
                        <button
                            v-else
                            type="button"
                            disabled
                            class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 cursor-not-allowed"
                        >
                            <div class="flex flex-col items-center">
                                <span class="text-sm font-medium text-gray-400 dark:text-gray-500">-</span>
                                <span class="text-sm font-bold text-gray-500 dark:text-gray-400">{{ n.no_rim }}</span>
                            </div>
                        </button>
                    </template>
                </div>

                <!-- Rim number and operator inputs -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="noRimPU" value="Nomor Rim" class="mb-1 text-sm dark:text-gray-300" />
                        <template v-if="formPrintUlang.noRim === 999">
                            <TextInput
                                id="noRimPU"
                                type="hidden"
                                v-model="formPrintUlang.noRim"
                                required
                            />
                            <TextInput
                                type="text"
                                value="Inschiet"
                                class="w-full bg-gray-50 dark:bg-gray-700 text-center text-sm dark:text-gray-300"
                                disabled
                            />
                        </template>
                        <template v-else>
                            <TextInput
                                id="noRimPU"
                                type="number"
                                v-model="formPrintUlang.noRim"
                                class="w-full bg-gray-50 dark:bg-gray-700 text-center text-sm dark:text-gray-300"
                                required
                                disabled
                            />
                        </template>
                    </div>

                    <div>
                        <InputLabel for="npPetugasPU" value="NP Petugas" class="mb-1 text-sm dark:text-gray-300" />
                        <TextInput
                            id="npPetugasPU"
                            type="text"
                            v-model="formPrintUlang.npPetugas"
                            class="w-full uppercase text-sm dark:bg-gray-700 dark:text-gray-300"
                            required
                        />
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex justify-between mt-4">
                    <button
                        type="button"
                        class="px-6 py-2.5 text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900 hover:bg-red-100 dark:hover:bg-red-800 rounded-lg transition-colors"
                    >
                        Hapus
                    </button>
                    <button
                        type="submit"
                        class="px-6 py-2.5 text-white bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 rounded-lg transition-colors"
                    >
                        Print
                    </button>
                </div>
            </div>
        </form>
    </Modal>

    <!-- Main layout -->
    <AuthenticatedLayout>
        <div class="w-full max-w-5xl bg-white dark:bg-gray-800 rounded-lg py-4 shadow-md px-6 mx-auto mt-8 flex flex-col gap-3 mb-8">
        <!-- Title -->
            <h1 class="text-3xl font-bold text-[#4B5563] dark:text-gray-200 my-auto text-center mb-4 pb-4 border-b border-sky-600">
                <span class="text-red-600 dark:text-red-400" v-if="form.seri == 3">
                    {{ form.obc }}
                </span>
                <span class="text-blue-600 dark:text-blue-400" v-else>
                    {{ form.obc }}
                </span>
                -
                <span class="text-blue-600 dark:text-blue-400">
                    {{ form.noPlat }}
                </span>
            </h1>
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Team selection -->
                <div>
                    <InputLabel for="team" value="Team" class="text-xl font-bold mb-3 dark:text-gray-300" />
                    <select
                        id="team"
                        ref="team"
                        v-model="form.team"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option v-for="team in props.listTeam" :key="team.id" :value="team.id">
                            {{ team.workstation }}
                        </option>
                    </select>
                </div>

                <!-- Order details grid -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- PO Number -->
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

                    <!-- OBC Number -->
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

                    <!-- Series -->
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

                <!-- Additional details grid -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- Rim Number -->
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

                    <!-- Plate Code -->
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
                        autofocus
                    />
                    <InputError class="mt-2" />
                </div>

                <!-- Action buttons -->
                <div class="flex justify-center gap-4">
                    <button
                        type="button"
                        @click="form.periksa1 = null"
                        class="px-6 py-3 text-white bg-violet-600 dark:bg-violet-700 hover:bg-violet-700 dark:hover:bg-violet-800 rounded-lg transition-colors"
                    >
                        Clear
                    </button>
                    <button
                        type="submit"
                        class="px-6 py-3 text-white bg-green-600 dark:bg-green-700 hover:bg-green-700 dark:hover:bg-green-800 rounded-lg transition-colors"
                    >
                        Generate
                    </button>
                    <button
                        type="button"
                        @click="[getDataRim(), (printUlangModal = !printUlangModal)]"
                        class="px-6 py-3 text-white bg-green-600 dark:bg-green-700 hover:bg-green-700 dark:hover:bg-green-800 rounded-lg transition-colors"
                    >
                        Print Ulang
                    </button>
                </div>

                <button
                    type="button"
                    @click="finish_order"
                    class="w-full px-6 py-3 text-white bg-cyan-600 dark:bg-cyan-700 hover:bg-cyan-700 dark:hover:bg-cyan-800 rounded-lg transition-colors"
                >
                    Selesaikan Order
                </button>
            </form>

            <!-- Navigation -->
            <div class="flex justify-center gap-4 mt-4">
                <a
                    href="#"
                    onclick="history.back()"
                    class="inline-flex items-center px-6 py-3 text-white bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 rounded-lg transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </a>
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center px-6 py-3 text-white bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 rounded-lg transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Home
                </Link>
            </div>
        </div>
        <TableVerifikasiPegawai :team="form.team" :date="form.date"/>
    </AuthenticatedLayout>
    <iframe ref="printFrame" style="display: none"></iframe>
</template>

<script setup>
import { reactive, ref } from "vue";
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import TableVerifikasiPegawai from "@/Components/TableVerifikasiPegawai.vue";
import { fullPageLabel } from "@/Components/PrintPages/PrintLabel";
import { Link, useForm, router, Head } from "@inertiajs/vue3";
import axios from "axios";

// Props definition
const props = defineProps({
    product: Object,        // Product details
    listTeam: Object,      // List of available teams
    crntTeam: Number,      // Current team ID
    noRim: Number,         // Rim number
    potongan: String,      // Cut sheet info
    date: String,          // Current date
});

// Reactive refs for modal states
const showModal = ref(false);
const printUlangModal = ref(false);
const dataPrintUlang = ref();

// Form for main data entry
const form = useForm({
    id: props.product.id,
    po: props.product.no_po,
    obc: props.product.no_obc,
    team: props.crntTeam,
    seri: props.product.no_obc.substr(4, 1) > 3 ? 1 : props.product.no_obc.substr(4, 1),
    jml_rim: 1,
    lbr_ptg: props.potongan,
    no_rim: props.noRim,
    periksa1: "",
    date: props.date,
    noPlat: "",
});

// Form for reprint functionality
const formPrintUlang = reactive({
    dataRim: "Kiri",
    noRim: "",
    npPetugas: "",
    po: props.product.no_po,
    obc: props.product.no_obc,
    team: props.crntTeam,
});

// Color coding for OBC series
const colorObc = form.seri == 3 ? "#b91c1c" : "#1d4ed8";

// Functions for handling rim data
const dataRimKanan = async () => {
    formPrintUlang.dataRim = "Kanan";
    getDataRim();
};

const dataRimKiri = async () => {
    formPrintUlang.dataRim = "Kiri";
    getDataRim();
};

// Fetch rim data from API
const getDataRim = () => {
    axios
        .post("/api/order-besar/cetak-label/edit", formPrintUlang)
        .then((res) => {
            dataPrintUlang.value = res.data;
        });
};

// Handle rim selection
const pilihRim = (noRim, np) => {
    formPrintUlang.noRim = noRim;
    formPrintUlang.npPetugas = np;
};

// Print frame reference
const printFrame = ref(null);

// Print functionality without dialog
const printWithoutDialog = (content) => {
    const iframe = printFrame.value;
    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <style>
            @media print {
                @page { margin-left: 3rem; margin-right:3rem; margin-top:1rem; }
                header, footer { display: none !important; }
                * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
        </style>
        ${content}
    `);

    iframe.contentWindow.focus();
    setTimeout(() => {
        iframe.contentWindow.print();
    }, 200);
};

// Handle reprint label submission
const printUlangLabel = () => {
    const printLabel = fullPageLabel(
        formPrintUlang.obc,
        formPrintUlang.noRim !== 999 ? formPrintUlang.noRim : "INS",
        colorObc,
        formPrintUlang.dataRim == "Kiri" ? "(*)" : "(**)",
        formPrintUlang.npPetugas,
        undefined
    );
    printWithoutDialog(printLabel);

    setTimeout(() => {
        router.post("/api/order-besar/cetak-label/update", formPrintUlang, {
            onFinish: () => {
                router.get(`/order-besar/cetak-label/${form.team}/${form.id}`);
            },
        });
    }, 500);
};

// Handle main form submission
const submit = () => {
    const printLabel = fullPageLabel(
        form.obc,
        form.no_rim !== 999 ? form.no_rim : "INS",
        colorObc,
        form.lbr_ptg == "Kiri" ? "(*)" : "(**)",
        form.periksa1,
        undefined
    );

    printWithoutDialog(printLabel);

    setTimeout(() => {
        router.post("/api/order-besar/cetak-label", form, {
            onFinish: () => {
                router.get(
                    props.noRim !== 0
                        ? `/order-besar/cetak-label/${form.team}/${form.id}`
                        : "/order-besar/po-siap-verif"
                );
                form.periksa1 = null;
            },
        });
    }, 500);
};

// Handle order completion
const finish_order = () => {
    axios.put(`/api/production-order-finish/${form.po}`);
    router.get("/order-besar/po-siap-verif");
};

// Fetch plate number from external API
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

// Initial plate number fetch
fetchNoPlat();
</script>
