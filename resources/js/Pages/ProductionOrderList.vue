<script setup>
import { inject, ref } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import PaginateLink from "@/Components/PaginateLink.vue";
import { Search, Printer, Home, Trash2, Eye, Filter, Edit } from 'lucide-vue-next';

// Menginisialisasi variabel dan fungsi yang diperlukan
const swal = inject("$swal"); // Menggunakan inject untuk mengakses swal dari parent component
const props = defineProps({
    products: Object,
    listTeam: Object,
    crntTeam: String,
    editModal: {
        type: Boolean,
        default: false,
    },
});

const listProduct = ref(props.products); // Menginisialisasi listProduct dengan nilai awal dari props
const paginateUrl = ref(props.products); // Menginisialisasi paginateUrl dengan nilai awal dari props

const form = useForm({
    id: "",
    po: "",
    team: props.crntTeam,
    search: "",
    sort_field: "created_at",
    sort_direction: "desc",
});

const deleteModal = ref(false); // Menginisialisasi deleteModal sebagai Boolean

// Fungsi untuk mengformat tanggal
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

// Fungsi untuk memfilter produk berdasarkan tim
const filterTeam = () => {
    axios.post("/data-po/" + form.team, form).then((res) => {
        listProduct.value = res.data;
    });
};

// Fungsi untuk mencari produk berdasarkan kata kunci
const search = () => {
    axios.post("/data-po/" + form.team, form).then((res) => {
        listProduct.value = res.data;
    }).catch(error => {
        console.error("Error fetching search results:", error);
    });
};

// Fungsi untuk menghapus order
const deleteOrder = () => {
    router.delete(route('dataPo.destroy', form.po), {
        onSuccess: () => {
            filterTeam(); // Memfilter ulang produk setelah menghapus
            swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "Order " + form.po + " berhasil dihapus",
                showConfirmButton: false,
                timer: 1500
            });
            form.reset(); // Mengreset form setelah menghapus
            deleteModal.value = !deleteModal.value; // Menutup modal setelah menghapus
        },
    });
};

// Add new sorting function
const sort = (field) => {
    if (form.sort_field === field) {
        form.sort_direction = form.sort_direction === 'asc' ? 'desc' : 'asc';
    } else {
        form.sort_field = field;
        form.sort_direction = 'asc';
    }

    axios.post("/data-po/" + form.team, form).then((res) => {
        listProduct.value = res.data;
    }).catch(error => {
        console.error("Error sorting data:", error);
    });
};
</script>

