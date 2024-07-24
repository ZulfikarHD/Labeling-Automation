<template>
    <!-- Modal for Reprinting -->
    <Modal :show="printUlangModal" @close="(Modal) => (printUlangModal = !printUlangModal)">
        <form @submit.prevent="printUlangLabel">
            <div class="flex flex-col gap-4 px-4 py-4">
                <!-- Header -->
                <h1 class="py-1 text-lg font-semibold text-center border-b-2 text-slate-500 border-slate-500/70">
                    Print Ulang / Ganti Data Rim
                </h1>

                <!-- Rim Data Description -->
                <TextInput id="dataRim" name="dataRim" type="text"
                    class="py-1 mx-4 text-lg font-semibold text-center uppercase border text-slate-500 border-slate-500/70 rounded-mdr"
                    v-model="formPrintUlang.dataRim" required disabled autocomplete="rfid" />

                <!-- Select Rim Side -->
                <div class="flex justify-center gap-6">
                    <button type="button" @click="dataRimKiri()"
                        class="flex items-center gap-1 px-6 py-2 font-semibold transition duration-300 ease-in-out rounded-lg shadow bg-sky-400 text-sky-50 hover:brightness-90 drop-shadow shadow-sky-300/25">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" />
                        </svg>
                        KIRI
                    </button>
                    <button type="button" @click="dataRimKanan()"
                        class="flex items-center gap-1 px-6 py-2 font-semibold transition duration-300 ease-in-out rounded-lg shadow bg-sky-400 text-sky-50 hover:brightness-90 drop-shadow shadow-sky-300/25">
                        KANAN
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
                        </svg>
                    </button>
                </div>

                <!-- List of Rim Numbers -->
                <div class="flex flex-wrap justify-center gap-4 mt-4">
                    <template v-for="n in dataPrintUlang" v-bind:key="n.no_rim">
                        <button v-if="n.np_users !== null && n.start !== null && n.finish === null" type="button"
                            @click="pilihRim(n.no_rim, n.np_users)"
                            class="min-w-[8ch] px-4 py-2 text-xs bg-yellow-300 text-yellow-900 hover:brightness-95 duration-300 transition ease-in-out rounded drop-shadow shadow">
                            <div class="flex flex-col">
                                <span class="font-semibold text-yellow-950">{{ n.np_users }}</span>
                                <span class="font-bold text-green-700">{{ n.no_rim }}</span>
                            </div>
                        </button>
                        <button v-else-if="n.np_users !== null && n.start !== null && n.finish !== null" type="button"
                            @click="pilihRim(n.no_rim, n.np_users)"
                            class="min-w-[8ch] px-4 py-2 text-xs bg-green-400 text-green-900 hover:brightness-95 duration-300 transition ease-in-out rounded drop-shadow shadow">
                            <div class="flex flex-col">
                                <span class="font-semibold text-green-950">{{ n.np_users }}</span>
                                <span class="font-bold text-indigo-700">{{ n.no_rim }}</span>
                            </div>
                        </button>

                        <!-- Disable if NP is Null -->
                        <button v-else-if="n.no_rim === 999" type="button"
                            @click="pilihRim(n.no_rim, n.np_users)"
                            class="min-w-[8ch] px-4 py-2 text-xs bg-violet-500 text-violet-50 active:bg-violet-700 hover:bg-violet-600 transition ease-in-out duration-200 rounded drop-shadow shadow">
                            <div class="flex flex-col">
                                <span class="font-semibold">{{ n.np_users }}</span>
                                <span class="font-bold text-violet-50">Inschiet</span>
                            </div>
                        </button>

                        <!-- Disable if NP is Null -->
                        <button v-else type="button"
                            class="min-w-[8ch] px-4 py-2 text-xs bg-slate-500 text-slate-50 rounded drop-shadow shadow"
                            disabled>
                            <div class="flex flex-col">
                                <span class="font-semibold">-</span>
                                <span class="font-bold text-yellow-300">{{ n.no_rim }}</span>
                            </div>
                        </button>
                    </template>
                </div>

                <!-- Form Section -->
                <div class="flex justify-center gap-4 mt-4 px-7">
                    <!-- Rim Number -->
                    <div>
                        <InputLabel for="noRimPU" value="Nomor Rim" class="text-sm font-semibold text-center" />
                        <template v-if="formPrintUlang.noRim === 999">
                            <TextInput id="noRimPU" type="hidden" name="dataRim" class="block text-sm text-center bg-slate-300"
                                disabled v-model="formPrintUlang.noRim" required autocomplete="noRimPU" />
                            <TextInput id="noRimPU" type="text" name="dataRim" class="block text-sm text-center bg-slate-300"
                                disabled value="Inschiet" required autocomplete="noRimPU" />
                        </template>
                        <template v-else>
                            <TextInput id="noRimPU" type="number" name="dataRim" class="block text-sm text-center bg-slate-300"
                                disabled v-model="formPrintUlang.noRim" required autocomplete="noRimPU" />
                        </template>
                    </div>

                    <!-- NP Officer -->
                    <div>
                        <InputLabel for="npPetugasPU" value="NP Petugas" class="text-sm font-semibold text-center" />
                        <TextInput id="npPetugasPU" type="text" class="block text-sm text-center uppercase"
                            v-model="formPrintUlang.npPetugas" required autocomplete="npPetugasPU" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-center gap-4 pt-4 px-7">
                    <button type="button"
                        class="flex items-center gap-1 px-6 py-2 mr-auto font-semibold text-red-500 underline transition duration-300 ease-in-out border border-red-500 rounded-lg shadow bg-red-50 hover:brightness-90 drop-shadow shadow-red-300/25">
                        Hapus
                    </button>
                    <button type="submit"
                        class="flex items-center gap-1 px-6 py-2 font-semibold transition duration-300 ease-in-out rounded-lg shadow bg-sky-400 text-sky-50 hover:brightness-90 drop-shadow shadow-sky-300/25">
                        Print
                    </button>
                </div>
            </div>
        </form>
    </Modal>

    <!-- Main Content -->
    <ContentLayout>
        <div class="flex flex-col justify-center py-8">
            <form @submit.prevent="submit">
                <div class="flex flex-col justify-center gap-6 mx-auto px-8 max-w-2xl">
                    <!-- Team Selection -->
                    <div class="mx-auto w-full">
                        <InputLabel for="team" value="Team" class="text-2xl font-extrabold text-center" />

                        <select id="team" ref="team" v-model="form.team" disabled
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block px-10 py-2 mt-2 text-lg w-full drop-shadow flex-grow">
                            <option v-for="team in props.listTeam" :key="team.id" :value="team.id">
                                {{ team.workstation }}
                            </option>
                        </select>
                    </div>

                    <div class="flex justify-between gap-4 w-full">
                        <!-- PO Number -->
                        <div class="flex-grow">
                            <InputLabel for="po" value="Nomor PO" class="text-2xl font-extrabold text-center" />

                            <TextInput id="po" ref="po" v-model="form.po" type="number"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-200/80 drop-shadow"
                                autocomplete="po" disabled />
                        </div>

                        <!-- OBC Number -->
                        <div class="flex-grow">
                            <InputLabel for="obc" value="Nomor OBC" class="text-2xl font-extrabold text-center" />

                            <TextInput id="obc" ref="obc" v-model="form.obc" type="text"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-200/80 drop-shadow"
                                autocomplete="obc" disabled />
                        </div>

                        <!-- Series -->
                        <div class="flex-grow">
                            <InputLabel for="seri" value="Seri" class="text-2xl font-extrabold text-center" />

                            <TextInput id="seri" ref="seri" v-model="form.seri" type="number"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-200/80 drop-shadow"
                                autocomplete="seri" disabled />
                        </div>
                    </div>

                    <div class="flex justify-between gap-6 mx-auto w-full">
                        <!-- Rim Number -->
                        <div class="flex-grow">
                            <InputLabel for="no_rim" value="Nomor Rim" class="text-2xl font-extrabold text-center" />

                            <template v-if="form.no_rim !== 999">
                                <TextInput id="no_rim" ref="no_rim" v-model="form.no_rim" type="number"
                                    class="block bg-slate-200/80  drop-shadow shadow w-full px-4 py-2 mt-2 text-lg text-center font-bold"
                                    autocomplete="no_rim" min="1" disabled />
                            </template>
                            <template v-else>
                                <TextInput id="no_rim" ref="no_rim" v-model="form.no_rim" type="hidden"
                                    class="block bg-slate-200/80  drop-shadow shadow w-full px-4 py-2 mt-2 text-lg text-center font-bold"
                                    autocomplete="no_rim" min="1" disabled />
                                <TextInput type="text" value="Inschiet"
                                    class="block bg-slate-200/80  drop-shadow shadow w-full px-4 py-2 mt-2 text-lg text-center font-bold"
                                    autocomplete="no_rim" min="1" disabled />
                            </template>
                        </div>

                        <!-- Cutting Sheet -->
                        <div class="flex-grow">
                            <InputLabel for="lbr_ptg" value="Lembar Potong" class="text-2xl font-extrabold text-center" />

                            <TextInput id="lbr_ptg" ref="lbr_ptg" v-model="form.lbr_ptg" type="text"
                                class="block bg-slate-200/80  drop-shadow shadow w-full px-4 py-2 mt-2 text-lg text-center font-bold"
                                autocomplete="lbr_ptg" disabled />
                        </div>
                    </div>

                    <!-- NP Inspector -->
                    <div>
                        <InputLabel for="rfid" value="Silahkan Scan NP mu" class="text-2xl font-semibold text-center" />

                        <TextInput id="rfid" type="text" class="block w-full mt-4 text-center uppercase" v-model="form.rfid"
                            required autofocus autocomplete="rfid" />

                        <InputError class="mt-2" />
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-center gap-6 mx-auto w-fit">
                    <!-- Reset Form -->
                    <button type="button" @click="form.rfid = null"
                        class="flex justify-center px-4 py-4 mx-auto mt-8 text-lg font-bold shadow w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start hover:brightness-90 drop-shadow shadow-violet-500/20 text-violet-50">
                        Clear
                    </button>

                    <!-- Submit -->
                    <button type="submit"
                        class="flex justify-center px-4 py-4 mx-auto mt-8 shadow w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start hover:brightness-90 drop-shadow shadow-green-500/20">
                        <div class="text-lg font-bold text-yellow-50">
                            Generate
                        </div>
                    </button>

                    <!-- Reprint -->
                    <button type="button" @click="
                        [getDataRim(), (printUlangModal = !printUlangModal)]
                        "
                        class="flex justify-center px-4 py-4 mx-auto mt-8 shadow w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start hover:brightness-90 drop-shadow shadow-green-500/20">
                        <div class="text-lg font-bold text-yellow-50">
                            Print Ulang
                        </div>
                    </button>
                </div>
                <!-- Finish Order -->
                <button type="button" @click="finish_order"
                    class="flex justify-center px-4 py-4 mx-auto mt-8 shadow max-w-sm w-full bg-gradient-to-r from-cyan-400 to-cyan-500 rounded-xl text-start hover:brightness-90 drop-shadow shadow-green-500/20">
                    <div class="text-lg font-bold text-yellow-50">
                        Selesaikan Order
                    </div>
                </button>
            </form>
            <div class="flex gap-6 mx-auto mt-10">
                <!-- Back Button -->
                <a href="#" onclick="history.back()"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start  drop-shadow shadow flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" />
                    </svg>
                    Back
                </a>

                <!-- Home Button -->
                <Link :href="route('dashboard')"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start drop-shadow shadow flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                    <path
                        d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                    <path
                        d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
                </svg>
                </Link>
            </div>

        </div>
        <!-- Verification Table -->
        <TableVerifikasiPegawai :team="form.team" :date="form.date"/>
    </ContentLayout>
