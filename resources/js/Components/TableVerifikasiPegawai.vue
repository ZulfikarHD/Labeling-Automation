<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import axios from 'axios';
import { ChevronLeft, ChevronRight, Loader } from 'lucide-vue-next';

const props = defineProps({
    team: Number,
    date: String,
});

const verifPegawai = ref();
const dateFilter = ref(props.date);
const currentPage = ref(1);
const itemsPerPage = 10;
const isLoading = ref(false);

watch(
    () => props.date,
    () => {
        dateFilter.value = props.date;
        produksiPegawai();
    },
);

const produksiPegawai = async () => {
    try {
        isLoading.value = true;
        const res = await axios.get(`/api/pendapatan-harian?date=${dateFilter.value}&team=${props.team}`);
        verifPegawai.value = res.data;
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        isLoading.value = false;
    }
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

produksiPegawai();
</script>

<template>
    <div class="w-full max-w-5xl mx-auto p-4 sm:p-6 bg-white rounded-2xl shadow-lg dark:bg-gray-800/95 backdrop-blur-sm">
        <div class="relative overflow-x-auto rounded-xl">
            <div v-if="isLoading" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm flex items-center justify-center z-10">
                <Loader class="w-8 h-8 text-blue-600 animate-spin" />
            </div>

            <table class="w-full">
                <thead>
                    <tr class="bg-gray-200">
                        <th scope="col" rowspan="2"
                            class="sticky top-0 px-4 sm:px-6 py-4 bg-gray-50/90 dark:bg-gray-700/90 backdrop-blur-sm text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            No
                        </th>
                        <th scope="col" rowspan="2"
                            class="sticky top-0 px-4 sm:px-6 py-4 bg-gray-50/90 dark:bg-gray-700/90 backdrop-blur-sm text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            NP
                        </th>
                        <th scope="col" colspan="3"
                            class="sticky top-0 px-4 sm:px-6 py-4 bg-gray-50/90 dark:bg-gray-700/90 backdrop-blur-sm text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            Jumlah Verifikasi
                        </th>
                        <th scope="col" rowspan="2"
                            class="sticky top-0 px-4 sm:px-6 py-4 bg-gray-50/90 dark:bg-gray-700/90 backdrop-blur-sm text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            Target Harian
                        </th>
                    </tr>
                    <tr class="bg-gray-200">
                        <th scope="col"
                            class="sticky top-12 px-4 sm:px-6 py-3 bg-gray-50/90 dark:bg-gray-700/90 backdrop-blur-sm text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            Lembar
                        </th>
                        <th scope="col"
                            class="sticky top-12 px-4 sm:px-6 py-3 bg-gray-50/90 dark:bg-gray-700/90 backdrop-blur-sm text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            RIM
                        </th>
                        <th scope="col"
                            class="sticky top-12 px-4 sm:px-6 py-3 bg-gray-50/90 dark:bg-gray-700/90 backdrop-blur-sm text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b-2 border-gray-200 dark:border-gray-600">
                            PO
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(produksi, index) in paginatedData" :key="index"
                        class="hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-all duration-200">
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-600 dark:text-gray-400">
                            {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-800 dark:text-gray-200">
                            {{ produksi.pegawai }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ Number(produksi.verifikasi).toLocaleString() }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1 text-xs">Lbr</span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ Math.ceil(Number(produksi.verifikasi) / 500) }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1 text-xs">RIM</span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ produksi.jumlah_po }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1 text-xs">PO</span>
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-right">
                            <div class="inline-flex items-center gap-1">
                                <span class="font-semibold text-blue-600 dark:text-blue-400">17.500</span>
                                <span class="text-gray-400">/</span>
                                <span class="font-semibold text-blue-600 dark:text-blue-400">35</span>
                                <span class="text-gray-500 dark:text-gray-400 text-xs">RIM</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="flex justify-center items-center gap-3 mt-6">
            <button
                @click="prevPage"
                :disabled="currentPage === 1"
                class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
                <ChevronLeft class="w-4 h-4" />
                Sebelumnya
            </button>
            <span class="text-sm text-gray-700 dark:text-gray-300">
                Halaman {{ currentPage }} dari {{ totalPages }}
            </span>
            <button
                @click="nextPage"
                :disabled="currentPage === totalPages"
                class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
                Selanjutnya
                <ChevronRight class="w-4 h-4" />
            </button>
        </div>
    </div>
</template>
