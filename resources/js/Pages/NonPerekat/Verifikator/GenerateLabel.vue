<script setup>
import { reactive } from 'vue';
import Modal from '@/Components/Modal.vue'
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, router } from '@inertiajs/vue3';


let emptyKanan = false;
let emptyKiri  = false;

const props = defineProps({
    product: Object,
    showModal: {
        type: Boolean,
        default: false,
    },
    printUlangModal : {
        type: Boolean,
        default: false,
    }
});

// Form
const form = useForm({
    po: props.product.no_po,
    obc: props.product.no_obc,
    team: props.product.assigned_team,
    seri: '',
    jml_rim: '1',
    lbr_ptg: 'Kiri',
    no_rim: [],
    rfid: '',
});

// Tarik data nomor Rim yang belum di Isi
const rim = () => {
    axios.post(route('np.generateLabels.getRim'),form)       // -> post form ke routing, untuk munculkan request data
         .then(res => {                                 // -> then (kemudian), bikin var buat data yang di tarik contoh(res) terus definisi fungsinya
            form.no_rim = [];                           // -> reset array di const form yang di atas
            res.data.forEach((noRim,index) => {         // -> foreach loop data res tadi dengan format seperti sebelah, dan di dalam foreach (value,index)
                form.no_rim[index] = noRim.no_rim       // -> you know what is this.
            });

            /**
             * Jika Yang Lembar Pilihan Pertama Sudah Habis
             * Langsung di pindah Sisirannya
             */
            if (form.no_rim.length === 0)
            {
                if(form.lbr_ptg == 'Kiri')
                {
                    form.lbr_ptg = 'Kanan';
                    emptyKiri = !emptyKiri;
                }
                else if (form.lbr_ptg == 'Kanan')
                {
                    form.lbr_ptg = 'Kiri';
                    emptyKanan = !emptyKanan;
                }


                axios.post(route('np.generateLabels.getRim'),form)       // -> post form ke routing, untuk munculkan request data
                    .then(res => {                                 // -> then (kemudian), bikin var buat data yang di tarik contoh(res) terus definisi fungsinya
                        form.no_rim = [];                           // -> reset array di const form yang di atas
                        res.data.forEach((noRim,index) => {         // -> foreach loop data res tadi dengan format seperti sebelah, dan di dalam foreach (value,index)
                            form.no_rim[index] = noRim.no_rim       // -> you know what is this.
                        });
                    });
            }
            else
            {
                //
            }
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
                    "Des"
                    ];

    let obc = form.obc;
    let tgl = date.getDay() + "-" + months[date.getMonth()] + "-" + date.getFullYear();
    let np  = form.rfid;
    let noRim = form.no_rim;
    let time = '';
    let sisiran = form.lbr_ptg == 'Kiri' ? '(*)' : '(**)';
    let stylesHtml = '';


    if(date.getHours() >= 5 && date.getHours() < 8 )
    {
        time = 'A'
    }
    else if (date.getHours() >= 8 && date.getHours() < 10  )
    {
        time = 'B'
    }
    else if (date.getHours() >= 10 && date.getHours() < 13 )
    {
        time = 'C'
    }
    else if (date.getHours() >= 13 && date.getHours() < 16 )
    {
        time = 'D'
    }
    else
    {
        time = 'E'
    }

    let printLabel = '';
    for (const node of [...document.querySelectorAll('link[rel="stylesheet"], style')]) {
    stylesHtml += node.outerHTML;
    }

    let WinPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
    noRim.forEach(function(nomorRim){
        printLabel += `<!DOCTYPE html>
                            <html>
                                <head>
                                    ${stylesHtml}
                                </head>
                                <body>
                                    <div style='page-break-after:always; width:100%; height:100%'>
                                        <div style="margin-top:52px ">
                                            <span style="margin-top: -8px; font-weight:600; text-align:center;">${tgl}</span>
                                            <h1 style="font-size: 24px; line-height: 32px; margin-left:40px; font-weight:600; text-align:center; display:inline-block;">${obc}</h1>
                                        </div>
                                        <div style="margin-top:48px">
                                            <h1 style="font-size: 24px; line-height: 32px; margin-left:160px; margin-right:auto; ;font-weight:600;text-align:center;display:inline-block;">${np}</h1>
                                        </div>
                                        <div style="margin-top:90px;">
                                            <h1 style="display: inline-block; margin-left: 160px; margin-right: auto; text-align: center; font-size: 20px; line-height: 28px; font-weight:500;">${nomorRim} ${sisiran} ${time}</h1>
                                        </div>
                                    </div>
                                </body>
                            </html>`
    })

    WinPrint.document.write(printLabel);

    WinPrint.document.close();
    WinPrint.focus();
        WinPrint.print();
        // WinPrint.close();
    router.post(route('np.generateLabels.store'),form)
};
</script>

<template>
    <!-- Modal Konfirmasi Print -->
    <Modal :show="showModal" @close="Modal => showModal = !showModal">
        <div class="px-8 py-6">
            <form @submit.prevent="submit">
                <div class="pb-4 mb-4 border-b-2 border-slate-400">
                    <h3 class="text-2xl font-semibold text-center text-slate-700">Barang Yang Anda Ambil</h3>
                    <div>
                        <h3 class="text-2xl font-semibold text-center text-slate-700">
                            Berjumlah <span class="text-cyan-600 brightness-110">{{ form.jml_rim }} RIM</span>, Dengan Nomor
                            <span class="text-emerald-600 brightness-110">{{ form.no_rim }}</span>, Sisiran {{ form.lbr_ptg }}
                        </h3>
                    </div>
                </div>
                <div>
                    <InputLabel for="rfid" value="Silahkan Scan RFID mu" class="text-2xl font-semibold text-center"/>

                    <TextInput id="rfid" type="text" class="block w-full mt-4 text-center" v-model="form.rfid" required
                        autofocus autocomplete="rfid" />

                    <InputError class="mt-2" />
                </div>
                <div class="flex flex-row justify-center gap-4">
                    <!-- <button type="button" @click="print"
                        class="flex justify-center px-4 py-2 mt-8 border border-blue-400 shadow-md w-fit bg-inherit rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-blue-500/20">
                        <span class="text-lg font-bold text-blue-500">Test</span>
                    </button> -->
                    <button type="button" @click="showModal = !showModal"
                        class="flex justify-center px-4 py-2 mt-8 border border-blue-400 shadow-md w-fit bg-inherit rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-blue-500/20">
                        <span class="text-lg font-bold text-blue-500">Cancel</span>
                    </button>
                    <button type="submit"
                        class="flex justify-center px-4 py-2 mt-8 shadow-md w-fit bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-blue-500/20">
                        <span class="text-lg font-bold text-indigo-50">Print</span>
                    </button>
                </div>
            </form>
        </div>
    </Modal>

    <!-- Modal Prit Ulang -->
    <Modal :show="printUlangModal" @close="Modal => printUlangModal = !printUlangModal">
                <InputLabel for="rfid" value="Silahkan Scan RFID mu" class="text-2xl font-semibold text-center"/>
                <TextInput id="rfid" type="text" class="block w-full mt-4 text-center" v-model="form.rfid" required
                    autofocus autocomplete="rfid" />
                <InputError class="mt-2" />
    </Modal>

    <ContentLayout>
        <div class="py-8">
            <form>
                <div class="flex flex-col justify-center gap-6 mx-auto w-fit">
                    <!-- Team -->
                    <div class="mx-auto mb-7">
                        <InputLabel for="team" value="Team" class="text-2xl font-extrabold text-center" />

                        <TextInput id="team" ref="team" v-model="form.team" type="text"
                            class="block px-4 py-2 mt-2 text-lg text-center shadow w-fit bg-slate-300 drop-shadow"
                            autocomplete="team" disabled />
                    </div>
                    <div class="flex justify-between gap-6 mb-6 w-fit">
                        <!-- PO -->
                        <div>
                            <InputLabel for="po" value="Nomor PO" class="text-2xl font-extrabold text-center" />

                            <TextInput id="po" ref="po" v-model="form.po" type="number"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-300 drop-shadow"
                                autocomplete="po" disabled />
                        </div>

                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel for="obc" value="Nomor OBC" class="text-2xl font-extrabold text-center" />

                            <TextInput id="obc" ref="obc" v-model="form.obc" type="text"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-300 drop-shadow"
                                autocomplete="obc" disabled />
                        </div>

                        <!-- Seri -->
                        <div>
                            <InputLabel for="seri" value="Seri" class="text-2xl font-extrabold text-center " />

                            <TextInput id="seri" ref="seri" v-model="form.seri" type="number"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-300 drop-shadow"
                                autocomplete="seri" value="1" />
                        </div>
                    </div>

                    <div class="flex justify-between gap-6 mx-auto w-fit">
                        <!-- Start RIM -->
                        <div>
                            <InputLabel for="jml_rim" value="Jumlah RIM" class="text-2xl font-extrabold text-center" />

                            <TextInput id="jml_rim" ref="jml_rim" v-model="form.jml_rim" type="number"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center" autocomplete="jml_rim" min="1"
                                max="10" />
                        </div>

                        <!-- End Rim -->
                        <div>
                            <InputLabel for="lbr_ptg" value="Lembar Potong" class="text-2xl font-extrabold text-center" />

                            <TextInput id="lbr_ptg" ref="lbr_ptg" v-model="form.lbr_ptg" type="text"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center" autocomplete="lbr_ptg" disabled />
                        </div>
                    </div>
                </div>

                <!-- Keypad -->
                <div class="grid grid-cols-2 gap-6 mx-auto w-fit">
                    <div class="grid grid-cols-3 gap-3 mt-10">
                        <button type="btn"
                            class="flex justify-center w-full px-2 py-4 mx-auto font-extrabold transition duration-150 ease-in-out shadow bg-gradient-to-r from-sky-300 to-sky-400 shadow-sky-400/30 drop-shadow rounded-xl text-start text-sky-50 hover:brightness-90"
                            v-for="n in 9">{{ n }}</button>
                        <button type="btn"
                            class="flex justify-center w-full col-span-2 px-2 py-4 mx-auto font-extrabold transition duration-150 ease-in-out shadow bg-gradient-to-r from-sky-300 to-sky-400 shadow-sky-400/30 drop-shadow rounded-xl text-start text-sky-50 hover:brightness-90">0</button>
                        <button type="btn"
                            class="flex justify-center w-full px-2 py-4 mx-auto font-extrabold transition duration-150 ease-in-out shadow bg-gradient-to-r from-sky-300 to-sky-400 shadow-sky-400/30 drop-shadow rounded-xl text-start text-sky-50 hover:brightness-90">Backspace</button>
                    </div>
                    <div class="grid grid-cols-2 mt-10">
                        <button type="button"  v-bind:disabled="emptyKiri" @click="form.lbr_ptg = 'Kiri'"
                            class="flex justify-center w-2/3 px-2 py-4 mx-auto font-extrabold transition duration-150 ease-in-out shadow bg-gradient-to-r from-emerald-400 to-emerald-300 shadow-emerald-400/30 rounded-xl text-start text-green-50 hover:brightness-90">
                            <div class="flex flex-col items-center justify-center h-full gap-4 px-6">
                                <div class="flex gap-2">
                                    <span class="p-2 rounded-full bg-emerald-50"></span>
                                </div>
                                <span class="font-extrabold uppercase">Kiri</span>
                            </div>
                        </button>
                        <button type="button" @click="form.lbr_ptg = 'Kanan'" :disabled="emptyKanan"
                            class="flex justify-center w-2/3 px-2 py-4 mx-auto font-extrabold transition duration-150 ease-in-out shadow bg-gradient-to-r from-emerald-300 to-emerald-400 shadow-emerald-400/30 rounded-xl text-start text-green-50 hover:brightness-90">
                            <div class="flex flex-col items-center justify-center h-full gap-4 px-6">
                                <div class="flex gap-2">
                                    <span class="p-2 rounded-full bg-emerald-50"></span>
                                    <span class="p-2 rounded-full bg-emerald-50"></span>
                                </div>
                                <span class="font-extrabold uppercase">Kanan</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-center gap-6 mx-auto w-fit">
                    <button type="button" @click="form.jml_rim = '1'"
                        class="flex justify-center px-4 py-4 mx-auto mt-8 text-lg font-bold shadow-md w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-violet-500/20 text-violet-50">
                        Clear
                    </button>
                    <button type="button" @click="[rim(), showModal = !showModal]"
                        class="flex justify-center px-4 py-4 mx-auto mt-8 shadow-md w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-green-500/20">
                        <div class="text-lg font-bold text-yellow-50">Generate</div>
                    </button>
                    <button type="button" @click="[printUlangModal = !printUlangModal]"
                        class="flex justify-center px-4 py-4 mx-auto mt-8 shadow-md w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-green-500/20">
                        <div class="text-lg font-bold text-yellow-50">Print Ulang</div>
                    </button>
                </div>
            </form>
        </div>
    </ContentLayout>
</template>
