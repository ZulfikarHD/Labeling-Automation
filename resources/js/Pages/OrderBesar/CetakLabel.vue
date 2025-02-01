<template>
    <!-- Page title -->
    <Head title="Cetak Label" />

    <!-- Use the LoadingOverlay component -->
    <LoadingOverlay :show="loading" />

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
                            maxlength="4"
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
                        :disabled="loading"
                        :class="[
                            'px-6 py-2.5 text-white bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 rounded-lg transition-colors',
                            loading ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                    >
                        {{ loading ? 'Memproses...' : 'Print' }}
                    </button>
                </div>
            </div>
        </form>
    </Modal>

    <!-- Add this modal component after the existing Modal component -->
    <Modal :show="printManualModal" @close="() => (printManualModal = false)">
        <form @submit.prevent="printLabelManual" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex flex-col gap-4">
                <!-- Modal header -->
                <h1 class="text-xl font-bold text-center text-gray-800 dark:text-gray-200">
                    Print Label Manual
                </h1>
                <p class="text-red-600 dark:text-red-400 text-center">
                    Label yang dibuat disini tidak tersimpan di database.
                </p>

                <!-- Input fields -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Left side inputs -->
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="npPetugas" value="NP Petugas 1" class="mb-1 text-sm dark:text-gray-300" />
                            <TextInput
                                id="npPetugas"
                                type="text"
                                v-model="formPrintManual.npPetugas"
                                class="w-full uppercase text-sm dark:bg-gray-700 dark:text-gray-300"
                                maxlength="4"
                                required
                            />
                        </div>
                        <div>
                            <InputLabel for="npPetugas2" value="NP Petugas 2" class="mb-1 text-sm dark:text-gray-300" />
                            <TextInput
                                id="npPetugas2"
                                type="text"
                                v-model="formPrintManual.npPetugas2"
                                class="w-full uppercase text-sm dark:bg-gray-700 dark:text-gray-300"
                                maxlength="4"
                            />
                        </div>
                    </div>

                    <!-- Right side inputs -->
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="jmlLabel" value="Jumlah Label" class="mb-1 text-sm dark:text-gray-300" />
                            <TextInput
                                id="jmlLabel"
                                type="number"
                                v-model="formPrintManual.jml_label"
                                class="w-full text-sm dark:bg-gray-700 dark:text-gray-300"
                                min="1"
                                max="100"
                                required
                            />
                        </div>
                        <div>
                            <InputLabel for="dataRimManual" value="Potongan" class="mb-1 text-sm dark:text-gray-300" />
                            <select
                                id="dataRimManual"
                                v-model="formPrintManual.dataRim"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300"
                            >
                                <option value="">-</option>
                                <option value="Kiri">Kiri (*)</option>
                                <option value="Kanan">Kanan (**)</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="lembarManual" value="Lembar" class="mb-1 text-sm dark:text-gray-300" />
                            <TextInput
                                id="lembarManual"
                                type="number"
                                v-model="formPrintManual.lembar"
                                class="w-full text-sm dark:bg-gray-700 dark:text-gray-300"
                                min="1"
                                max="500"
                                required
                            />
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex justify-end gap-3 mt-4">
                    <button
                        type="button"
                        @click="printManualModal = false"
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        :class="[
                            'px-4 py-2 text-white bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 rounded-lg transition-colors',
                            loading ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                    >
                        {{ loading ? 'Memproses...' : 'Print' }}
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
                        ref="periksa1Input"
                        autofocus
                    />
                    <InputError class="mt-2" />
                </div>

                <!-- Action buttons -->
                <div class="flex justify-center gap-4">
                    <!-- clear -->
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

                    <!-- print ulang -->
                    <button
                        type="button"
                        @click="[getDataRim(), (printUlangModal = !printUlangModal)]"
                        class="px-6 py-3 text-white bg-green-600 dark:bg-green-700 hover:bg-green-700 dark:hover:bg-green-800 rounded-lg transition-colors"
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
    <!-- Replace iframe with PrintFrame component -->
    <PrintFrame ref="printFrameRef" />
</template>

<script setup>
import { reactive, ref, onMounted, nextTick } from "vue";
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import TableVerifikasiPegawai from "@/Components/TableVerifikasiPegawai.vue";
import LoadingOverlay from "@/Components/LoadingOverlay.vue";
import PrintFrame from "@/Components/PrintPages/PrintFrame.vue";
import { singleLabel, batchSingleLabel } from "@/Components/PrintPages/index";
import { Link, useForm, router, Head } from "@inertiajs/vue3";
import axios from "axios";
import { useNotification } from '@/composables/useNotifications';
import { usePrinting } from '@/composables/printing';
import Swal from 'sweetalert2';

// Initialize notifications with all functions
const {
    showSuccessNotification,
    showErrorNotification,
    showConfirmation
} = useNotification();

// Initialize printing utilities
const {
    cleanup,
    setupPrintCleanup,
    debounce,
    calculateTimeoutDuration,
    memoryManager
} = usePrinting();

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

// Add loading state ref
const loading = ref(false);

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

