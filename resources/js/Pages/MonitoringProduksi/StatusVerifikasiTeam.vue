<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowRight,
    CheckCircle2,
    Clock,
    AlertCircle,
    FileText,
    Layers,
    Hash,
} from "lucide-vue-next";
import { ref } from "vue";

const props = defineProps({
    spec: Object,
    dataRim: Object,
    team: Object,
});
const modalDetailProduksi = ref(false);

const form = useForm({
    team: props.team.workstation,
    po: props.spec.no_po,
    obc: props.spec.no_obc,
    seri: props.spec.no_obc.substr(4, 1),
});

const periksa1   = ref("");
const periksa2   = ref("");
const nomorPo    = ref(form.po);
const nomorObc   = ref(form.obc);
const nomorRim   = ref();
const startTime  = ref();
const finishTime = ref();
const prodTime   = ref();


const openModalDetailProduksi = (dataLabel) => {
    modalDetailProduksi.value = true;

    periksa1.value   = dataLabel.np_users;
    periksa2.value   = dataLabel.np_user_p2 ?? "-";
    nomorPo.value    = form.po;
    nomorObc.value   = form.obc;
    nomorRim.value   = dataLabel.no_rim;
    startTime.value  = dataLabel.start;
    finishTime.value = dataLabel.finish;
    prodTime.value   = finishTime.value - startTime.value;


};
</script>

