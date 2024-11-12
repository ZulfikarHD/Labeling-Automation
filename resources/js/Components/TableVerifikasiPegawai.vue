<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    team: Number,
    date: String,
});

const verifPegawai = ref();
const dateFilter = ref(props.date);
const currentPage = ref(1);
const itemsPerPage = 10;

watch(
    () => props.date,
    () => { dateFilter.value = props.date, produksiPegawai() },
);

const produksiPegawai = () => {
    axios.get('/api/pendapatan-harian?date=' + dateFilter.value + '&team=' + props.team).then((res) => {
        verifPegawai.value = res.data;
    });
}

const paginatedData = computed(() => {
    if (!verifPegawai.value) return [];
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return verifPegawai.value.slice(start, end);
});

const totalPages = computed(() => {
    if (!verifPegawai.value) return 0;
    return Math.ceil(verifPegawai.value.length / itemsPerPage);
});

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

produksiPegawai()
</script>

<template>
    <div class="max-w-5xl mx-auto p-6 bg-white rounded-xl shadow-lg dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th scope="col" rowspan="2"
                            class="sticky top-0 px-6 py-4 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            No
                        </th>
                        <th scope="col" rowspan="2"
                            class="sticky top-0 px-6 py-4 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            NP
                        </th>
                        <th scope="col" colspan="3"
                            class="sticky top-0 px-6 py-4 bg-gray-100 dark:bg-gray-700 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            Jml Verif
                        </th>
                        <th scope="col" rowspan="2"
                            class="sticky top-0 px-6 py-4 bg-gray-100 dark:bg-gray-700 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            Target Harian
                        </th>
                    </tr>
                    <tr>
                        <th scope="col"
                            class="sticky top-12 px-6 py-3 bg-gray-100 dark:bg-gray-700 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            (LBR)
                        </th>
                        <th scope="col"
                            class="sticky top-12 px-6 py-3 bg-gray-100 dark:bg-gray-700 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            (RIM)
                        </th>
                        <th scope="col"
                            class="sticky top-12 px-6 py-3 bg-gray-100 dark:bg-gray-700 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            (PO)
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(produksi, index) in paginatedData" :key="index"
                        class="hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer transition-all duration-200 ease-in-out">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-600 dark:text-gray-400">
                            {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-800 dark:text-gray-200">
                            {{ produksi.pegawai }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ Number(produksi.verifikasi).toLocaleString() }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1">Lbr</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ Math.ceil(Number(produksi.verifikasi) / 500) }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1">RIM</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ produksi.jumlah_po }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1">PO</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-blue-600 dark:text-blue-400">17.500</span>
                            <span class="text-gray-500 dark:text-gray-400">/</span>
                            <span class="font-semibold text-blue-600 dark:text-blue-400">35</span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1">RIM</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="flex justify-center items-center gap-4 mt-4">
            <button
                @click="prevPage"
                :disabled="currentPage === 1"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Previous
            </button>
            <span class="text-sm text-gray-700">
                Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button
                @click="nextPage"
                :disabled="currentPage === totalPages"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Next
            </button>
        </div>
    </div>
</template>
