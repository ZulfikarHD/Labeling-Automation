<script setup lang="ts">
// Import library dan komponen yang dibutuhkan
import { ref, watch, computed } from "vue";
import axios from "axios";
import { ChevronLeft, ChevronRight } from "lucide-vue-next";
import LoadingOverlay from "./LoadingOverlay.vue";
import BaseCard from "./BaseCard.vue";

// Props untuk menerima team ID dan tanggal dari parent component
const props = defineProps({
    team: Number,
    date: String,
});

// State management menggunakan ref
const verifPegawai = ref(); // Menyimpan data verifikasi pegawai
const dateFilter = ref(props.date); // Filter tanggal
const currentPage = ref(1); // Halaman pagination saat ini
const itemsPerPage = 10; // Jumlah item per halaman
const isLoading = ref(false); // State loading
const teamName = ref(''); // Nama tim

// Fungsi untuk mengambil nama tim dari API
const fetchTeamName = async () => {
    try {
        teamName.value = props.team === 0 ? "Keseluruhan" : (await axios.get(`/api/team-name/${props.team}`)).data.workstation;
    } catch (error) {
        console.error("Error fetching team name:", error);
    }
}

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

// Fungsi untuk mengambil data produksi pegawai dari API
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

// Computed property untuk data yang sudah dipaginasi
const paginatedData = computed(() => {
    if (!verifPegawai.value) return [];
    const start = (currentPage.value - 1) * itemsPerPage;
    return verifPegawai.value.slice(start, start + itemsPerPage);
});

// Computed property untuk total halaman
const totalPages = computed(() =>
    !verifPegawai.value ? 0 : Math.ceil(verifPegawai.value.length / itemsPerPage)
);

// Fungsi untuk navigasi pagination
const nextPage = () => currentPage.value < totalPages.value && currentPage.value++;
const prevPage = () => currentPage.value > 1 && currentPage.value--;

// Inisialisasi awal
fetchTeamName();
produksiPegawai();
</script>

<template>
    <!-- Komponen BaseCard sebagai wrapper utama -->
    <BaseCard :title="cardTitle" :subtitle="cardSubtitle" no-padding>
        <template #title></template>
        <div class="relative overflow-x-auto rounded -mx-6">
            <!-- Overlay loading saat data sedang diambil -->
            <LoadingOverlay :is-loading="isLoading" />

            <!-- Tabel untuk menampilkan data verifikasi -->
            <table class="w-full">
                <!-- Header tabel dengan 2 baris -->
                <thead>
                    <tr>
                        <th scope="col" rowspan="2" class="sticky top-0 px-4 sm:px-6 py-4 bg-blue-50/90 dark:bg-blue-900/30 backdrop-blur-sm text-left text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider border-b-2 border-blue-200 dark:border-blue-800">
                            No
                        </th>
                        <th scope="col" rowspan="2" class="sticky top-0 px-4 sm:px-6 py-4 bg-blue-50/90 dark:bg-blue-900/30 backdrop-blur-sm text-left text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider border-b-2 border-blue-200 dark:border-blue-800">
                            NP
                        </th>
                        <th scope="col" colspan="3" class="sticky top-0 px-4 sm:px-6 py-4 bg-blue-50/90 dark:bg-blue-900/30 backdrop-blur-sm text-center text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider border-b-2 border-blue-200 dark:border-blue-800">
                            Jumlah Verifikasi
                        </th>
                        <th scope="col" rowspan="2" class="sticky top-0 px-4 sm:px-6 py-4 bg-blue-50/90 dark:bg-blue-900/30 backdrop-blur-sm text-center text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider border-b-2 border-blue-200 dark:border-blue-800">
                            Target Harian
                        </th>
                    </tr>
                    <tr>
                        <th scope="col" class="sticky top-12 px-4 sm:px-6 py-3 bg-blue-50/90 dark:bg-blue-900/30 backdrop-blur-sm text-center text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider border-b-2 border-blue-200 dark:border-blue-800">
                            Lembar
                        </th>
                        <th scope="col" class="sticky top-12 px-4 sm:px-6 py-3 bg-blue-50/90 dark:bg-blue-900/30 backdrop-blur-sm text-center text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider border-b-2 border-blue-200 dark:border-blue-800">
                            RIM
                        </th>
                        <th scope="col" class="sticky top-12 px-4 sm:px-6 py-3 bg-blue-50/90 dark:bg-blue-900/30 backdrop-blur-sm text-center text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wider border-b-2 border-blue-200 dark:border-blue-800">
                            PO
                        </th>
                    </tr>
                </thead>
                <!-- Body tabel dengan data yang sudah dipaginasi -->
                <tbody class="bg-white/50 dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="(produksi, index) in paginatedData" :key="index" class="hover:bg-sky-50 dark:hover:bg-sky-900/20 transition-all duration-200">
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-gray-600 dark:text-gray-400">
                            {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-blue-600 dark:text-blue-400">
                            {{ produksi.pegawai }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="font-semibold text-cyan-600 dark:text-cyan-400">
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
                                <span class="font-semibold text-fuchsia-600 dark:text-fuchsia-400">17.500</span>
                                <span class="text-gray-400">/</span>
                                <span class="font-semibold text-fuchsia-600 dark:text-fuchsia-400">35</span>
                                <span class="text-gray-500 dark:text-gray-400 text-xs">RIM</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination controls -->
        <div class="flex justify-center items-center gap-3 p-4 border-t border-gray-200 dark:border-gray-700">
            <button @click="prevPage" :disabled="currentPage === 1" class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800/40 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <ChevronLeft class="w-4 h-4" />
                Sebelumnya
            </button>

            <div class="px-4 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800">
                <span class="text-sm font-medium text-blue-700 dark:text-blue-300">
                    Halaman {{ currentPage }} dari {{ totalPages }}
                </span>
            </div>

            <button @click="nextPage" :disabled="currentPage === totalPages" class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800/40 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                Selanjutnya
                <ChevronRight class="w-4 h-4" />
            </button>
        </div>
    </BaseCard>
</template>
