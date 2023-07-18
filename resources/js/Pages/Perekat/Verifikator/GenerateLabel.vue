<script setup>
import { reactive } from 'vue';
import Modal from '@/Components/Modal.vue'
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    },
});

// Form
const form = reactive({
    po  : '',
    obc : '',
    gol : '',
    vol : '',
    produk  : 'MMEA',
    periksa1: '',
    periksa2: '',
    kemasan : '',
    lbr_kemas : '',
});

const getData = () => {
    axios.post(route('p.genLabels.callSpec'),form)       // -> post form ke routing, untuk munculkan request data
                    .then(res => {
                        form.obc    = res.data.no_obc;
                        form.gol    = res.data.gol;
                        form.vol    = res.data.volume;
                        form.kemasan = Math.ceil(res.data.rencet / 300);

                    });
};

const print = () => {
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

    let tgl = date.getDay() + "-" + months[date.getMonth()] + "-" + date.getFullYear();
    let po  = form.po;
    let obc = form.obc;
    let gol = form.gol;
    let vol = form.vol;
    let periksa1  = form.periksa1;
    let periksa2  = form.periksa2;
    let produk  = form.produk;
    let kemasan = form.kemasan;
    let time = '';
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

    let WinPrint = window.open('', '', 'left=0,top=30,width=800,height=900,toolbar=0,scrollbars=0,status=0');
    for(let i = 0; i < kemasan; i++){
        printLabel += `<!DOCTYPE html>
                            <html>
                                <head>
                                    ${stylesHtml}
                                </head>
                                <body>
                                    <div class="w-full h-full top-3/4" style='page-break-after:always'>
                                        <div class="flex gap-8 mt-[525px] items-center">
                                            <span class="ml-2 -mt-2 font-semibold text-center">${tgl}</span>
                                            <h1 class="inline-block ml-2 -mt-2 font-semibold text-center">${po}</h1>
                                            <h1 class="inline-block -mt-1 text-xl font-semibold text-center">${gol}${vol}</h1>
                                        </div>
                                        <div class="flex gap-8 mt-11">
                                            <h1 class="inline-block ml-32 text-lg font-semibold text-center">${obc}</h1>
                                            <h1 class="inline-block text-xl font-semibold text-center">${gol}${vol}</h1>
                                        </div>
                                        <div class="mt-[121px] flex justify-center px-24 gap-8">
                                            <h1 class="inline-block mx-auto text-2xl font-medium text-center">${periksa1}</h1>
                                            <h1 class="inline-block mx-auto text-2xl font-medium text-center">${periksa2}</h1>
                                        </div>
                                    </div>
                                </body>
                            </html>`
    }

    WinPrint.document.write(printLabel);

    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print();
    // WinPrint.close();
    router.post(route('p.genLabels.storeMmea'),form)
};
</script>

<template>
    <ContentLayout>
        <div class="container flex flex-col items-center justify-center gap-6 py-10 mx-auto">
            <!-- Nomor PO -->
            <div class="mx-auto mb-7">
                <InputLabel for="po" value="Nomor PO" class="text-2xl font-extrabold text-center" />

                <TextInput id="po" ref="po" v-model="form.po" type="number" autofocus @update:model-value="getData()" required
                    class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="po"/>
            </div>
            <div class="flex justify-center gap-4">

                <!-- Jenis Produk -->
                <div class="mx-auto mb-7">
                    <InputLabel for="produk" value="Produk" class="text-2xl font-extrabold text-center" />

                    <TextInput id="produk" ref="produk" v-model="form.produk" type="text" required
                        class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="produk"/>
                </div>

                <!-- Nomor OBC -->
                <div class="mx-auto mb-7">
                    <InputLabel for="obc" value="Nomor OBC" class="text-2xl font-extrabold text-center" />

                    <TextInput id="obc" ref="obc" v-model="form.obc" type="text" required
                        class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="obc"/>
                </div>

                <!-- Golongan / Volume -->
                <div class="mx-auto mb-7">
                    <InputLabel for="gol" value="Golongan / Volume" class="text-2xl font-extrabold text-center" />

                    <TextInput id="gol" ref="gol" v-model="form.gol" type="text" required
                        class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="gol"/>
                </div>
            </div>
            <div class="flex justify-center gap-4">
                <!-- NP Periksa 1 -->
                <div class="mx-auto mb-7">
                    <InputLabel for="periksa1" value="Periksa 1" class="text-2xl font-extrabold text-center" />

                    <TextInput id="periksa1" ref="periksa1" v-model="form.periksa1" type="text" required
                        class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="periksa1"/>
                </div>

                <!-- NP Periksa 2 -->
                <div class="mx-auto mb-7">
                    <InputLabel for="periksa2" value="Periksa 2" class="text-2xl font-extrabold text-center" />

                    <TextInput id="periksa2" ref="periksa2" v-model="form.periksa2" type="text" required
                        class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="periksa2"/>
                </div>
            </div>

            <!-- Isi Kemasan -->
            <div class="mx-auto mb-7">
                <InputLabel for="kemasan" value="Jumlah Kemasan" class="text-2xl font-extrabold text-center" />

                <TextInput id="kemasan" ref="kemasan" v-model="form.kemasan" type="number" required
                    class="block w-full px-8 py-2 mt-2 text-2xl text-center" autocomplete="kemasan"/>
            </div>

            <!-- Print Label -->
            <div class="flex justify-center gap-4 ">
                <button class="px-8 py-2 text-green-500 underline transition duration-200 ease-in-out bg-white border border-green-500 rounded-md shadow hover:border-green-600 drop-shadow shadow-green-500/25">
                    <span class="text-lg font-bold">Clear</span>
                </button>
                <button @click="print()" class="px-8 py-2 transition duration-200 ease-in-out bg-green-500 rounded-md shadow hover:bg-green-600 text-green-50 drop-shadow shadow-green-500/25">
                    <span class="text-lg font-bold">Print</span>
                </button>
            </div>
        </div>
    </ContentLayout>
    <Modal :show="showModal" @close="Modal => showModal = !showModal">
        <div class="px-8 py-6">
            <form @submit.prevent="submit">
                <div class="pb-4 mb-4 border-b-2 border-slate-400">
                    <h3 class="text-2xl font-semibold text-center text-slate-700">Barang Yang Anda Ambil</h3>
                    <div>
                        <h3 class="text-2xl font-semibold text-center text-slate-700">
                            Berjumlah <span class="text-cyan-600 brightness-110">{{ form.jml_rim }} RIM</span>, Dengan Nomor
                            <span class="text-emerald-600 brightness-110">{{ form.no_rim }}</span>, Sisiran {{ form.lbr_ptg
                            }}
                        </h3>
                    </div>
                </div>
                <div>
                    <InputLabel for="rfid" value="Silahkan Scan RFID mu" class="text-2xl font-semibold text-center" />

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
</template>
