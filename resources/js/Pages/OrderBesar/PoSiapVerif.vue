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
 * Mengambil data produk baru berdasarkan tim yang dipilih
 */
const changeTeam = () => {
    isLoading.value = true;
    axios.get('/api/order-besar/verif/'+form.team)
        .then((res) => {
            listProduct.value = res.data;
        })
        .finally(() => {
            isLoading.value = false;
        });
}

// Custom date formatter tanpa library external
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const monthNames = [
        'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    const month = monthNames[date.getMonth()];
    const year = date.getFullYear();

    return `${day}-${month}-${year}`;
};
</script>

<template>
    <Head title="Siap Periksa" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8 relative">
            <LoadingOverlay :is-loading="isLoading" />

            <!-- Bagian Pemilihan Tim -->
            <div class="max-w-md mx-auto mb-12">
                <InputLabel
                    for="team"
                    value="Pilih Tim"
                    required
                    size="lg"
                    class="text-center"
                />
                <Select
                    id="team"
                    v-model="form.team"
                    @change="changeTeam"
                    size="lg"
                >
                    <option value="" disabled>Pilih Tim Anda</option>
                    <option v-for="teams in props.teamList" :value="teams.id">
                        {{ teams.workstation }}
                    </option>
                </Select>
            </div>

            <!-- Improve empty state -->
            <div v-if="!listProduct.length"
                 class="text-center py-12 text-gray-500 dark:text-gray-400">
                <div class="max-w-md mx-auto">
                    <ClipboardList class="w-16 h-16 mx-auto mb-4 opacity-50" />
                    <h3 class="text-lg font-medium mb-2">Belum Ada Produk</h3>
                    <p class="text-sm">Belum ada produk yang siap diperiksa untuk tim ini</p>
                </div>
            </div>

            <!-- Card Tabel -->
            <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <!-- Header Tabel -->
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Daftar Produk Siap Periksa</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Daftar produk yang siap untuk diperiksa oleh tim</p>
                </div>

                <!-- Tabel Data Produk -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-700 border-b border-slate-200 dark:border-slate-600">
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 dark:text-slate-300 text-left">No. PO</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 dark:text-slate-300 text-left">OBC</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 dark:text-slate-300 text-center">Jenis</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 dark:text-slate-300 text-center">No. Rim</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 dark:text-slate-300 text-center">Status</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 dark:text-slate-300 text-center">Waktu Dibuat</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 dark:text-slate-300 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr v-for="product in listProduct"
                                class="hover:bg-slate-100 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ product.no_po }}</td>
                                <td class="px-6 py-4 text-sm font-medium" :class="{'text-red-600 dark:text-red-400': product.no_obc.substr(4,1) == 3, 'text-blue-600 dark:text-blue-400': product.no_obc.substr(4,1) != 3}">
                                    {{ product.no_obc }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 text-center">{{ product.type }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 text-center">{{ product.start_rim }} - {{ product.end_rim }}</td>
                                <td class="px-6 py-4 text-center">
                                    <StatusProduksiBadge :status="product.status" />
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 text-center">{{ formatDate(product.created_at) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <Link
                                        :href="route('orderBesar.cetakLabel', {team : form.team, id : product.id})"
                                        class="inline-block"
                                    >
                                        <Button
                                            variant="primary"
                                            size="sm"
                                            :icon="ArrowRight"
                                            icon-position="right"
                                        >
                                            Pilih
                                        </Button>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
