<script setup>
import { inject, ref } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import PaginateLink from "@/Components/PaginateLink.vue";
import { Search, Printer, Home, Trash2, Eye } from 'lucide-vue-next';

const swal = inject("$swal");

const props = defineProps({
    products: Object,
    listTeam: Object,
    crntTeam: String,
    editModal: {
        type: Boolean,
        default: false,
    },
});

const listProduct = ref(props.products);
const paginateUrl = ref(props.products);

const form = useForm({
    id: "",
    po: "",
    team: props.crntTeam,
    search: "",
});

const deleteModal = ref(false);

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

const filterTeam = () => {
    axios.post("/data-po/" + form.team, form).then((res) => {
        listProduct.value = res.data;
    });
};

const search = () => {
    axios.post("/data-po/" + form.team, form).then((res) => {
        listProduct.value = res.data;
    });
};

const deleteOrder = () => {
    router.delete(route('dataPo.destroy', form.po), {
        onSuccess: () => {
            filterTeam();
            swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "Order " + form.po + " berhasil dihapus",
            });
            form.reset();
            deleteModal.value = !deleteModal.value;
        },
    });
};
</script>

<template>
    <Head title="Daftar PO" />

    <!-- Delete Modal -->
    <Modal :show="deleteModal" @close="deleteModal = !deleteModal">
        <div class="flex flex-col gap-4 p-6">
            <div class="flex items-start gap-4">
                <div class="p-3 bg-red-100 rounded-full">
                    <Trash2 class="w-6 h-6 text-red-600" />
                </div>
                <div class="flex flex-col gap-2">
                    <h3 class="text-xl font-semibold text-slate-900">
                        Hapus Order {{ form.po }}?
                    </h3>
                    <p class="text-sm leading-relaxed text-slate-600">
                        Peringatan: Menghapus order ini akan menghapus seluruh data label terkait, baik yang sudah dikerjakan maupun belum. Data yang dihapus tidak dapat dikembalikan.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 mt-4 border-t border-slate-200">
                <button
                    class="px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-slate-800"
                    @click.prevent="deleteModal = !deleteModal"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click.prevent="deleteOrder"
                    class="px-4 py-2 text-sm font-medium text-white transition bg-red-600 rounded-lg hover:bg-red-700"
                >
                    Hapus Order
                </button>
            </div>
        </div>
    </Modal>

    <AuthenticatedLayout>
        <div class="py-12 px-4 max-w-7xl mx-auto">
            <div class="space-y-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Filter By Team -->
                    <div class="w-full md:w-64">
                        <select
                            id="team"
                            ref="team"
                            v-model="form.team"
                            @change="filterTeam"
                            class="w-full px-3 py-2 text-sm bg-white border rounded-lg border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
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

                    <!-- Search Box -->
                    <div class="relative w-full md:w-64">
                        <Search class="absolute left-3 top-2.5 w-5 h-5 text-slate-400" />
                        <TextInput
                            id="search"
                            @input="search"
                            v-model="form.search"
                            type="search"
                            placeholder="Cari order..."
                            class="w-full pl-10"
                        />
                    </div>
                </div>

                <!-- Table Card -->
                <div class="overflow-hidden bg-white rounded-xl shadow-sm border border-slate-200">
                    <!-- Table Header -->
                    <div class="p-4 bg-white border-b border-slate-200">
                        <h2 class="font-semibold text-slate-800">Daftar Order Produksi</h2>
                        <p class="text-sm text-slate-500">Daftar Order Produksi Yang Labelnya Sudah Dibuat</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-4 py-3.5 text-sm font-medium text-slate-600 text-center">No</th>
                                    <th scope="col" class="px-4 py-3.5 text-sm font-medium text-slate-600 text-left">Nomor PO</th>
                                    <th scope="col" class="px-4 py-3.5 text-sm font-medium text-slate-600 text-left">OBC</th>
                                    <th scope="col" class="px-4 py-3.5 text-sm font-medium text-slate-600 text-center">Tim</th>
                                    <th scope="col" class="px-4 py-3.5 text-sm font-medium text-slate-600 text-center">Tanggal Dibuat</th>
                                    <th scope="col" class="px-4 py-3.5 text-sm font-medium text-slate-600 text-center">Status</th>
                                    <th scope="col" class="px-4 py-3.5 text-sm font-medium text-slate-600 text-center">Selesai</th>
                                    <th scope="col" class="px-4 py-3.5 text-sm font-medium text-slate-600 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr
                                    v-for="(product, index) in listProduct.data"
                                    :key="index"
                                    class="hover:bg-slate-100 transition"
                                >
                                    <td class="px-4 py-3 text-sm text-center text-slate-600">
                                        {{ listProduct.current_page == 1 ? index + 1 : index + 1 + listProduct.current_page * 10 }}
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                        {{ product.no_po }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span :class="product.no_obc.substr(4, 1) == 3 ? 'text-red-600' : 'text-blue-600'" class="font-medium">
                                            {{ product.no_obc }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-slate-600">
                                        {{ product.workstation }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-slate-600">
                                        {{ formatDate(product.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <span
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': product.status == 1,
                                                'bg-slate-100 text-slate-800': product.status == 0,
                                                'bg-green-100 text-green-800': product.status == 2
                                            }"
                                            class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{ product.status == 1 ? 'Sedang Diperiksa' : product.status == 0 ? 'Siap Diperiksa' : 'Selesai' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center text-slate-600">
                                        {{ product.status == 2 ? formatDate(product.updated_at) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <Link
                                                :href="route('dataPo.show', { team: form.team, no_po: product.no_po })"
                                                class="p-1 text-blue-600 transition rounded-full hover:bg-blue-50"
                                                title="Lihat Detail"
                                            >
                                                <Eye class="w-5 h-5" />
                                            </Link>

                                            <Link
                                                :href="route('orderBesar.cetakLabel', { team: product.assigned_team, id: product.id })"
                                                class="p-1 text-indigo-600 transition rounded-full hover:bg-indigo-50"
                                                title="Cetak Label"
                                            >
                                                <Printer class="w-5 h-5" />
                                            </Link>

                                            <button
                                                @click.prevent="
                                                    deleteModal = !deleteModal;
                                                    form.id = product.id;
                                                    form.po = product.no_po;
                                                "
                                                class="p-1 text-red-600 transition rounded-full hover:bg-red-50"
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

                    <div class="px-4 py-3 border-t border-slate-200">
                        <PaginateLink :links="listProduct.links" />
                    </div>
                </div>
            </div>

            <!-- Home Button -->
            <div class="flex justify-center mt-8">
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white transition bg-blue-600 rounded-lg hover:bg-blue-700"
                >
                    <Home class="w-5 h-5" />
                    Kembali ke Dashboard
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