</template>
<script setup>
import { reactive, ref } from "vue";
import Modal from "@/Components/Modal.vue";
import ContentLayout from "@/Layouts/ContentLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import TableVerifikasiPegawai from "@/Components/TableVerifikasiPegawai.vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import axios from "axios";

// Mendefinisikan properti komponen
const props = defineProps({
    product: Object, // Detail produk
    listTeam: Object, // Daftar tim
    crntTeam: Number, // ID tim saat ini
    noRim: Number, // Nomor rim
    potongan: String, // Spesifikasi pemotongan
    date: String, // Tanggal untuk operasi
    showModal: {
        type: Boolean,
        default: false, // Visibilitas modal
    },
    printUlangModal: {
        type: Boolean,
        default: false, // Visibilitas modal cetak ulang
    },
});

// Referensi reaktif untuk data cetak ulang
const dataPrintUlang = ref();

// Data formulir untuk operasi utama
const form = useForm({
    id: props.product.id, // ID produk
    po: props.product.no_po, // Nomor pesanan
    obc: props.product.no_obc, // Nomor OBC
    team: props.crntTeam, // Tim saat ini
    seri: props.product.no_obc.substr(4, 1), // Seri yang diambil dari OBC
    jml_rim: 1, // Jumlah rim
    lbr_ptg: props.potongan, // Spesifikasi pemotongan
    no_rim: props.noRim, // Nomor rim
    rfid: "", // RFID untuk pemindaian
    date: props.date, // Tanggal untuk operasi
});

