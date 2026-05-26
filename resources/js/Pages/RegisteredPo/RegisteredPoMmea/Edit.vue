<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { FileText, Layers, Hash, Save, ArrowLeft, AlertCircle } from '@lucide/vue';
import { inject, ref } from 'vue';

const swal = inject("$swal");

const props = defineProps({
    data_product: Object,
    data_periksa: Array,
});

const form = useForm({
    no_obc: props.data_product?.no_obc || '',
    type: props.data_product?.type || 'MMEA',
    labels: props.data_periksa?.map(label => ({
        nomor_rim: label.nomor_rim,
        periksa1: label.periksa1 || '',
        periksa2: label.periksa2 || '',
        lbr_kemas: label.lbr_kemas || '',
    })) || [],
});

const submit = () => {
    form.put(route('dataPoMmea.update', { no_po: props.data_product.no_po }), {
        onSuccess: () => {
            swal.fire({
                icon: "success",
                title: "Berhasil",
                text: "Data PO berhasil diperbarui",
                showConfirmButton: false,
                timer: 1500
            });
        },
        onError: (errors) => {
            swal.fire({
                icon: "error",
                title: "Gagal",
                text: "Terjadi kesalahan saat memperbarui data",
                confirmButtonText: 'OK'
            });
        },
    });
};
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
                            Edit Data Produksi PC Berperekat
                        </h1>
                        <p class="text-center text-slate-500 dark:text-slate-400 mt-2">
                            Nomor PO: {{ props.data_product?.no_po }}
                        </p>
                    </div>

                    <!-- Main Info Form -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- OBC -->
                        <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                            <div class="flex items-center justify-center gap-3 mb-3">
                                <Layers class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                <InputLabel for="obc" value="Nomor OBC"
                                    class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                            </div>
                            <TextInput
                                id="obc"
                                v-model="form.no_obc"
                                type="text"
                                class="text-xl text-center bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900"
                                :class="{ 'border-red-500': form.errors.no_obc }"
                                autocomplete="obc" />
                            <div v-if="form.errors.no_obc" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.no_obc }}
                            </div>
                        </div>

                        <!-- Produk Type -->
                        <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                            <div class="flex items-center justify-center gap-3 mb-3">
                                <Hash class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                <InputLabel for="type" value="Produk"
                                    class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                            </div>
                            <select
                                id="type"
                                v-model="form.type"
                                class="w-full text-xl text-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white px-4 py-3 transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 focus:outline-none"
                                :class="{ 'border-red-500': form.errors.type }">
                                <option value="MMEA">MMEA</option>
                                <option value="HPTL">HPTL</option>
                            </select>
                            <div v-if="form.errors.type" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.type }}
                            </div>
                        </div>

                        <!-- PO (Read-only) -->
                        <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                            <div class="flex items-center justify-center gap-3 mb-3">
                                <FileText class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                <InputLabel for="po" value="Nomor PO"
                                    class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                            </div>
                            <TextInput
                                id="po"
                                :value="props.data_product?.no_po"
                                type="text"
                                class="text-xl text-center bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white"
                                disabled />
                        </div>
                    </div>
                </div>

                <!-- Labels Edit Section -->
                <div
                    class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 mb-8 border border-slate-100 dark:border-slate-700">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
                            Edit Data Label
                        </h2>
                        <p class="text-slate-500 dark:text-slate-400">
                            Edit data periksa dan lebar kemas untuk setiap rim
                        </p>
                    </div>

                    <!-- Labels Table -->
                    <div class="overflow-hidden bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                            <thead class="bg-slate-50 dark:bg-slate-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center whitespace-nowrap">
                                        Nomor Rim
                                    </th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center whitespace-nowrap">
                                        Periksa 1
                                    </th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center whitespace-nowrap">
                                        Periksa 2
                                    </th>
                                    <th class="px-4 py-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-center whitespace-nowrap">
                                        Lebar Kemas
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr v-for="(label, index) in form.labels" :key="label.nomor_rim"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                                    <td class="px-4 py-3 text-sm text-center font-medium text-slate-900 dark:text-white">
                                        {{ label.nomor_rim }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <TextInput
                                            v-model="form.labels[index].periksa1"
                                            type="text"
                                            class="w-full text-sm text-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all duration-200"
                                            placeholder="-" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <TextInput
                                            v-model="form.labels[index].periksa2"
                                            type="text"
                                            class="w-full text-sm text-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all duration-200"
                                            placeholder="-" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <TextInput
                                            v-model="form.labels[index].lbr_kemas"
                                            type="text"
                                            class="w-full text-sm text-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-900 transition-all duration-200"
                                            placeholder="-" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>

                    <div v-if="form.errors.labels" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div class="flex items-center gap-2">
                            <AlertCircle class="w-5 h-5 text-red-600 dark:text-red-400" />
                            <p class="text-sm text-red-600 dark:text-red-400">{{ form.errors.labels }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-between gap-4 mt-8">
                    <Link
                        :href="route('dataPoMmea.index')"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 shadow-sm hover:shadow">
                        <ArrowLeft class="w-5 h-5" />
                        Kembali
                    </Link>
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-medium text-white bg-blue-600 dark:bg-blue-500 rounded-xl hover:bg-blue-700 dark:hover:bg-blue-600 transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                        <Save class="w-5 h-5" />
                        <span v-if="form.processing">Menyimpan...</span>
                        <span v-else>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
