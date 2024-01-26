<template>
    <!-- Modal Prit Ulang -->
    <Modal
        :show="printUlangModal"
        @close="(Modal) => (printUlangModal = !printUlangModal)"
    >
        <form @submit.prevent="printUlangLabel">
            <div class="flex flex-col gap-4 px-4 py-4">
                <!-- Header -->
                <h1
                    class="py-1 text-lg font-semibold text-center border-b-2 text-slate-500 border-slate-500/70"
                >
                    Print Ulang / Ganti Data Rim
                </h1>

                <!-- Keterangan Kiri Kanan -->
                <TextInput
                    id="dataRim"
                    name="dataRim"
                    type="text"
                    class="py-1 mx-4 text-lg font-semibold text-center uppercase border text-slate-500 border-slate-500/70 rounded-mdr"
                    v-model="formPrintUlang.dataRim"
                    required
                    disabled
                    autocomplete="rfid"
                />

                <!-- Pilih Potongan -->
                <div class="flex justify-center gap-6">
                    <button
                        type="button"
                        @click="dataRimKiri()"
                        class="flex items-center gap-1 px-6 py-2 font-semibold transition duration-300 ease-in-out rounded-lg shadow-md bg-sky-400 text-sky-50 hover:brightness-90 drop-shadow-md shadow-sky-300/25"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2.5"
                            stroke="currentColor"
                            class="w-6 h-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75"
                            />
                        </svg>
                        KIRI
                    </button>
                    <button
                        type="button"
                        @click="dataRimKanan()"
                        class="flex items-center gap-1 px-6 py-2 font-semibold transition duration-300 ease-in-out rounded-lg shadow-md bg-sky-400 text-sky-50 hover:brightness-90 drop-shadow-md shadow-sky-300/25"
                    >
                        KANAN
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2.5"
                            stroke="currentColor"
                            class="w-6 h-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"
                            />
                        </svg>
                    </button>
                </div>

                <!-- List Nomor -->
                <div class="flex flex-wrap justify-center gap-4 mt-4">
                    <template v-for="n in dataPrintUlang" v-bind:key="refresh">
                    <button
                        v-if="n.np_users !== null && n.start !== null && n.finish === null"
                        type="button"
                        @click="pilihRim(n.no_rim, n.np_users)"
                        class="min-w-[8ch] px-4 py-2 text-xs bg-yellow-300 text-yellow-900 hover:brightness-95 duration-300 transition ease-in-out rounded drop-shadow shadow"
                    >
                        <div class="flex flex-col">
                            <span class="font-semibold text-yellow-950">{{ n.np_users }}</span>
                            <span class="font-bold text-green-700">{{
                                n.no_rim
                            }}</span>
                        </div>
                    </button>
                    <button
                        v-else-if="n.np_users !== null && n.start !== null && n.finish !== null"
                        type="button"
                        @click="pilihRim(n.no_rim, n.np_users)"
                        class="min-w-[8ch] px-4 py-2 text-xs bg-green-400 text-green-900 hover:brightness-95 duration-300 transition ease-in-out rounded drop-shadow shadow"
                    >
                        <div class="flex flex-col">
                            <span class="font-semibold text-green-950">{{ n.np_users }}</span>
                            <span class="font-bold text-indigo-700">{{
                                n.no_rim
                            }}</span>
                        </div>
                    </button>

                    <!-- Disable Jika NP Null -->
                    <button
                        v-else
                        type="button"
                        class="min-w-[8ch] px-4 py-2 text-xs bg-slate-500 text-slate-50 rounded drop-shadow shadow"
                        disabled
                    >
                        <div class="flex flex-col">
                            <span class="font-semibold">-</span>
                            <span class="font-bold text-yellow-300">{{
                                n.no_rim
                            }}</span>
                        </div>
                    </button>
                    </template>
                </div>

                <!-- Form -->
                <div class="flex justify-center gap-4 mt-4 px-7">
                    <!-- Nomor Rim -->
                    <div>
                        <InputLabel
                            for="noRimPU"
                            value="Nomor Rim"
                            class="text-sm font-semibold text-center"
                        />
                        <TextInput
                            id="noRimPU"
                            type="number"
                            name="dataRim"
                            class="block text-sm text-center bg-slate-300"
                            disabled
                            v-model="formPrintUlang.noRim"
                            required
                            autocomplete="noRimPU"
                        />
                    </div>

                    <!-- NP Petugas -->
                    <div>
                        <InputLabel
                            for="npPetugasPU"
                            value="NP Petugas"
                            class="text-sm font-semibold text-center"
                        />
                        <TextInput
                            id="npPetugasPU"
                            type="text"
                            class="block text-sm text-center uppercase"
                            v-model="formPrintUlang.npPetugas"
                            required
                            autocomplete="npPetugasPU"
                        />
                    </div>
                </div>

                <!-- Action -->
                <div class="flex justify-center gap-4 pt-4 px-7">
                    <button
                        type="button"
                        class="flex items-center gap-1 px-6 py-2 mr-auto font-semibold text-red-500 underline transition duration-300 ease-in-out border border-red-500 rounded-lg shadow-md bg-red-50 hover:brightness-90 drop-shadow-md shadow-red-300/25"
                    >
                        Hapus
                    </button>
                    <button
                        type="submit"
                        class="flex items-center gap-1 px-6 py-2 font-semibold transition duration-300 ease-in-out rounded-lg shadow-md bg-sky-400 text-sky-50 hover:brightness-90 drop-shadow-md shadow-sky-300/25"
                    >
                        Print
                    </button>
                </div>
            </div>
        </form>
    </Modal>

    <!-- Content -->
    <ContentLayout>
        <div class="flex flex-col justify-center py-8">
            <form @submit.prevent="submit">
                <div class="flex flex-col justify-center gap-6 mx-auto w-fit">
                    <!-- Team -->
                    <div class="mx-auto mb-7">
                        <InputLabel
                            for="team"
                            value="Team"
                            class="text-2xl font-extrabold text-center"
                        />

                        <select
                            id="team"
                            ref="team"
                            v-model="form.team"
                            type="text"
                            disabled
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block px-10 py-2 mt-2 text-lg w-full drop-shadow"
                        >
                            <option
                                v-for="team in props.listTeam"
                                :value="team.id"
                            >
                                {{ team.workstation }}
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-between gap-6 mb-6 w-fit">
                        <!-- PO -->
                        <div>
                            <InputLabel
                                for="po"
                                value="Nomor PO"
                                class="text-2xl font-extrabold text-center"
                            />

                            <TextInput
                                id="po"
                                ref="po"
                                v-model="form.po"
                                type="number"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-300 drop-shadow"
                                autocomplete="po"
                                disabled
                            />
                        </div>

                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel
                                for="obc"
                                value="Nomor OBC"
                                class="text-2xl font-extrabold text-center"
                            />

                            <TextInput
                                id="obc"
                                ref="obc"
                                v-model="form.obc"
                                type="text"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-300 drop-shadow"
                                autocomplete="obc"
                                disabled
                            />
                        </div>

                        <!-- Seri -->
                        <div>
                            <InputLabel
                                for="seri"
                                value="Seri"
                                class="text-2xl font-extrabold text-center"
                            />

                            <TextInput
                                id="seri"
                                ref="seri"
                                v-model="form.seri"
                                type="number"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-300 drop-shadow"
                                autocomplete="seri"
                                disabled
                            />
                        </div>
                    </div>

                    <div class="flex justify-between gap-6 mx-auto w-fit">
                        <!-- No Rim -->
                        <div>
                            <InputLabel
                                for="no_rim"
                                value="Nomor Rim"
                                class="text-2xl font-extrabold text-center"
                            />

                            <TextInput
                                id="no_rim"
                                ref="no_rim"
                                v-model="form.no_rim"
                                type="number"
                                class="block bg-slate-300/80 drop-shadow-md shadow-md w-full px-4 py-2 mt-2 text-lg text-center font-bold"
                                autocomplete="no_rim"
                                min="1"
                                disabled
                            />
                        </div>

                        <!-- Lembar Potong -->
                        <div>
                            <InputLabel
                                for="lbr_ptg"
                                value="Lembar Potong"
                                class="text-2xl font-extrabold text-center"
                            />

                            <TextInput
                                id="lbr_ptg"
                                ref="lbr_ptg"
                                v-model="form.lbr_ptg"
                                type="text"
                                class="block bg-slate-300/80 drop-shadow-md shadow-md w-full px-4 py-2 mt-2 text-lg text-center font-bold"
                                autocomplete="lbr_ptg"
                                disabled
                            />
                        </div>
                    </div>

                    <!-- NP Pemeriksa -->
                    <div>
                        <InputLabel
                            for="rfid"
                            value="Silahkan Scan NP mu"
                            class="text-2xl font-semibold text-center"
                        />

                        <TextInput
                            id="rfid"
                            type="text"
                            class="block w-full mt-4 text-center uppercase"
                            v-model="form.rfid"
                            required
                            autofocus
                            autocomplete="rfid"
                        />

                        <InputError class="mt-2" />
                    </div>
                </div>

                <!-- Button  -->
                <div class="flex justify-center gap-6 mx-auto w-fit">
                    <!-- Reset Form -->
                    <button
                        type="button"
                        @click="form.rfid = null"
                        class="flex justify-center px-4 py-4 mx-auto mt-8 text-lg font-bold shadow-md w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-violet-500/20 text-violet-50"
                    >
                        Clear
                    </button>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="flex justify-center px-4 py-4 mx-auto mt-8 shadow-md w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-green-500/20"
                    >
                        <div class="text-lg font-bold text-yellow-50">
                            Generate
                        </div>
                    </button>

                    <!-- Print Ulang -->
                    <button
                        type="button"
                        @click="
                            [getDataRim(), (printUlangModal = !printUlangModal)]
                        "
                        class="flex justify-center px-4 py-4 mx-auto mt-8 shadow-md w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-green-500/20"
                    >
                        <div class="text-lg font-bold text-yellow-50">
                            Print Ulang
                        </div>
                    </button>
                </div>
            </form>
            <div class="flex gap-6 mx-auto mt-10">
                <!-- Back Button -->
                <a href="#" onclick="history.back()"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start  drop-shadow-md shadow-md flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" />
                    </svg>
                    Back
                </a>

                <!-- Home Button -->
                <Link
                    :href="route('dashboard')"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start drop-shadow-md shadow-md flex items-center gap-1.5"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="w-6 h-6"
                    >
                        <path
                            d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z"
                        />
                        <path
                            d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z"
                        />
                    </svg>
                </Link>
            </div>
        </div>
    </ContentLayout>