// Form reaktif untuk mencetak ulang atau mengedit data rim
const formPrintUlang = reactive({
    dataRim: "Kiri", // Sisi rim default
    noRim: "", // Nomor rim
    npPetugas: "", // NP petugas
    po: props.product.no_po, // Nomor pesanan
    obc: props.product.no_obc, // Nomor OBC
    team: props.crntTeam, // Tim saat ini
});

// Fungsi untuk memilih rim kanan
const dataRimKanan = async () => {
    formPrintUlang.dataRim = "Kanan"; // Mengatur sisi rim ke kanan
    getDataRim(); // Mengambil data untuk rim yang dipilih
};

// Fungsi untuk memilih rim kiri
const dataRimKiri = async () => {
    formPrintUlang.dataRim = "Kiri"; // Mengatur sisi rim ke kiri
    getDataRim(); // Mengambil data untuk rim yang dipilih
};

// Referensi reaktif untuk verifikasi pegawai
const verifPegawai = ref()

// Fungsi untuk mengambil pendapatan harian untuk verifikasi
const produksiPegawai = () => {
    axios.get('/api/pendapatan-harian?date=' + form.date + '&team=' + form.team).then((res) => {
        verifPegawai.value = res.data; // Menyimpan data yang diambil
    });
}

// Panggilan awal untuk mengambil data pegawai
produksiPegawai()

