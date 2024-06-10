<script setup>
import { reactive } from "vue";
import Modal from "@/Components/Modal.vue";
import ContentLayout from "@/Layouts/ContentLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import axios from "axios";
import NavigateBackButton from "@/Components/NavigateBackButton.vue";

const form = reactive({
    po: "",
    obc: "",
    jml_lembar: "",
    jml_label: "",
    seri: "",
    npPeriksa: "",
});

const fetchData = () => {
    axios.get('/api/gen-perso-label/' + form.po)
        .then(res => {
            let total_label = Math.ceil(res.data.rencet / 500)

            form.obc = res.data.no_obc
            form.jml_lembar = res.data.rencet + ' / ' + total_label + ' Rim'
            form.jml_label = total_label
        })
}

const printLabel = () => {
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
    let np = form.npPeriksa;
    let noRim = form.no_rim !== 999 ? form.no_rim : "INS";
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
                                        <div style="margin-top:19.5vh; margin-left:18vh">
                                            <span style="font-weight:600; text-align:center;">${tgl}</span>
                                            <h1 style="font-size: 24px; line-height: 32px; margin-left:25px; font-weight:600; text-align:center; display:inline-block; padding-top:6px; color:${obc_color}">${obc}</h1>
                                        </div>
                                        <div style="margin-top:16.2px; margin-left:16vh">
                                            <h1 style="font-size: 20px; line-height: 32px; margin-left:155px; margin-right:auto; ;font-weight:600;text-align:center;display:inline-block;text-transform: uppercase;">${np}</h1>
                                        </div>
                                        <div style="margin-top:47.5px; margin-left:13vh">
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
    // router.post("/api/non-perekat/non-personal/print-label", form, {
    //     onFinish: () => {
    //         if (props.noRim !== 0) {
    //             router.get("/non-perekat/non-personal/print-label/" + form.team + "/" + form.id);
    //         }
    //         else {
    //             router.get("/non-perekat/non-personal/verif");
    //         }
    //     },
    // });
    form.npPeriksa = null;
}

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
            <form @submit.prevent="printLabel">
                <div class="flex flex-col justify-center gap-6 mx-auto mt-20 w-fit">
                    <!-- Nomor PO -->
                    <div>
                        <InputLabel for="po" value="Nomor PO" class="text-4xl font-extrabold text-center" />

                        <TextInput id="po" ref="po" v-model="form.po" @input="fetchData" type="number"
                            class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="po"
                            placeholder="Production Order" required autofocus />
                    </div>
                    <div class="flex justify-between gap-6 mb-10 w-fit">
                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel for="obc" value="Nomor OBC" class="text-4xl font-extrabold text-center" />

                            <TextInput id="obc" ref="obc" v-model="form.obc" type="text"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="obc"
                                placeholder="Order Bea Cukai" required />
                        </div>

                        <!-- Jumlah Lembar/Rim -->
                        <div>
                            <InputLabel for="jml_lembar" value="Lembar / Rim"
                                class="text-4xl font-extrabold text-center" />

                            <TextInput id="jml_lembar" ref="jml_lembar" v-model="form.jml_lembar" type="text"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center bg-slate-200/70"
                                autocomplete="jml_lembar" placeholder="Lembar/Rim" min="1" disabled />
                        </div>

                        <!-- Jumlah Label -->
                        <div>
                            <InputLabel for="jml_label" value="Jumlah Label"
                                class="text-4xl font-extrabold text-center" />

                            <TextInput id="jml_label" ref="jml_label" v-model="form.jml_label" type="number"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="jml_label"
                                placeholder="Label" min="1" required />
                        </div>
                    </div>

                    <!-- Nomor Rim -->
                    <!-- <h4 class="text-4xl font-semibold text-center">
                        Nomor RIM
                    </h4> -->

                    <!-- NP Pemeriksa -->
                    <div>
                        <InputLabel for="npPeriksa" value="NP Pemeriksa" class="text-4xl font-extrabold text-center" />

                        <TextInput id="npPeriksa" ref="npPeriksa" v-model="form.npPeriksa" type="text"
                            class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="jml_rim"
                            placeholder="NP" required />
                    </div>
                </div>
                <div class="flex justify-center gap-6 mx-auto w-fit">
                    <button
                        class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-11">
                        <!-- <Link
                            :href="route('p.products.create')"
                            class="text-2xl font-bold text-violet-50"
                            >Clear</Link
                        > -->
                    </button>
                    <button type="submit"
                        class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11">
                        <span class="text-2xl font-bold text-yellow-50">Generate</span>
                    </button>
                </div>
            </form>
        </div>
        <div class="flex justify-center w-full">
            <NavigateBackButton :link="route('nonPer.index')" />
        </div>
    </ContentLayout>
</template>