</template>

<script setup>
import { reactive, ref } from "vue";
import Modal from "@/Components/Modal.vue";
import ContentLayout from "@/Layouts/ContentLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import axios from "axios";
import NavigateBackButton from "@/Components/NavigateBackButton.vue";

const props = defineProps({
    product: Object,
    listTeam: Object,
    crntTeam: Number,
    noRim: Number,
    potongan: String,
    showModal: {
        type: Boolean,
        default: false,
    },
    printUlangModal: {
        type: Boolean,
        default: false,
    },
});

const dataPrintUlang = ref();

// Form
const form = useForm({
    id: props.product.id,
    po: props.product.no_po,
    obc: props.product.no_obc,
    team: props.crntTeam,
    seri: props.product.no_obc.substr(4,1),
    jml_rim: 1,
    lbr_ptg: props.potongan,
    no_rim: props.noRim,
    rfid: "",
});

// Form Print Ulang / Edit data Rim
const formPrintUlang = reactive({
    dataRim: "Kiri",
    noRim: "",
    npPetugas: "",
    po: props.product.no_po,
    obc: props.product.no_obc,
});

const dataRimKanan = async () => {
    formPrintUlang.dataRim = "Kanan";
    getDataRim();
};

const dataRimKiri = async () => {
    formPrintUlang.dataRim = "Kiri";
    getDataRim();
};

