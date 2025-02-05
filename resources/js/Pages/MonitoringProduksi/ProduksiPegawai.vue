<script setup lang="ts">
// Import komponen-komponen yang dibutuhkan
import InputLabel from '@/Components/InputLabel.vue' // Komponen untuk label input form
import TableVerifikasiPegawai from '@/Components/TableVerifikasiPegawai.vue' // Komponen tabel untuk menampilkan data verifikasi pegawai
import TextInput from '@/Components/TextInput.vue' // Komponen input form
import LoadingOverlay from '@/Components/LoadingOverlay.vue' // Komponen overlay untuk menampilkan status loading
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue' // Layout dasar untuk halaman yang membutuhkan autentikasi
import { Head, useForm } from '@inertiajs/vue3' // Utilitas dari Inertia.js untuk manajemen form dan head
import { ref, watch, onMounted, computed } from 'vue' // Composables dari Vue 3
import { Calendar } from 'lucide-vue-next' // Icon kalender dari library Lucide
import axios from 'axios' // HTTP client untuk request API

// Props yang diterima komponen
const props = defineProps({
    teams: Object // Data tim dalam bentuk object
})

// Setup state dan form
const getDate = new Date()
const form = useForm({
    date: getDate.toISOString().substr(0, 10) // Inisialisasi form dengan tanggal hari ini dalam format YYYY-MM-DD
})
const today = ref(form.date) // Ref untuk menyimpan tanggal hari ini
const activeTeams = ref([]) // Ref untuk menyimpan daftar tim yang aktif
const isLoading = ref(true) // Ref untuk status loading

/**
 * Fungsi untuk mengambil data tim yang aktif dari API
 * Dipanggil saat komponen dimount dan saat tanggal berubah
 */
const fetchActiveTeams = async () => {
    try {
        isLoading.value = true
        const response = await axios.get(`/api/active-teams?date=${form.date}`)
        activeTeams.value = response.data
    } catch (error) {
        console.error('Error fetching active teams:', error)
    } finally {
        isLoading.value = false
    }
}

// Watch untuk memantau perubahan tanggal
watch(() => form.date, () => {
    fetchActiveTeams() // Reload data tim aktif ketika tanggal berubah
})

// Lifecycle hook untuk inisialisasi data
onMounted(() => {
    fetchActiveTeams() // Load data tim aktif saat komponen dimount
})

// Computed property untuk memfilter tim yang aktif
const filteredTeams = computed(() => {
    return props.teams.filter(team => activeTeams.value.includes(team.id)) // Mengembalikan hanya tim yang aktif pada tanggal terpilih
})
</script>

<template>
    <Head title="Produksi Pegawai" />
    <AuthenticatedLayout>
        <!-- Container utama dengan gradient background -->
        <div class="relative min-h-screen py-12 bg-gradient-to-br from-blue-50 via-cyan-50 to-sky-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
            <div class="max-w-[95%] mx-auto">
                <!-- Header Section -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-blue-900 dark:text-blue-100 tracking-tight sm:text-5xl">
                        Monitoring Produksi Harian
                    </h1>
                </div>

                <!-- Filter tanggal dengan icon kalender -->
                <div class="max-w-md mx-auto mb-12">
                    <div class="relative">
                        <InputLabel
                            for="dateFilter"
                            class="block text-sm font-medium text-blue-700 dark:text-blue-300 mb-2"
                        >
                            Tanggal Produksi
                        </InputLabel>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-blue-400 dark:text-blue-500" />
                            <TextInput
                                id="dateFilter"
                                type="date"
                                v-model="form.date"
                                class="pl-12 w-full rounded-xl border-blue-200 dark:border-blue-700 dark:bg-slate-800 dark:text-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Content container dengan loading state -->
                <div class="relative">
                    <LoadingOverlay :is-loading="isLoading" />

                    <!-- Tampilan saat tidak ada data -->
                    <div v-if="!isLoading && activeTeams.length === 0" class="text-center py-12">
                        <p class="text-lg text-gray-600 dark:text-gray-400">
                            Tidak ada data produksi untuk tanggal ini
                        </p>
                    </div>

                    <!-- Grid untuk menampilkan data tim -->
                    <template v-else-if="!isLoading">
                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-12">
                            <template v-for="team in filteredTeams" :key="'team'+team.id">
                                <TableVerifikasiPegawai :team="team.id" :date="form.date" />
                            </template>
                        </div>

                        <!-- Tabel untuk semua tim (agregat) -->
                        <TableVerifikasiPegawai :team="0" :date="form.date" />
                    </template>
                </div>
            </div>

            <!-- Background dekoratif dengan grid pattern dan gradient blur -->
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                <div class="absolute left-0 right-0 top-0 -z-10 m-auto h-[310px] w-[310px] rounded-full bg-cyan-400 dark:bg-cyan-600 opacity-20 blur-[100px]"></div>
                <div class="absolute right-0 top-0 -z-10 h-[310px] w-[310px] rounded-full bg-blue-400 dark:bg-blue-600 opacity-20 blur-[100px]"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
