<script setup>
// Import komponen dan library yang dibutuhkan
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowRight } from 'lucide-vue-next';
import LoadingOverlay from '@/Components/LoadingOverlay.vue';
import Select from '@/Components/Select.vue';
import Button from '@/Components/Button.vue';
import StatusProduksiBadge from '@/Components/StatusProduksiBadge.vue';

// Props untuk menerima data dari parent component
const props = defineProps({
    products: Object, // Data produk yang akan ditampilkan
    teamList: Object, // Daftar tim yang tersedia
    crntTeam: Object, // Tim yang sedang aktif/dipilih
});

// Reactive reference untuk menyimpan daftar produk
const listProduct = ref(props.products);

// Form state untuk menyimpan tim yang dipilih
const form = useForm({
    team: props.crntTeam,
});

const isLoading = ref(false);

/**
 * Handler untuk perubahan tim yang dipilih
 * Mengambil data produk berdasarkan tim yang dipilih
 */
const handleTeamChange = async () => {
    try {
        isLoading.value = true;
        const response = await fetch(`/api/order-siap-periksa/fetch-work-po/${form.team}`);
        const data = await response.json();
        listProduct.value = data;
    } catch (error) {
        console.error('Error fetching products:', error);
    } finally {
        isLoading.value = false;
    }
};

/**
 * Handler untuk navigasi ke halaman cetak label
 * @param {Object} product - Data produk yang akan dicetak labelnya
 */
const goToCetakLabel = (product) => {
    const teamId = product.assigned_team;
    const productId = product.id;
    window.location.href = `/order-besar/cetak-label/${teamId}/${productId}`;
};
</script>

<template>
    <Head title="Order Siap Periksa" />
    <AuthenticatedLayout>
        <div class="min-h-screen py-12">
            <div class="container mx-auto px-4 max-w-7xl">
                <!-- Header Section -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Order Siap Periksa
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Pilih tim untuk melihat daftar order yang siap untuk diperiksa
                    </p>
                </div>

                <!-- Team Selection -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                    <InputLabel for="team" value="Pilih Tim" class="mb-2" />
                    <Select id="team" v-model="form.team" @change="handleTeamChange" class="w-full">
                        <option v-for="team in teamList" :key="team.id" :value="team.id">
                            {{ team.workstation }}
                        </option>
                    </Select>
                </div>

                <!-- Loading Overlay -->
                <LoadingOverlay :is-loading="isLoading" />

                <!-- Products List -->
                <div v-if="listProduct && listProduct.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="product in listProduct"
                        :key="product.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer"
                        @click="goToCetakLabel(product)"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                PO: {{ product.no_po }}
                            </h3>
                            <StatusProduksiBadge :status="product.status" />
                        </div>
                        
                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <p><span class="font-medium">OBC:</span> {{ product.no_obc }}</p>
                            <p><span class="font-medium">Type:</span> {{ product.type }}</p>
                            <p><span class="font-medium">Rim:</span> {{ product.start_rim }} - {{ product.end_rim }}</p>
                        </div>

                        <div class="mt-4 flex items-center justify-end">
                            <ArrowRight class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-12 text-center">
                    <p class="text-gray-600 dark:text-gray-400 text-lg">
                        Tidak ada order yang siap untuk diperiksa
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