// Tarik Data Untuk Perint Ulang Rim
const getDataRim = () => {
    axios.post('/api/non-perekat/non-personal/print-label/edit', formPrintUlang).then((res) => {
        dataPrintUlang.value = res.data;
    });
};

const pilihRim = (noRim, np) => {
    formPrintUlang.noRim = noRim;
    formPrintUlang.npPetugas = np;
};

const printUlangLabel = () => {
    // Check Jika Ini Chrome Karena Chrome harus ada TImeout
    // let is_chrome = function () { return Boolean(window.chrome); }
    let date = new Date();
    const months = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Agu",
        "Sep",
        "Okt",
        "Nov",
        "Des",
    ];

    let obc_color = form.seri == 3 ? "#b91c1c" : "#1d4ed8"
    let obc = formPrintUlang.obc;
    // let tgl = date.getDay() + "-" + months[date.getMonth()] + "-" + date.getFullYear();
    let tgl =
        date.getDate() +
        "-" +
        months[date.getMonth()] +
        "-" +
        date.getFullYear();
    let np = formPrintUlang.npPetugas;
    let noRim = formPrintUlang.noRim;
    let time = date.getHours() + " : " + date.getMinutes();
    let sisiran = formPrintUlang.dataRim == "Kiri" ? "(*)" : "(**)";
    let WinPrint = window.open(
        "",
        "",
        "left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0"
    );
    let printLabel = `<!DOCTYPE html>
                            <html>
                                <head>
                                </head>
                                <body>
                                    <div style='page-break-after:always; width:100%; height:100%;'>
                                        <div style="margin-top:19.5vh; margin-left:17vh">
                                            <span style="font-weight:600; text-align:center;">${tgl}</span>
                                            <h1 style="font-size: 24px; line-height: 32px; margin-left:25px; font-weight:600; text-align:center; display:inline-block; padding-top:6px; color:${obc_color}">${obc}</h1>
                                        </div>
                                        <div style="margin-top:11.5px; margin-left:16vh">
                                            <h1 style="font-size: 24px; line-height: 32px; margin-left:155px; margin-right:auto; ;font-weight:600;text-align:center;display:inline-block;text-transform: uppercase;">${np}</h1>
                                        </div>
                                        <div style="margin-top:43px; margin-left:13vh">
                                            <h1 style="display: inline-block; margin-left: 160px; margin-right: auto; text-align: center; font-size: 20px; line-height: 28px; font-weight:500;">${noRim} ${sisiran} <span style="font-size:12px; margin-left:8px">${time}</span></h1>
                                        </div>
                                    </div>
                                </body>
                            </html>`;

    WinPrint.document.write(printLabel);

    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print();
    WinPrint.close();
    router.post("/api/non-perekat/non-personal/print-label/update", formPrintUlang, {
        onFinish: () => {
            router.get("/non-perekat/non-personal/print-label/"+ form.team +"/" + form.id);
        },
    });
};

