<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import { Link } from '@inertiajs/vue3';
import { Hash, Layers, FileText, AlertCircle, CheckCircle2, Clock, ArrowRight } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    data_product: Object,
    data_periksa: Object,
})

const modalDetailProduksi = ref(false);
const periksa1 = ref('');
const periksa2 = ref('');
const nomorPo = ref('');
const nomorObc = ref('');
const nomorRim = ref('');
const lbrKemas = ref('');
const waktuP1 = ref('');
const waktuP2 = ref('');

const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    
    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
};

const openModalDetailProduksi = (dataLabel) => {
    modalDetailProduksi.value = true;
    
    periksa1.value = dataLabel.periksa1 ?? '-';
    periksa2.value = dataLabel.periksa2 ?? '-';
    nomorPo.value = dataLabel.nomor_po;
    nomorObc.value = props.data_product.no_obc;
    nomorRim.value = dataLabel.nomor_rim;
    lbrKemas.value = dataLabel.lbr_kemas ?? '-';
    waktuP1.value = formatDateTime(dataLabel.waktu_p1);
    waktuP2.value = formatDateTime(dataLabel.waktu_p2);
};

</script>

<template>
    <!-- Detail Modal -->
    <Modal :show="modalDetailProduksi" @close="() => (modalDetailProduksi = !modalDetailProduksi)">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8">
            <div class="flex flex-col gap-6">
                <!-- Modal header -->
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
                        Detail Data Verifikasi
                    </h2>
                    <div class="w-20 h-1 bg-blue-500 mx-auto mt-3 rounded-full"></div>
                </div>

                <!-- Detail Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-400 min-w-32">Nomor PO</span>
                            <span class="text-slate-500 dark:text-slate-400">:</span>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ nomorPo }}</p>
                        </div>

                        <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-400 min-w-32">Nomor OBC</span>
                            <span class="text-slate-500 dark:text-slate-400">:</span>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ nomorObc }}</p>
                        </div>

                        <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-400 min-w-32">Nomor Rim</span>
                            <span class="text-slate-500 dark:text-slate-400">:</span>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ nomorRim }}</p>
                        </div>

                        <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-400 min-w-32">Lembar Kemas</span>
                            <span class="text-slate-500 dark:text-slate-400">:</span>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ lbrKemas }}</p>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-4 p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-400 min-w-32">Periksa 1</span>
                            <span class="text-blue-600 dark:text-blue-400">:</span>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-300">{{ periksa1 }}</p>
                        </div>

                        <div class="flex items-center gap-4 p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                            <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-400 min-w-32">Periksa 2</span>
                            <span class="text-emerald-600 dark:text-emerald-400">:</span>
                            <p class="text-sm font-medium text-emerald-900 dark:text-emerald-300">{{ periksa2 }}</p>
                        </div>

                        <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-400 min-w-32">Waktu P1</span>
                            <span class="text-slate-500 dark:text-slate-400">:</span>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ waktuP1 }}</p>
                        </div>

                        <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-400 min-w-32">Waktu P2</span>
                            <span class="text-slate-500 dark:text-slate-400">:</span>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ waktuP2 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Close Button -->
                <div class="flex justify-end pt-4 mt-4 border-t border-slate-200 dark:border-slate-700">
                    <button 
                        type="button" 
                        @click="modalDetailProduksi = !modalDetailProduksi"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg transition-colors duration-200 shadow-sm hover:shadow">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </Modal>

    <AuthenticatedLayout>
        <div class="min-h-screen py-12">
            <div class="container mx-auto px-4 max-w-7xl">
                <!-- Header Section -->
                <div
                    class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 mb-8 border border-slate-100 dark:border-slate-700">
                    <div class="mb-6">
                        <h1 class="text-4xl font-bold text-slate-900 dark:text-white text-center tracking-tight">
                            Data Produksi PC Berperekat
                        </h1>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- PO -->
                        <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                            <div class="flex items-center justify-center gap-3 mb-3">
                                <FileText class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                <InputLabel for="po" value="Nomor PO"
                                    class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                            </div>
                            <TextInput id="po" ref="po" :value="props.data_product.no_po" type="number"
                                class="text-xl text-center bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white"
                                autocomplete="po" disabled />
                        </div>

                        <!-- OBC -->
                        <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                            <div class="flex items-center justify-center gap-3 mb-3">
                                <Layers class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                <InputLabel for="obc" value="Nomor OBC"
                                    class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                            </div>
                            <TextInput id="obc" ref="obc" :value="props.data_product.no_obc" type="text"
                                class="text-xl text-center bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white"
                                autocomplete="obc" disabled />
                        </div>

                        <!-- Produk -->
                        <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                            <div class="flex items-center justify-center gap-3 mb-3">
                                <Hash class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                <InputLabel for="seri" value="Produk"
                                    class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                            </div>
                            <TextInput id="produk" ref="produk" :value="props.data_product.type" type="text"
                                class="text-xl text-center bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white"
                                autocomplete="seri" disabled />
                        </div>
                    </div>
                </div>

                <!-- periksa Section -->
                <div
                    class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 border border-slate-100 dark:border-slate-700">
                    <div class="flex flex-col lg:flex-row justify-between gap-8">
                        <!-- Periksa1 -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white text-center mb-6">
                                Periksa 1
                            </h3>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                                <template v-for="periksa in props.data_periksa">
                                    <!-- Selesai -->
                                    <button v-if="periksa.updated_at != null" @click="openModalDetailProduksi(periksa)"
                                        class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{
                                                periksa.periksa1 }}</span>
                                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{
                                                periksa.nomor_rim }}</span>
                                        </div>
                                    </button>

                                    <!-- Proses -->
                                    <button v-else-if="
                                        periksa.created_at != null &&
                                        periksa.update_at == null
                                    " @click="openModalDetailProduksi(periksa)"
                                        class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-sm font-bold text-amber-700 dark:text-amber-400">{{
                                                periksa.periksa1 }}</span>
                                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{
                                                periksa.nomor_rim }}</span>
                                        </div>
                                    </button>

                                    <!-- Belum -->
                                    <button v-else @click="openModalDetailProduksi(periksa)"
                                        class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-sm font-bold text-slate-400 dark:text-slate-500">-</span>
                                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{
                                                periksa.nomor_rim }}</span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div
                            class="flex flex-row lg:flex-col justify-center gap-6 bg-slate-50/50 dark:bg-slate-700/50 p-6 rounded-xl">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 flex items-center justify-center shadow-sm">
                                    <CheckCircle2 class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Selesai
                                    Verifikasi</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 flex items-center justify-center shadow-sm">
                                    <Clock class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                                </div>
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Proses
                                    Verifikasi</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 flex items-center justify-center shadow-sm">
                                    <AlertCircle class="w-6 h-6 text-slate-400 dark:text-slate-500" />
                                </div>
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Belum
                                    Verifikasi</span>
                            </div>
                        </div>

                        <!-- Lembar Kanan -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white text-center mb-6">
                                Periksa 2
                            </h3>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                                <template v-for="periksa in props.data_periksa">
                                    <!-- Selesai -->
                                    <button @click="openModalDetailProduksi(periksa)" v-if="periksa.updated_at != null"
                                        class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{
                                                periksa.periksa2 }}</span>
                                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{
                                                periksa.nomor_rim }}</span>
                                        </div>
                                    </button>

                                    <!-- Proses -->
                                    <button @click="openModalDetailProduksi(periksa)" v-else-if="
                                        periksa.created_at != null &&
                                        periksa.updated_at == null
                                    "
                                        class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-sm font-bold text-amber-700 dark:text-amber-400">{{
                                                periksa.periksa2 }}</span>
                                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{
                                                periksa.nomor_rim }}</span>
                                        </div>
                                    </button>

                                    <!-- Belum -->
                                    <button @click="openModalDetailProduksi(periksa)" v-else
                                        class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-sm font-bold text-slate-400 dark:text-slate-500">-</span>
                                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{
                                                periksa.nomor_rim }}</span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-center mt-8">
                    <Link :href="route('orderSiapPeriksa.index')"
                        class="inline-flex items-center gap-3 px-8 py-4 text-lg font-semibold text-white bg-blue-600 dark:bg-blue-500 rounded-xl hover:bg-blue-700 dark:hover:bg-blue-600 transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    <span>Kembali ke Dashboard</span>
                    <ArrowRight class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
