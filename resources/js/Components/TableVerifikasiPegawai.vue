<script setup lang="ts">
import { ref,watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    team : Number,
    date : String,
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
    class="h-fit max-w-4xl w-fit px-4 py-4 mx-auto bg-white md:py-6 drop-shadow-sm rounded-xl dark:bg-slate-800 dark:bg-opacity-60 dark:backdrop-blur-sm dark:backdrop-filter">
        <div>
            <div class="-mx-4 -mt-6 overflow-hidden rounded-t-xl">
                <table class="min-w-full table-auto">
                    <thead
                        class="pb-4 font-bold border-b-2 border-slate-300 text-slate-700 dark:border-slate-500 dark:text-slate-400 bg-slate-200">
                        <tr>
                            <th scope="col"
                                class="pt-6 pb-1.5 px-6 leading-tight text-left border-slate-300 dark:border-slate-500">
                                No
                            </th>
                            <th scope="col"
                                class="pt-6 pb-1.5 px-6 leading-tight text-left border-slate-300 dark:border-slate-500">
                                NP
                            </th>
                            <th scope="col"
                                class="pt-6 pb-1.5 px-6 leading-tight text-center border-slate-300 dark:border-slate-500">
                                Jml Verif
                            </th>
                            <th scope="col"
                                class="pt-6 pb-1.5 px-6 leading-tight text-center border-slate-300 dark:border-slate-500">
                                Target Harian
                            </th>
                        </tr>
                    </thead>
                    <td></td>
                    <tbody>
                        <tr v-for="(produksi, index) in verifPegawai" :key="index"
                            class="font-mono transition duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100">
                            <td
                                class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 font-medium text-slate-950 border-r">
                                <p>
                                    {{ index + 1 }}
                                </p>
                            </td>
                            <td
                                class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 font-medium text-slate-950 border-r">
                                <p>
                                    {{ produksi.pegawai }}
                                </p>
                            </td>
                            <td
                                class="leading-5 whitespace-nowrap text-sm px-4 py-1.5 font-medium text-slate-950 border-r text-end">
                                <p>
                                    {{
                                        Number(
                                            produksi.verifikasi
                                        ).toLocaleString()
                                    }}
                                    Lbr / {{ Number(produksi.verifikasi) / 500 }} RIM
                                </p>
                            </td>
                            <td
                                class="leading-5 whitespace-nowrap text-sm px-4 py-1.5 font-medium text-slate-950 border-r text-end">
                                15,000 / 30 RIM
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
