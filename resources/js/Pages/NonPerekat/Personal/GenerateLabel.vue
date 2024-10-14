<script setup>
import { reactive,ref } from "vue";
import Modal from "@/Components/Modal.vue";
import ContentLayout from "@/Layouts/ContentLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import axios from "axios";
import NavigateBackButton from "@/Components/NavigateBackButton.vue";

// Define a reactive form object to store form data
const form = reactive({
    po: "", // Production Order number
    obc: "", // Order Bea Cukai number
    jml_lembar: "", // Number of sheets/rims
    jml_label: "", // Number of labels
    seri: "", // Series number
    rfid: "", // NP Inspector
});

// Function to fetch data based on the Production Order number
const fetchData = () => {
    axios.get("/api/gen-perso-label/" + form.po).then((res) => {
        let total_label = Math.ceil(res.data.rencet / 500);

        form.obc = res.data.no_obc;
        form.jml_lembar = res.data.rencet + " / " + total_label + " Rim";
        form.jml_label = total_label;
    });
};

// Fungsi untuk menghasilkan HTML label cetak
const generatePrintLabel = (obc, np) => {
    let date = new Date(); // Tanggal saat ini
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
    let tgl = `${date.getDate()}-${
        months[date.getMonth()]
    }-${date.getFullYear()}`; // Tanggal yang diformat
    let time = `${date.getHours()} : ${date.getMinutes()}`; // Waktu saat ini
    let obc_color = form.seri == 3 ? "#b91c1c" : "#1d4ed8"; // Warna berdasarkan seri

    // Mengembalikan struktur HTML untuk pencetakan
    return `<!DOCTYPE html>
        <html>
            <head></head>
            <body>
                <div style='page-break-after:avoid; width:100%; height:fit-content;'>
                    <div style="margin-top:19.5vh; margin-left:18.5vh">
                        <span style="font-weight:600; text-align:center;">${tgl}</span>
                        <h1 style="font-size: 24px; line-height: 32px; margin-left:25px; font-weight:600; text-align:center; display:inline-block; padding-top:6px; color:${obc_color}">${obc}</h1>
                    </div>
                    <div style="margin-top:0.75rem; margin-left:16vh">
                        <h1 style="font-size: 20px; line-height: 32px; margin-left:155px; margin-right:auto; font-weight:600;text-align:center;display:inline-block;text-transform: uppercase;">${np}</h1>
                    </div>
                    <div style="margin-top:47.5px; margin-left:13vh">
                        <h1 style="display: inline-block; margin-left: 160px; margin-right: auto; text-align: center; font-size: 20px; line-height: 28px; font-weight:500; color:${obc_color}"><span style="font-size:12px; margin-left:8px">${time}</span></h1>
                    </div>
                </div>
            </body>
        </html>`;
};

const printFrame = ref(null);

const printWithoutDialog = (content) => {
    const iframe = printFrame.value;
    const doc = iframe.contentWindow.document;
    doc.open();
    // Add styles to hide header and footer and limit to the first page
    doc.write(`
        <style>
            @media print {
                @page {
                    margin: 3rem; /* Remove default margins */
                }
                body {
                    margin: 0; /* Remove body margin */
                }
                header, footer {
                    display: none !important; /* Hide header and footer */
                }
                * {
                    -webkit-print-color-adjust: exact; /* Ensure colors are printed correctly */
                    print-color-adjust: exact; /* Ensure colors are printed correctly */
                }
            }
        </style>
        ${content}
    `);
    doc.close();
    iframe.contentWindow.focus();

    // Use a timeout to allow the content to load before printing
    setTimeout(() => {
        iframe.contentWindow.print();
    }, 100); // Adjust the timeout as necessary
};

