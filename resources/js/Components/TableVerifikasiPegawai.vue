<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    team: Number,
    date: String,
});


const verifPegawai = ref();
const dateFilter = ref(props.date);
watch(
    () => props.date,
    () => { dateFilter.value = props.date, produksiPegawai() },
);
// watch(props.date,(newVal,oldVal) => {

// })

const produksiPegawai = () => {
    axios.get('/api/pendapatan-harian?date=' + dateFilter.value + '&team=' + props.team).then((res) => {
        verifPegawai.value = res.data;
    });
}

produksiPegawai()
</script>
<template>
    <div
        class="h-fit max-w-4xl w-fit px-4 py-4 mx-auto bg-slate-50 md:py-6 shadow-sm drop-shadow-sm shadow-slate-900/25 rounded-xl dark:bg-slate-800 dark:bg-opacity-60 dark:backdrop-blur-sm dark:backdrop-filter">
        <div>
            <div class="-mx-4 -mt-6 overflow-hidden rounded-t-xl">
                <table class="min-w-full table-auto">
                    <thead
                        class="pb-4 font-bold border-b-2 border-slate-300 text-slate-700 dark:border-slate-500 dark:text-slate-400 bg-slate-200">
                        <tr>
                            <th scope="col" rowspan="2"
                                class="py-2 pb-1.5 px-6 leading-tight text-left border-slate-300 dark:border-slate-500 border-r">
                                No
                            </th>
                            <th scope="col" rowspan="2"
                                class="py-2 pb-1.5 px-6 leading-tight text-left border-slate-300 dark:border-slate-500 border-r">
                                NP
                            </th>
                            <th scope="col" colspan="2"
                                class="py-2 pb-1.5 px-6 leading-tight text-center border-slate-300 dark:border-slate-500 border-r border-b">
                                Jml Verif
                            </th>
                            <th scope="col" rowspan="2"
                                class="py-2 pb-1.5 px-6 leading-tight text-center border-slate-300 dark:border-slate-500 border-r">
                                Target Harian
                            </th>
                        </tr>
                        <tr>
                            <th scope="col"
                                class="py-2 pb-1.5 px-6 leading-tight text-center border-slate-300 dark:border-slate-500 border-r">
                                (LBR)
                            </th>
                            <th scope="col"
                                class="py-2 pb-1.5 px-6 leading-tight text-center border-slate-300 dark:border-slate-500 border-r">
                                (RIM)
                            </th>
                        </tr>
                    </thead>
                    <td></td>
                    <tbody>
                        <tr v-for="(produksi, index) in verifPegawai" :key="index"
                            class="font-mono transition duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100">
                            <td
                                class="text-center leading-5 whitespace-nowrap px-4 py-1.5 font-medium text-slate-950 border-r">
                                <!-- <span v-if="index+1 == 1" class="text-green-600 font-semibold">
                                    {{ index + 1 }}
                                </span>
                                <span v-else-if="index+1 == 2" class="text-green-600 font-semibold">
                                    {{ index + 1 }}
                                </span>
                                <span v-else-if="index+1 == 3" class="text-green-600 font-semibold">
                                    {{ index + 1 }}
                                </span>
                                <span v-else class="text-slate-600 font-medium">
                                    {{ index + 1 }}
                                </span> -->
                                <span class="text-slate-700 font-medium">
                                    {{ index + 1 }}
                                </span>
                            </td>
                            <td
                                class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-800 border-r">
                                <p class="font-medium">
                                    {{ produksi.pegawai }}
                                </p>
                            </td>
                            <td
                                class="leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-green-700 border-r text-end">
                                <p class="font-semibold">
                                    {{
                            Number(
                                produksi.verifikasi
                            ).toLocaleString()
                        }}
                                    <span class="text-slate-600">Lbr</span>
                                </p>
                            </td>
                            <td
                                class="leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-emerald-700 border-r text-end">
                                <p class="font-semibold">{{ Math.ceil(Number(produksi.verifikasi) / 500) }}
                                    <span class="text-slate-600">RIM</span>
                                </p>
                            </td>
                            <td
                                class="leading-5 whitespace-nowrap text-sm px-4 py-1.5 font-semibold text-blue-600 border-r text-end">
                                17.500 <span class="text-slate-600">/</span> 35 <span class="text-slate-600">RIM</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