<template>
    <Modal
        :show="modalDetailProduksi"
        @close="() => (modalDetailProduksi = !modalDetailProduksi)"
    >
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex flex-col gap-4 mb-10">
                <!-- Modal header -->
                <h1
                    class="text-xl font-bold text-center text-gray-800 dark:text-gray-200"
                >
                    Detail Waktu Produksi Pegawai
                </h1>
                <div class="w-full border-b-2 border-blue-300"></div>

                <!-- Detail -->
                <div class="flex flex-row gap-16 flex-wrap justify-center">
                    <div class="flex flex-col gap-2">

                        <div class="flex-1 flex gap-4">
                            <h2 class="min-w-24">NO</h2>
                            <span>:</span>
                            <p>{{ nomorRim }}</p>
                        </div>

                        <div class="flex-1 flex gap-4">
                            <h2 class="min-w-24">Periksa 1</h2>
                            <span>:</span>
                            <p>{{ periksa1 }}</p>
                        </div>

                        <div class="flex-1 flex gap-4">
                            <h2 class="min-w-24">Periksa 2</h2>
                            <span>:</span>
                            <p>{{ periksa2 }}</p>
                        </div>

                        <div class="flex-1 flex gap-4">
                            <h2 class="min-w-24">PO</h2>
                            <span>:</span>
                            <p>{{ nomorPo }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex-1 flex gap-4">
                            <h2 class="min-w-24">OBC</h2>
                            <span>:</span>
                            <p>{{ nomorObc }}</p>
                        </div>

                        <div class="flex-1 flex gap-4">
                            <h2 class="min-w-24">Mulai</h2>
                            <span>:</span>
                            <p>{{ startTime }}</p>
                        </div>

                        <div class="flex-1 flex gap-4">
                            <h2 class="min-w-24">Selesai</h2>
                            <span>:</span>
                            <p>{{ finishTime }}</p>
                        </div>

                        <div class="flex-1 flex gap-4">
                            <h2 class="min-w-24">Waktu Pengerjaan</h2>
                            <span>:</span>
                            <p>{{ prodTime }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full flex items-center justify-end px-4">
                <button
                    type="button"
                    @click="modalDetailProduksi = !modalDetailProduksi"
                    class="px-4 py-2 text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-700 hover:bg-blue-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </Modal>
    <Head title="Monitor Produksi" />
    <AuthenticatedLayout>
        <div
            class="min-h-screen py-12 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900"
        >
            <div class="container mx-auto px-4 max-w-7xl">
                <!-- Header Section -->
                <div
                    class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 mb-8 border border-slate-100 dark:border-slate-700"
                >
                    <div class="mb-6">
                        <h1
                            class="text-4xl font-bold text-slate-900 dark:text-white text-center tracking-tight"
                        >
                            Tim {{ form.team }}
                        </h1>
                        <p
                            class="mt-2 text-slate-600 dark:text-slate-400 text-center"
                        >
                            Status Verifikasi Produksi
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- PO -->
                        <div
                            class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6"
                        >
                            <div
                                class="flex items-center justify-center gap-3 mb-3"
                            >
                                <FileText
                                    class="w-5 h-5 text-blue-600 dark:text-blue-400"
                                />
                                <InputLabel
                                    for="po"
                                    value="Nomor PO"
                                    class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                                />
                            </div>
                            <TextInput
                                id="po"
                                ref="po"
                                v-model="form.po"
                                type="number"
                                class="text-xl text-center bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white"
                                autocomplete="po"
                                disabled
                            />
                        </div>

                        <!-- OBC -->
                        <div
                            class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6"
                        >
                            <div
                                class="flex items-center justify-center gap-3 mb-3"
                            >
                                <Layers
                                    class="w-5 h-5 text-indigo-600 dark:text-indigo-400"
                                />
                                <InputLabel
                                    for="obc"
                                    value="Nomor OBC"
                                    class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                                />
                            </div>
                            <TextInput
                                id="obc"
                                ref="obc"
                                v-model="form.obc"
                                type="text"
                                class="text-xl text-center bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white"
                                autocomplete="obc"
                                disabled
                            />
                        </div>

                        <!-- Seri -->
                        <div
                            class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6"
                        >
                            <div
                                class="flex items-center justify-center gap-3 mb-3"
                            >
                                <Hash
                                    class="w-5 h-5 text-emerald-600 dark:text-emerald-400"
                                />
                                <InputLabel
                                    for="seri"
                                    value="Nomor Seri"
                                    class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                                />
                            </div>
                            <TextInput
                                id="seri"
                                ref="seri"
                                v-model="form.seri"
                                type="number"
                                class="text-xl text-center bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white"
                                autocomplete="seri"
                                disabled
                            />
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div
                    class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 border border-slate-100 dark:border-slate-700"
                >
                    <div
                        class="flex flex-col lg:flex-row justify-between gap-8"
                    >
                        <!-- Lembar Kiri -->
                        <div class="flex-1">
                            <h3
                                class="text-2xl font-bold text-slate-800 dark:text-white text-center mb-6"
                            >
                                Lembar Kiri
                            </h3>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                                <template v-for="status in dataRim">
                                    <template v-if="status.potongan == 'Kiri'">
                                        <!-- Selesai -->
                                        <button
                                            v-if="status.finish != null"
                                            @click="openModalDetailProduksi(status)"
                                            class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105"
                                        >
                                            <div
                                                class="flex flex-col items-center gap-1"
                                            >
                                                <span
                                                    class="text-sm font-bold text-emerald-700 dark:text-emerald-400"
                                                    >{{ status.np_users }}</span
                                                >
                                                <span
                                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400"
                                                    >{{ status.no_rim }}</span
                                                >
                                            </div>
                                        </button>

                                        <!-- Proses -->
                                        <button
                                            v-else-if="
                                                status.start != null &&
                                                status.finish == null
                                            "
                                            @click="openModalDetailProduksi(status)"
                                            class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105"
                                        >
                                            <div
                                                class="flex flex-col items-center gap-1"
                                            >
                                                <span
                                                    class="text-sm font-bold text-amber-700 dark:text-amber-400"
                                                    >{{ status.np_users }}</span
                                                >
                                                <span
                                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400"
                                                    >{{ status.no_rim }}</span
                                                >
                                            </div>
                                        </button>

                                        <!-- Belum -->
                                        <button
                                            v-else
                                            @click="openModalDetailProduksi(status)"
                                            class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105"
                                        >
                                            <div
                                                class="flex flex-col items-center gap-1"
                                            >
                                                <span
                                                    class="text-sm font-bold text-slate-400 dark:text-slate-500"
                                                    >-</span
                                                >
                                                <span
                                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400"
                                                    >{{ status.no_rim }}</span
                                                >
                                            </div>
                                        </button>
                                    </template>
                                </template>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div
                            class="flex flex-row lg:flex-col justify-center gap-6 bg-slate-50/50 dark:bg-slate-700/50 p-6 rounded-xl"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 flex items-center justify-center shadow-sm"
                                >
                                    <CheckCircle2
                                        class="w-6 h-6 text-emerald-600 dark:text-emerald-400"
                                    />
                                </div>
                                <span
                                    class="text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >Selesai Verifikasi</span
                                >
                            </div>
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 flex items-center justify-center shadow-sm"
                                >
                                    <Clock
                                        class="w-6 h-6 text-amber-600 dark:text-amber-400"
                                    />
                                </div>
                                <span
                                    class="text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >Proses Verifikasi</span
                                >
                            </div>
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 flex items-center justify-center shadow-sm"
                                >
                                    <AlertCircle
                                        class="w-6 h-6 text-slate-400 dark:text-slate-500"
                                    />
                                </div>
                                <span
                                    class="text-sm font-medium text-slate-700 dark:text-slate-300"
                                    >Belum Verifikasi</span
                                >
                            </div>
                        </div>

                        <!-- Lembar Kanan -->
                        <div class="flex-1">
                            <h3
                                class="text-2xl font-bold text-slate-800 dark:text-white text-center mb-6"
                            >
                                Lembar Kanan
                            </h3>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                                <template v-for="status in dataRim">
                                    <template v-if="status.potongan == 'Kanan'">
                                        <!-- Selesai -->
                                        <button
                                            @click="openModalDetailProduksi(status)"
                                            v-if="status.finish != null"
                                            class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105"
                                        >
                                            <div
                                                class="flex flex-col items-center gap-1"
                                            >
                                                <span
                                                    class="text-sm font-bold text-emerald-700 dark:text-emerald-400"
                                                    >{{ status.np_users }}</span
                                                >
                                                <span
                                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400"
                                                    >{{ status.no_rim }}</span
                                                >
                                            </div>
                                        </button>

                                        <!-- Proses -->
                                        <button
                                            @click="openModalDetailProduksi(status)"
                                            v-else-if="
                                                status.start != null &&
                                                status.finish == null
                                            "
                                            class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105"
                                        >
                                            <div
                                                class="flex flex-col items-center gap-1"
                                            >
                                                <span
                                                    class="text-sm font-bold text-amber-700 dark:text-amber-400"
                                                    >{{ status.np_users }}</span
                                                >
                                                <span
                                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400"
                                                    >{{ status.no_rim }}</span
                                                >
                                            </div>
                                        </button>

                                        <!-- Belum -->
                                        <button
                                            @click="openModalDetailProduksi(status)"
                                            v-else
                                            class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105"
                                        >
                                            <div
                                                class="flex flex-col items-center gap-1"
                                            >
                                                <span
                                                    class="text-sm font-bold text-slate-400 dark:text-slate-500"
                                                    >-</span
                                                >
                                                <span
                                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400"
                                                    >{{ status.no_rim }}</span
                                                >
                                            </div>
                                        </button>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-center mt-8">
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-3 px-8 py-4 text-lg font-semibold text-white bg-blue-600 dark:bg-blue-500 rounded-xl hover:bg-blue-700 dark:hover:bg-blue-600 transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                    >
                        <span>Kembali ke Dashboard</span>
                        <ArrowRight class="w-5 h-5" />
                    </Link>
                </div>
            </div>

            <!-- Decorative Background -->
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <div
                    class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:24px_24px]"
                ></div>
                <div
                    class="absolute left-0 right-0 top-0 -z-10 m-auto h-[310px] w-[310px] rounded-full bg-blue-400 dark:bg-blue-600 opacity-20 blur-[100px]"
                ></div>
                <div
                    class="absolute right-0 top-0 -z-10 h-[310px] w-[310px] rounded-full bg-indigo-400 dark:bg-indigo-600 opacity-20 blur-[100px]"
                ></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
