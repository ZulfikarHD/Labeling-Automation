<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link } from '@inertiajs/vue3';
import { Hash, Layers, FileText, AlertCircle, CheckCircle2, Clock, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    data_product: Object,
    data_periksa: Object,
})

console.log(props.data_product);

</script>

<template>
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
                                                periksa.no_rim }}</span>
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
                                                periksa.no_rim }}</span>
                                        </div>
                                    </button>

                                    <!-- Belum -->
                                    <button v-else @click="openModalDetailProduksi(periksa)"
                                        class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-sm font-bold text-slate-400 dark:text-slate-500">-</span>
                                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{
                                                periksa.no_rim }}</span>
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
                                                periksa.no_rim }}</span>
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
                                                periksa.no_rim }}</span>
                                        </div>
                                    </button>

                                    <!-- Belum -->
                                    <button @click="openModalDetailProduksi(periksa)" v-else
                                        class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-sm font-bold text-slate-400 dark:text-slate-500">-</span>
                                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{
                                                periksa.no_rim }}</span>
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