// Fungsi untuk mengirim formulir utama
const submit = () => {
    generatePrintLabel(
        form.obc,
        form.rfid,
    );
    // let printLabel = generatePrintLabel(
    //     form.obc,
    //     form.rfid,
    // );
    // printWithoutDialog(printLabel);
    router.post("/api/non-perekat/personal/print-label", form, {
        onFinish: () => {"/non-perekat/personal"},
    });
    form.rfid = null; // Menghapus RFID setelah pengiriman
};
</script>

<template>
    <ContentLayout>
        <!-- Modal -->
        <!-- <Modal :show="showModal" @close="showModal = !showModal">
            <div
                class="px-8 py-4 bg-white rounded-lg shadow drop-shadow shadow-slate-300/25"
            >
                <h1
                    class="text-2xl font-bold text-center text-green-600 brightness-110"
                >
                    Label Berhasil Di Buat
                </h1>
            </div>
        </Modal> -->

        <!-- Form -->
        <div class="py-12">
            <form @submit.prevent="submit">
                <div
                    class="flex flex-col justify-center gap-6 mx-auto mt-20 w-fit"
                >
                    <!-- Nomor PO -->
                    <div>
                        <InputLabel
                            for="po"
                            value="Nomor PO"
                            class="text-4xl font-extrabold text-center"
                        />

                        <TextInput
                            id="po"
                            ref="po"
                            v-model="form.po"
                            @input="fetchData"
                            type="number"
                            class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                            autocomplete="po"
                            placeholder="Production Order"
                            required
                            autofocus
                        />
                    </div>
                    <div class="flex justify-between gap-6 mb-10 w-fit">
                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel
                                for="obc"
                                value="Nomor OBC"
                                class="text-4xl font-extrabold text-center"
                            />

                            <TextInput
                                id="obc"
                                ref="obc"
                                v-model="form.obc"
                                type="text"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                                autocomplete="obc"
                                placeholder="Order Bea Cukai"
                                required
                            />
                        </div>

                        <!-- Jumlah Lembar/Rim -->
                        <div>
                            <InputLabel
                                for="jml_lembar"
                                value="Lembar / Rim"
                                class="text-4xl font-extrabold text-center"
                            />

                            <TextInput
                                id="jml_lembar"
                                ref="jml_lembar"
                                v-model="form.jml_lembar"
                                type="text"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center bg-slate-200/70"
                                autocomplete="jml_lembar"
                                placeholder="Lembar/Rim"
                                min="1"
                                disabled
                            />
                        </div>

                        <!-- Jumlah Label -->
                        <div>
                            <InputLabel
                                for="jml_label"
                                value="Jumlah Label"
                                class="text-4xl font-extrabold text-center"
                            />

                            <TextInput
                                id="jml_label"
                                ref="jml_label"
                                v-model="form.jml_label"
                                type="number"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                                autocomplete="jml_label"
                                placeholder="Label"
                                min="1"
                                required
                            />
                        </div>
                    </div>

                    <!-- Nomor Rim -->
                    <!-- <h4 class="text-4xl font-semibold text-center">
                        Nomor RIM
                    </h4> -->

                    <!-- NP Pemeriksa -->
                    <div>
                        <InputLabel
                            for="rfid"
                            value="NP Pemeriksa"
                            class="text-4xl font-extrabold text-center"
                        />

                        <TextInput
                            id="rfid"
                            ref="rfid"
                            v-model="form.rfid"
                            type="text"
                            class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                            autocomplete="rfid"
                            placeholder="NP"
                            required
                        />
                    </div>
                </div>
                <div class="flex justify-center gap-6 mx-auto w-fit">
                    <button
                        class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-11"
                    >
                        <!-- <Link
                            :href="route('p.products.create')"
                            class="text-2xl font-bold text-violet-50"
                            >Clear</Link
                        > -->
                    </button>
                    <button
                        type="submit"
                        class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11"
                    >
                        <span class="text-2xl font-bold text-yellow-50"
                            >Generate</span
                        >
                    </button>
                </div>
            </form>
        </div>
    </ContentLayout>
</template>
