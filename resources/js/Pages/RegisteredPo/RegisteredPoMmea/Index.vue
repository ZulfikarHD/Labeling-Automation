<script setup>
import StatusProduksiBadge from '@/Components/StatusProduksiBadge.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Home, Search, Trash2, Eye, Edit, Printer } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    registeredPo: Object,
});

const form = useForm({
    search: '',
});

const listProduct = ref(props.registeredPo)

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    const day = String(date.getDate()).padStart(2, '0');
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day} ${month} ${year} ${hours}:${minutes}`;
};

const search = () => {
    axios.post("/data-po-mmea/", form).then((res) => {
        listProduct.value = res.data;
    }).catch(error => {
        console.error("Error fetching search results:", error);
    });
};

</script>

<template>
    <AuthenticatedLayout>
        <div class="py-12 px-4 max-w-7xl mx-auto">
            <div class="space-y-8">
                <!-- Page Header -->
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Daftar Order Produksi MMEA</h1>
                    <p class="text-slate-500 dark:text-slate-400">Kelola dan pantau order produksi yang labelnya sudah
                        dibuat</p>
                </div>

                <!-- Table -->

                <div
                    class="overflow-hidden bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <!-- Table Header with Search and Filter -->
                    <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <!-- Search Input -->
                            <div class="relative w-full md:w-64">
                                <Search class="absolute left-3 top-2.5 w-5 h-5 text-slate-400" />
                                <input v-model="form.search" @input="search" type="search" placeholder="Cari order..."
                                    class="w-full pl-10 pr-4 py-2 text-sm bg-white dark:bg-slate-800 border rounded-xl border-slate-200 dark:border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all duration-200 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <!-- Table Content -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <!-- Table Headers -->
                            <thead class="bg-slate-50 dark:bg-slate-700/50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center whitespace-nowrap">
                                        No
                                    </th>
                                    <th
                                        class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-left cursor-pointer group whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            Nomor PO
                                        </div>
                                    </th>
                                    <th
                                        class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-left cursor-pointer group whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            OBC
                                        </div>
                                    </th>
                                    <th
                                        class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-left cursor-pointer group whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            Produk
                                        </div>
                                    </th>
                                    <th
                                        class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center cursor-pointer group whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1">
                                            Tanggal Dibuat
                                        </div>
                                    </th>
                                    <th
                                        class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center cursor-pointer group whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1">
                                            Status
                                        </div>
                                    </th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center whitespace-nowrap">
                                        Selesai</th>
                                    <th scope="col"
                                        class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center whitespace-nowrap">
                                        Aksi</th>
                                </tr>
                            </thead>

                            <!-- Table Body -->
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr v-for="(dataPo, index) in listProduct.data" :key="index"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                                    <td
                                        class="px-4 py-3 text-sm text-center text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ listProduct.current_page == 1 ? index + 1 : index + 1 +
                                            (listProduct.current_page - 1)
                                        * 10 }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white whitespace-nowrap">
                                        {{ dataPo.no_po }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-1 text-xs font-medium text-blue-700">
                                            {{ dataPo.no_obc }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-center text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        <span :class="[
                                            dataPo.produk == 'MMEA'
                                                ? 'text-amber-700 dark:text-amber-400'
                                                : 'text-emerald-700 dark:text-emerald-400'
                                        ]" class="px-2.5 py-1 text-xs font-medium">
                                            {{ dataPo.produk }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        {{ formatDate(dataPo.created_at) }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-center text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        <StatusProduksiBadge :status="dataPo.status" />
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-center text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ dataPo.status == 2 ? formatDate(dataPo.updated_at) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1">
                                            <button
                                                class="group relative p-2 text-blue-600 dark:text-blue-400 transition-colors duration-200 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/50">
                                                <Eye class="w-5 h-5" />
                                                <span
                                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 text-xs text-white bg-slate-900 dark:bg-slate-800 rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                                    Lihat Detail
                                                </span>
                                            </button>

                                            <button
                                                class="group relative p-2 text-cyan-600 dark:text-cyan-400 transition-colors duration-200 rounded-lg hover:bg-cyan-50 dark:hover:bg-cyan-900/50">
                                                <Printer class="w-5 h-5" />
                                                <span
                                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 text-xs text-white bg-slate-900 dark:bg-slate-800 rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                                    Cetak Label
                                                </span>
                                            </button>

                                            <button
                                                class="group relative p-2 text-amber-600 dark:text-amber-400 transition-colors duration-200 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/50">
                                                <Edit class="w-5 h-5" />
                                                <span
                                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 text-xs text-white bg-slate-900 dark:bg-slate-800 rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                                    Edit Order
                                                </span>
                                            </button>

                                            <button
                                                class="group relative p-2 text-red-600 dark:text-red-400 transition-colors duration-200 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/50">
                                                <Trash2 class="w-5 h-5" />
                                                <span
                                                    class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 text-xs text-white bg-slate-900 dark:bg-slate-800 rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                                    Hapus Order
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 py-4 border-t border-slate-200 dark:border-slate-700">
                        <PaginateLink />
                    </div>
                </div>


                <!-- Home Button -->
                <div class="flex justify-center mt-12">
                    <Link :href="route('dashboard')"
                        class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white transition-all duration-200 bg-blue-500 dark:bg-blue-600 rounded-xl hover:bg-blue-600 dark:hover:bg-blue-700 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-900">
                    <Home class="w-5 h-5" />
                    Kembali ke Dashboard
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>