// Fungsi untuk mengambil data untuk mencetak ulang rim
const getDataRim = () => {
    axios.post('/api/non-perekat/non-personal/print-label/edit', formPrintUlang).then((res) => {
        dataPrintUlang.value = res.data; // Menyimpan data yang diambil
    });
};

// Fungsi untuk memilih rim berdasarkan nomor dan petugas
const pilihRim = (noRim, np) => {
    formPrintUlang.noRim = noRim; // Mengatur nomor rim yang dipilih
    formPrintUlang.npPetugas = np; // Mengatur NP petugas yang dipilih
};

// Fungsi untuk menghasilkan HTML label cetak
const generatePrintLabel = (obc, np, noRim, sisiran) => {
    let date = new Date(); // Tanggal saat ini
    const months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
    let tgl = `${date.getDate()}-${months[date.getMonth()]}-${date.getFullYear()}`; // Tanggal yang diformat
    let time = `${date.getHours()} : ${date.getMinutes()}`; // Waktu saat ini
    let obc_color = form.seri == 3 ? "#b91c1c" : "#1d4ed8"; // Warna berdasarkan seri

    // Mengembalikan struktur HTML untuk pencetakan
    return `<!DOCTYPE html>
        <html>
            <head></head>
            <body>
                <div style='page-break-after:always; width:100%; height:100%;'>
                    <div style="margin-top:19.5vh; margin-left:18vh">
                        <span style="font-weight:600; text-align:center;">${tgl}</span>
                        <h1 style="font-size: 24px; line-height: 32px; margin-left:25px; font-weight:600; text-align:center; display:inline-block; padding-top:6px; color:${obc_color}">${obc}</h1>
                    </div>
                    <div style="margin-top:16.2px; margin-left:16vh">
                        <h1 style="font-size: 20px; line-height: 32px; margin-left:155px; margin-right:auto; font-weight:600;text-align:center;display:inline-block;text-transform: uppercase;">${np}</h1>
                    </div>
                    <div style="margin-top:47.5px; margin-left:13vh">
                        <h1 style="display: inline-block; margin-left: 160px; margin-right: auto; text-align: center; font-size: 20px; line-height: 28px; font-weight:500;">${noRim} ${sisiran} <span style="font-size:12px; margin-left:8px">${time}</span></h1>
                    </div>
                </div>
            </body>
        </html>`;
};

// Fungsi untuk mencetak label cetak ulang
const printUlangLabel = () => {
    let printLabel = generatePrintLabel(formPrintUlang.obc, formPrintUlang.npPetugas, formPrintUlang.noRim !== 999 ? formPrintUlang.noRim : "INS", formPrintUlang.dataRim == "Kiri" ? "(*)" : "(**)");
    let WinPrint = window.open("", "", "left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0");
    WinPrint.document.write(printLabel); // Menulis label yang dihasilkan ke jendela baru
    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print(); // Memicu pencetakan
    WinPrint.close();
    router.post("/api/non-perekat/non-personal/print-label/update", formPrintUlang, {
        onFinish: () => {
            router.get("/non-perekat/non-personal/print-label/" + form.team + "/" + form.id); // Mengalihkan setelah pembaruan
        },
    });
};

// Fungsi untuk mengirim formulir utama
const submit = () => {
    let printLabel = generatePrintLabel(form.obc, form.rfid, form.no_rim !== 999 ? form.no_rim : "INS", form.lbr_ptg == "Kiri" ? "(*)" : "(**)");
    let WinPrint = window.open("", "", "left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0");
    WinPrint.document.write(printLabel); // Menulis label yang dihasilkan ke jendela baru
    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print(); // Memicu pencetakan
    WinPrint.close();
    router.post("/api/non-perekat/non-personal/print-label", form, {
        onFinish: () => {
            router.get(props.noRim !== 0 ? "/non-perekat/non-personal/print-label/" + form.team + "/" + form.id : "/non-perekat/non-personal/verif"); // Mengalihkan setelah pengiriman
        },
    });
    form.rfid = null; // Menghapus RFID setelah pengiriman
};

// Fungsi untuk menyelesaikan pesanan
const finish_order = () => {
    axios.put("/api/nonPers-finish-order/" + form.po) // Memperbarui status pesanan
    router.get("/non-perekat/non-personal/verif") // Mengalihkan ke halaman verifikasi
}
</script>