<template>
    <Head title="Daftar PO" />

    <!-- Delete Modal -->
    <Modal :show="deleteModal" @close="deleteModal = !deleteModal">
        <div class="flex flex-col gap-4 p-6 dark:bg-slate-800">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-red-100/50 dark:bg-red-900/50 rounded-xl">
                    <Trash2 class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="flex flex-col gap-2">
                    <h3 class="text-xl font-semibold text-slate-900 dark:text-white">
                        Hapus Order {{ form.po }}?
                    </h3>
                    <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Peringatan: Menghapus order ini akan menghapus seluruh data label terkait, baik yang sudah dikerjakan maupun belum. Data yang dihapus tidak dapat dikembalikan.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-slate-200 dark:border-slate-700">
                <button
                    class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 transition-colors duration-200 hover:text-slate-900 dark:hover:text-white"
                    @click.prevent="deleteModal = !deleteModal"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click.prevent="deleteOrder"
                    class="px-4 py-2 text-sm font-medium text-white transition-all duration-200 bg-red-500 rounded-lg hover:bg-red-600 focus:ring-2 focus:ring-red-200 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900"
                >
                    Hapus Order
                </button>
            </div>
        </div>
    </Modal>

    <AuthenticatedLayout>
        <div class="py-12 px-4 max-w-7xl mx-auto">
            <div class="space-y-8">
                <!-- Page Header -->
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Daftar Order Produksi</h1>
                    <p class="text-slate-500 dark:text-slate-400">Kelola dan pantau order produksi yang labelnya sudah dibuat</p>
                </div>

                <!-- Filters & Search -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="w-full md:w-64">
                        <div class="relative">
                            <Filter class="absolute left-3 top-2.5 w-5 h-5 text-slate-400" />
                            <select
                                id="team"
                                ref="team"
                                v-model="form.team"
                                @change="filterTeam"
                                class="w-full pl-10 pr-4 py-2 text-sm bg-white dark:bg-slate-800 border rounded-xl border-slate-200 dark:border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all duration-200 dark:text-white"
                            >
                                <option value="0">Semua Tim</option>
                                <option
                                    v-for="teams in props.listTeam"
                                    :value="teams.id"
                                >
                                    {{ teams.workstation }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="relative w-full md:w-64">
                        <Search class="absolute left-3 top-2.5 w-5 h-5 text-slate-400" />
                        <TextInput
                            id="search"
                            @input="search"
                            v-model="form.search"
                            type="search"
                            placeholder="Cari order..."
                            class="w-full pl-10 rounded-xl border-slate-200 dark:border-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 dark:bg-slate-800 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Table Card -->
                <div class="overflow-hidden bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-700/50">
                                <tr>
                                    <th scope="col" class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center">No</th>
                                    <th
                                        scope="col"
                                        @click="sort('no_po')"
                                        class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-left cursor-pointer hover:text-slate-700 dark:hover:text-slate-300 group"
                                    >
                                        <div class="flex items-center gap-1">
                                            Nomor PO
                                            <span v-if="form.sort_field === 'no_po'" class="text-blue-500">
                                                {{ form.sort_direction === 'asc' ? '↑' : '↓' }}
                                            </span>
                                            <span v-else class="opacity-0 group-hover:opacity-50">↑</span>
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        @click="sort('no_obc')"
                                        class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-left cursor-pointer hover:text-slate-700 dark:hover:text-slate-300 group"
                                    >
                                        <div class="flex items-center gap-1">
                                            OBC
                                            <span v-if="form.sort_field === 'no_obc'" class="text-blue-500">
                                                {{ form.sort_direction === 'asc' ? '↑' : '↓' }}
                                            </span>
                                            <span v-else class="opacity-0 group-hover:opacity-50">↑</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center">Tim</th>
                                    <th
                                        scope="col"
                                        @click="sort('created_at')"
                                        class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center cursor-pointer hover:text-slate-700 dark:hover:text-slate-300 group"
                                    >
                                        <div class="flex items-center justify-center gap-1">
                                            Tanggal Dibuat
                                            <span v-if="form.sort_field === 'created_at'" class="text-blue-500">
                                                {{ form.sort_direction === 'asc' ? '↑' : '↓' }}
                                            </span>
                                            <span v-else class="opacity-0 group-hover:opacity-50">↑</span>
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        @click="sort('status')"
                                        class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center cursor-pointer hover:text-slate-700 dark:hover:text-slate-300 group"
                                    >
                                        <div class="flex items-center justify-center gap-1">
                                            Status
                                            <span v-if="form.sort_field === 'status'" class="text-blue-500">
                                                {{ form.sort_direction === 'asc' ? '↑' : '↓' }}
                                            </span>
                                            <span v-else class="opacity-0 group-hover:opacity-50">↑</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center">Selesai</th>
                                    <th scope="col" class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr
                                    v-for="(product, index) in listProduct.data"
                                    :key="index"
                                    class="hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-150"
                                >
                                    <td class="px-4 py-4 text-sm text-center text-slate-500 dark:text-slate-400">
                                        {{ listProduct.current_page == 1 ? index + 1 : index + 1 + listProduct.current_page * 10 }}
                                    </td>
                                    <td class="px-4 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                        {{ product.no_po }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <span :class="product.no_obc.substr(4, 1) == 3 ? ' text-red-700 dark:text-red-400' : ' text-blue-700 dark:text-blue-400'"
                                              class="px-2.5 py-1 text-xs font-medium">
                                            {{ product.no_obc }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-center text-slate-500 dark:text-slate-400">
                                        {{ product.workstation }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-center text-slate-500 dark:text-slate-400">
                                        {{ formatDate(product.created_at) }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-center">
                                        <span
                                            :class="{
                                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-400': product.status == 1,
                                                'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300': product.status == 0,
                                                'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-400': product.status == 2
                                            }"
                                            class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{ product.status == 1 ? 'Sedang Diperiksa' : product.status == 0 ? 'Siap Diperiksa' : 'Selesai' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-center text-slate-500 dark:text-slate-400">
                                        {{ product.status == 2 ? formatDate(product.updated_at) : '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-center">
                                        <div class="flex items-center justify-center">
                                            <Link
                                                :href="route('dataPo.show', { team: form.team, no_po: product.no_po })"
                                                class="p-1.5 text-blue-600 dark:text-blue-400 transition-colors duration-200 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/50"
                                                title="Lihat Detail"
                                            >
                                                <Eye class="w-5 h-5" />
                                            </Link>

                                            <Link
                                                :href="route('orderBesar.cetakLabel', { team: product.assigned_team, id: product.id })"
                                                class="p-1.5 text-indigo-600 dark:text-indigo-400 transition-colors duration-200 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/50"
                                                title="Cetak Label"
                                            >
                                                <Printer class="w-5 h-5" />
                                            </Link>

                                            <Link
                                                :href="route('dataPo.edit', { no_po: product.no_po })"
                                                class="p-1.5 text-amber-600 dark:text-amber-400 transition-colors duration-200 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/50"
                                                title="Edit"
                                            >
                                                <Edit class="w-5 h-5" />
                                            </Link>

                                            <button
                                                @click.prevent="
                                                    deleteModal = !deleteModal;
                                                    form.id = product.id;
                                                    form.po = product.no_po;
                                                "
                                                class="p-1.5 text-red-600 dark:text-red-400 transition-colors duration-200 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/50"
                                                title="Hapus Order"
                                            >
                                                <Trash2 class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-4 border-t border-slate-200 dark:border-slate-700">
                        <PaginateLink :links="listProduct.links" />
                    </div>
                </div>
            </div>

            <!-- Home Button -->
            <div class="flex justify-center mt-12">
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white transition-all duration-200 bg-blue-500 dark:bg-blue-600 rounded-xl hover:bg-blue-600 dark:hover:bg-blue-700 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-900"
                >
                    <Home class="w-5 h-5" />
                    Kembali ke Dashboard
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
