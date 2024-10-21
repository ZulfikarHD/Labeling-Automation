<script setup>
import { reactive, ref } from "vue";
import Modal from "@/Components/Modal.vue";
import ContentLayout from "@/Layouts/ContentLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import axios from "axios";
import { batchFullPageLabel } from "@/Components/PrintPages/PrintLabel";
import NavigateBackButton from "@/Components/NavigateBackButton.vue";

const props = defineProps({
    listTeam: Object,
    currentTeam: Number,
});

// Define a reactive form object to store form data
const form = useForm({
    team: props.currentTeam,
    no_po: "", // Production Order number
    obc: "", // Order Bea Cukai number
    jml_lembar: "", // Number of sheets/rims
    jml_label: "", // Number of labels
    seri: "", // Series number
    periksa1: "", // NP Inspector
    periksa2: "",
    jml_rim: "",
});

const obc_color = form.seri == 3 ? "#b91c1c" : "#1d4ed8"; // Warna berdasarkan seri

// Function to fetch data based on the Production Order number
const fetchData = () => {
    axios.get("/api/order-kecil/fetch-spec/" + form.no_po).then((res) => {
        let total_label = Math.ceil(res.data.rencet / 500);

        form.obc = res.data.no_obc;
        form.jml_rim = res.data.rencet + " / " + total_label + " Rim";
        form.jml_lembar = res.data.rencet;
        form.jml_label = total_label;
    });
};

const showModal = ref(false);

const printFrame = ref(null);

const printWithoutDialog = (content) => {
    const iframe = printFrame.value;
    const doc = iframe.contentWindow.document;
    doc.open();
    // Add styles to hide header and footer and limit to the first page
    doc.write(`<style>
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
                ${content}`);
    doc.close();
    iframe.contentWindow.focus();

    // Use a timeout to allow the content to load before printing
    setTimeout(() => {
        iframe.contentWindow.print();
    }, 1000); // Adjust the timeout as necessary
};

// Fungsi untuk mengirim formulir utama
const submit = () => {
            let printLabel = batchFullPageLabel(
                form.obc,
                undefined,
                obc_color,
                undefined,
                form.periksa1,
                form.periksa2,
                form.jml_label
            );
            printWithoutDialog(printLabel);
    // router.post("/api/register-production-order", form, {
    //     onSuccess: () => {
    //         let printLabel = batchFullPageLabel(
    //             form.obc,
    //             undefined,
    //             obc_color,
    //             undefined,
    //             form.periksa1,
    //             form.periksa2,
    //             form.jml_label
    //         );
    //         printWithoutDialog(printLabel);

    //         form.reset();

    //         showModal.value = !showModal.value;
    //     },
    // });
};
</script>
<template>
    <ContentLayout>
        <!-- Modal -->
        <Modal :show="showModal" @close="showModal = !showModal">
            <div class="px-8 py-4 bg-white rounded-lg shadow drop-shadow shadow-slate-300/25">
                <h1 class="text-2xl font-bold text-center text-green-600 brightness-110">
                    Label Berhasil Di Buat
                </h1>
            </div>
        </Modal>

        <!-- Form -->
        <div class="py-12">
            <form @submit.prevent="submit">
                <div class="flex flex-col justify-center gap-6 mx-auto mt-14 w-fit">
                    <div class="mx-auto w-full">
                        <InputLabel
                            for="team"
                            value="Team"
                            class="text-2xl font-extrabold text-center"
                        />

                        <select
                            id="team"
                            ref="team"
                            v-model="form.team"
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block px-10 py-2 mt-2 text-lg w-full drop-shadow flex-grow"
                        >
                            <option
                                v-for="team in props.listTeam"
                                :key="team.id"
                                :value="team.id"
                            >
                                {{ team.workstation }}
                            </option>
                        </select>
                    </div>
                    <!-- Nomor no_po -->
                    <div>
                        <InputLabel
                            for="no_po"
                            value="Nomor Production Order"
                            class="text-4xl font-extrabold text-center"
                        />

                        <TextInput
                            id="no_po"
                            ref="no_po"
                            v-model="form.no_po"
                            @input="fetchData"
                            type="number"
                            class="block w-full px-8 py-2 mt-2 text-2xl text-center bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-400"
                            autocomplete="no_po"
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
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-400"
                                autocomplete="obc"
                                placeholder="Order Bea Cukai"
                                required
                            />
                        </div>

                        <!-- Jumlah Lembar/Rim -->
                        <div>
                            <InputLabel
                                for="jml_rim"
                                value="Lembar / Rim"
                                class="text-4xl font-extrabold text-center"
                            />

                            <TextInput
                                id="jml_rim"
                                ref="jml_rim"
                                v-model="form.jml_rim"
                                type="text"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-400"
                                autocomplete="jml_rim"
                                placeholder="Lembar/Rim"
                                min="1"
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
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-400"
                                autocomplete="jml_label"
                                placeholder="Label"
                                min="1"
                                required
                            />
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <!-- periksa 1 -->
                        <div class="flex-auto">
                            <InputLabel
                                for="periksa1"
                                value="Periksa 1"
                                class="text-4xl font-extrabold text-center"
                            />

                            <TextInput
                                id="periksa1"
                                ref="periksa1"
                                v-model="form.periksa1"
                                type="text"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-400"
                                autocomplete="periksa1"
                                placeholder="NP"
                                required
                            />
                        </div>

                        <!-- Periksa 2-->
                        <div class="flex-auto">
                            <InputLabel
                                for="periksa2"
                                value="Periksa 2"
                                class="text-4xl font-extrabold text-center"
                            />

                            <TextInput
                                id="periksa2"
                                ref="periksa2"
                                v-model="form.periksa2"
                                type="text"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center bg-white border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-400"
                                autocomplete="periksa2"
                                placeholder="NP"
                            />
                        </div>
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
                        <span class="text-2xl font-bold text-yellow-50">Generate</span>
                    </button>
                </div>
            </form>
        </div>
    </ContentLayout>
    <iframe ref="printFrame" style="display: none"></iframe>
</template>