const formPrintManual = reactive({
    dataRim: "",
    noRim: "",
    npPetugas: "",
    npPetugas2: "",
    lembar: 500,
    po: props.product.no_po,
    obc: props.product.no_obc,
    team: props.crntTeam,
    jml_label: 1,
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

// Replace printFrame ref with PrintFrame component ref
const printFrameRef = ref(null);

// First declare the function
function fetchData() {
    return async () => {
        try {
            if (dataPrintUlang.value) {
                dataPrintUlang.value = null;
            }

            const { data } = await axios.get(
                `/api/order-besar/cetak-label/data/${form.team}/${form.id}`,
                {
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                }
            );

            if (data) {
                form.no_rim = data.noRim;
                form.lbr_ptg = data.potongan;
                dataPrintUlang.value = data.printData;
            }
        } catch (error) {
            console.error('Error fetching updated data:', error);
            showErrorNotification('Gagal', 'Gagal memperbarui data');
        }
    };
}

// Then create the debounced version
const debouncedFetchData = debounce(fetchData(), 500);

// Use setupPrintCleanup in onMounted
onMounted(() => {
    setupPrintCleanup();
});

// Replace printWithoutDialog with new print method
const printWithoutDialog = (content) => {
    printFrameRef.value.print(content);
};

// Modify submit function to use new notifications
const submit = async (e) => {
    e.preventDefault();
    if (loading.value) return;

    loading.value = true;
    const controller = new AbortController();

    try {
        memoryManager.clearReactiveData();

        const { data } = await axios.post("/api/order-besar/cetak-label", form, {
            signal: controller.signal,
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        });

        if (data.status === 'error') {
            showErrorNotification('Error', data.message);
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

            await fetchData();
            await showSuccessNotification('Berhasil', 'Label berhasil dicetak');

            await nextTick(() => {
                periksa1Input.value?.focus();
            });
        } else {
            router.get("/order-besar/po-siap-verif", {}, { preserveState: true });
        }
    } catch (error) {
        if (!axios.isCancel(error)) {
            console.error('Error:', error);
            showErrorNotification('Gagal', 'Gagal mencetak label');
        }
    } finally {
        loading.value = false;
        controller.abort();
    }
};

// Modify confirmFinishOrder to use new notifications
const confirmFinishOrder = async () => {
    try {
        const result = await showConfirmation(
            'Selesaikan Order',
            'Apakah Anda yakin ingin menyelesaikan order ini?'
        );

        if (result.isConfirmed) {
            await axios.put(`/api/production-order-finish/${form.po}`);
            router.get("/order-besar/po-siap-verif", {}, { preserveState: true });
            await showSuccessNotification('Berhasil', 'Order berhasil diselesaikan');
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorNotification('Gagal', 'Gagal menyelesaikan order');
    }
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

// Add a new method to handle order completion status
const checkOrderCompletion = (message) => {
    if (message === 'Order sudah selesai') {
        Swal.fire({
            title: 'Order Selesai',
            text: 'Semua label telah selesai diproses',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#0891b2'
        }).then((result) => {
            if (result.isConfirmed) {
                router.get("/order-besar/po-siap-verif", {}, { preserveState: true });
            }
        });
        return true;
    }
    return false;
};

// Print label manual
const printLabelManual = async () => {
    try {
        loading.value = true;
        const printLabel = batchSingleLabel(
            formPrintManual.obc,
            "",
            colorObc,
            formPrintManual.dataRim,
            formPrintManual.npPetugas,
            formPrintManual.npPetugas2,
            formPrintManual.jml_label,
            formPrintManual.lembar,
        );
        printWithoutDialog(printLabel);

        // Reset form and close modal
        formPrintManual.npPetugas = '';
        formPrintManual.npPetugas2 = '';
        formPrintManual.jml_label = 1;
        formPrintManual.dataRim = 'Kiri';
        formPrintManual.lembar = 500;
        printManualModal.value = false;

        const timeoutDuration = calculateTimeoutDuration(formPrintManual.jml_label);
        setTimeout(() => {
            showSuccessNotification('Berhasil', 'Label berhasil dicetak');
            loading.value = false;
        }, timeoutDuration);

    } catch (error) {
        console.error('Error:', error);
        showErrorNotification('Gagal', 'Gagal mencetak label');
        loading.value = false;
    }
};

// Add ref for input focus
const periksa1Input = ref(null);

// Add this to the script setup section
const printManualModal = ref(false);

// Add this method in your script setup section
const printUlangLabel = async () => {
    try {
        loading.value = true;

        // Validate required fields
        if (!formPrintUlang.noRim || !formPrintUlang.npPetugas) {
            showErrorNotification('Error', 'Mohon lengkapi semua field');
            return;
        }

        // Send update request
        await axios.post('/api/order-besar/cetak-label/update', {
            po: formPrintUlang.po,
            dataRim: formPrintUlang.dataRim,
            noRim: formPrintUlang.noRim,
            npPetugas: formPrintUlang.npPetugas,
            team: formPrintUlang.team
        });

        // Generate and print label
        const printLabel = singleLabel(
            formPrintUlang.obc,
            formPrintUlang.noRim !== 999 ? formPrintUlang.noRim : "INS",
            colorObc,
            formPrintUlang.dataRim === "Kiri" ? "(*)" : "(**)",
            formPrintUlang.npPetugas,
            undefined,
            500,
        );

        await printWithoutDialog(printLabel);

        // Reset form and close modal
        formPrintUlang.npPetugas = '';
        printUlangModal.value = false;

        // Refresh data
        await fetchData();
        showSuccessNotification('Berhasil', 'Label berhasil dicetak');

    } catch (error) {
        console.error('Error:', error);
        showErrorNotification('Gagal', 'Gagal mencetak label');
    } finally {
        loading.value = false;
    }
};
</script>