const submit = () => {
    // Check Jika Ini Chrome Karena Chrome harus ada TImeout
    // let is_chrome = function () { return Boolean(window.chrome); }
    let date = new Date();
    const months = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "Mei",
        "Jun",
        "Jul",
        "Agu",
        "Sep",
        "Okt",
        "Nov",
        "Des",
    ];

    let obc_color = form.seri == 3 ? "#b91c1c" : "#1d4ed8"
    let obc = form.obc;
    let tgl =
        date.getDate() +
        "-" +
        months[date.getMonth()] +
        "-" +
        date.getFullYear();
    let np = form.rfid;
    let noRim = form.no_rim;
    let time =
        date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
    let sisiran = form.lbr_ptg == "Kiri" ? "(*)" : "(**)";
    let WinPrint = window.open(
        "",
        "",
        "left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0"
    );
    let printLabel = `<!DOCTYPE html>
                            <html>
                                <head>
                                </head>
                                <body>
                                    <div style='page-break-after:always; width:100%; height:100%;'>
                                        <div style="margin-top:19.5vh; margin-left:17vh">
                                            <span style="font-weight:600; text-align:center;">${tgl}</span>
                                            <h1 style="font-size: 24px; line-height: 32px; margin-left:25px; font-weight:600; text-align:center; display:inline-block; padding-top:6px; color:${obc_color}">${obc}</h1>
                                        </div>
                                        <div style="margin-top:11.5px; margin-left:16vh">
                                            <h1 style="font-size: 24px; line-height: 32px; margin-left:155px; margin-right:auto; ;font-weight:600;text-align:center;display:inline-block;text-transform: uppercase;">${np}</h1>
                                        </div>
                                        <div style="margin-top:43px; margin-left:13vh">
                                            <h1 style="display: inline-block; margin-left: 160px; margin-right: auto; text-align: center; font-size: 20px; line-height: 28px; font-weight:500;">${noRim} ${sisiran} <span style="font-size:12px; margin-left:8px">${time}</span></h1>
                                        </div>
                                    </div>
                                </body>
                            </html>`;

    WinPrint.document.write(printLabel);

    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print();
    WinPrint.close();
    router.post("/api/non-perekat/non-personal/print-label", form, {
        onFinish: () => {
            router.get("/non-perekat/non-personal/print-label/"+ form.team +"/" + form.id);
        },
    });
    form.rfid = null;
};
</script>
