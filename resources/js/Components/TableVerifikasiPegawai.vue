<script setup lang="ts">
// Import komponen dan fungsi yang dibutuhkan
import { ref, watch, computed } from "vue"; // Composables dari Vue 3
import axios from "axios"; // HTTP client untuk request API
import { ChevronLeft, ChevronRight } from "lucide-vue-next"; // Icon untuk navigasi pagination
import LoadingOverlay from "./LoadingOverlay.vue"; // Komponen overlay loading
import BaseCard from "./BaseCard.vue"; // Komponen card dasar

// Props yang diterima komponen
const props = defineProps({
    team: Number, // ID tim
    date: String, // Tanggal untuk filter data
});

// State management menggunakan ref
const verifPegawai = ref([]); // Data verifikasi pegawai
const dateFilter = ref(props.date); // Filter tanggal aktif
const currentPage = ref(1); // Halaman pagination saat ini
const itemsPerPage = 10; // Jumlah item per halaman
const isLoading = ref(false); // State loading
const teamName = ref(''); // Nama tim

/**
 * Mengambil nama tim dari API
 * Jika team = 0, menampilkan "Keseluruhan"
 * Jika tidak, mengambil nama workstation dari API
 */
const fetchTeamName = async () => {
    try {
        const response = props.team === 0
            ? "Keseluruhan"
            : (await axios.get(`/api/team-name/${props.team}`)).data.workstation;
        teamName.value = response;
    } catch (error) {
        console.error("Error fetching team name:", error);
    }
};

// Computed properties untuk judul dan subtitle card
const cardTitle = computed(() => `Data Verifikasi Tim ${teamName.value}`);
const cardSubtitle = computed(() => {
    if (!dateFilter.value) return '';
    return new Date(dateFilter.value).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    }).replace(/\s/g, '-');
});

/**
 * Mengambil data produksi pegawai dari API
 * Data difilter berdasarkan tanggal dan tim
 */
const produksiPegawai = async () => {
    try {
        isLoading.value = true;
        const res = await axios.get(`/api/pendapatan-harian?date=${dateFilter.value}&team=${props.team}`);
        verifPegawai.value = res.data;
    } catch (error) {
        console.error("Error fetching data:", error);
    } finally {
        isLoading.value = false;
    }
};

// Watch untuk memantau perubahan tanggal
watch(() => props.date, () => {
    dateFilter.value = props.date;
    fetchTeamName();
    produksiPegawai();
});

// Computed properties untuk pagination
const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return verifPegawai.value.slice(start, start + itemsPerPage) || [];
});

const totalPages = computed(() =>
    Math.ceil(verifPegawai.value.length / itemsPerPage) || 0
);

// Fungsi navigasi pagination
const nextPage = () => currentPage.value < totalPages.value && currentPage.value++;
const prevPage = () => currentPage.value > 1 && currentPage.value--;

// Inisialisasi data saat komponen dimuat
fetchTeamName();
produksiPegawai();
</script>

<template>
    <BaseCard :title="cardTitle" no-padding>
        <!-- Tambahkan label tanggal yang lebih jelas -->
        <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700">
            <span class="text-sm text-gray-600 dark:text-gray-400">Data Periode : </span>
            <span class="font-medium text-blue-600 dark:text-blue-400">{{ cardSubtitle }}</span>
        </div>

        <div class="relative overflow-x-auto rounded -mx-6">
            <LoadingOverlay :is-loading="isLoading" />
            <table class="w-full">
                <!-- Header dengan kontras yang lebih baik -->
                <thead>
                    <tr>
                        <th scope="col" rowspan="2" class="sticky top-0 px-6 py-4 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm text-left text-xs font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider border-b-2 border-blue-300 dark:border-blue-700">No</th>
                        <th scope="col" rowspan="2" class="sticky top-0 px-6 py-4 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm text-left text-xs font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider border-b-2 border-blue-300 dark:border-blue-700">NP</th>
                        <th scope="col" colspan="3" class="sticky top-0 px-6 py-4 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm text-center text-xs font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider border-b-2 border-blue-300 dark:border-blue-700">Jumlah Verifikasi</th>
                        <th scope="col" rowspan="2" class="sticky top-0 px-6 py-4 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm text-center text-xs font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider border-b-2 border-blue-300 dark:border-blue-700">Target Harian</th>
                    </tr>
                    <tr>
                        <th scope="col" class="sticky top-0 px-6 py-4 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm text-left text-xs font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider border-b-2 border-blue-300 dark:border-blue-700">Lembar</th>
                        <th scope="col" class="sticky top-0 px-6 py-4 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm text-left text-xs font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider border-b-2 border-blue-300 dark:border-blue-700">RIM</th>
                        <th scope="col" class="sticky top-0 px-6 py-4 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm text-left text-xs font-bold text-blue-800 dark:text-blue-200 uppercase tracking-wider border-b-2 border-blue-300 dark:border-blue-700">PO</th>
                    </tr>
                </thead>
                <!-- Body dengan spacing yang lebih baik -->
                <tbody class="bg-white/50 dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(produksi, index) in paginatedData"
                        :key="index"
                        class="hover:bg-sky-50 dark:hover:bg-sky-900/20 transition-all duration-200 even:bg-gray-50/50 dark:even:bg-gray-800/30">
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-center font-medium text-gray-600 dark:text-gray-400">
                            {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                        </td>
                        <!-- NP dengan indikator clickable -->
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-center group">
                            <a href="#" class="inline-flex items-center gap-1 font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                {{ produksi.pegawai }}
                                <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </td>
                        <!-- Nilai dengan spacing yang lebih baik -->
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-cyan-600 dark:text-cyan-400 mr-2">{{ Number(produksi.verifikasi).toLocaleString() }}</span>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Lbr</span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ Math.ceil(Number(produksi.verifikasi) / 500) }}</span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1 text-xs">RIM</span>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ produksi.jumlah_po }}</span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1 text-xs">PO</span>
                        </td>
                        <!-- Target Harian yang lebih jelas -->
                        <td class="px-6 py-5 whitespace-nowrap text-sm">
                            <div class="flex flex-col items-end gap-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Target:</div>
                                <div class="flex items-center gap-2">
                                    <div>
                                        <span class="font-semibold text-fuchsia-600 dark:text-fuchsia-400">17.500</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">Lbr</span>
                                    </div>
                                    /
                                    <div>
                                        <span class="font-semibold text-fuchsia-600 dark:text-fuchsia-400">35</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">RIM</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination yang lebih cerdas -->
        <div v-if="totalPages > 1" class="flex justify-center items-center gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
            <button @click="prevPage" :disabled="currentPage === 1" class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800/40 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <ChevronLeft class="w-4 h-4" /> Sebelumnya
            </button>

            <div class="px-4 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800">
                <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Halaman {{ currentPage }} dari {{ totalPages }}</span>
            </div>

            <button @click="nextPage" :disabled="currentPage === totalPages" class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800/40 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                Selanjutnya <ChevronRight class="w-4 h-4" />
            </button>
        </div>
    </BaseCard>
</template>
