<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import axios from "axios";
import { Head, router, useForm } from "@inertiajs/vue3";
import {
    FileText,
    Layers,
    Hash,
    Calendar,
    Users,
    BarChart,
    ClipboardCheck,
    CheckCircle2,
    Clock,
    AlertCircle,
} from "lucide-vue-next";
import { ref, watch } from "vue";

const props = defineProps({
    dataPo: Object,
    dataLabel: Object,
    teamList: Object,
    inschiet: String,
});

const teams = ref(props.teamList);
const labels = ref(props.dataLabel);

const form = useForm({
    id: props.dataPo.id,
    no_po: props.dataPo.no_po,
    no_obc: props.dataPo.no_obc,
    type: props.dataPo.type,
    sum_rim: props.dataPo.sum_rim,
    start_rim: props.dataPo.start_rim,
    end_rim: props.dataPo.end_rim,
    assigned_team: props.dataPo.assigned_team,
    status: props.dataPo.status,
    inschiet: props.inschiet,
});

// Watch for changes in start_rim and sum_rim to calculate end_rim
watch(
    [() => form.start_rim, () => form.sum_rim],
    ([newStartRim, newSumRim]) => {
        // Ensure start_rim is not zero or negative
        if (newStartRim <= 0) {
            form.start_rim = 1;
        }

        // Calculate end_rim based on start_rim and sum_rim
        form.end_rim = parseInt(form.start_rim) + parseInt(form.sum_rim) - 1;
    }
);

const updateOrder = () => {
    router.post("/api/production-order/update-rim", form)
}

</script>

<template>
    <Head title="Edit Production Order" />
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
                            Edit Order
                            <span class="text-blue-600">{{ form.no_po }}</span>
                        </h1>
                        <p
                            class="mt-2 text-slate-600 dark:text-slate-400 text-center"
                        >
                            Edit settingan label produksi
                        </p>
                    </div>

                    <form @submit.prevent="updateOrder">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <!-- PO Number -->
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
                                        value="Nomor PO"
                                        for="noPo"
                                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                                <TextInput
                                    v-model="form.no_po"
                                    id="noPo"
                                    type="text"
                                    class="text-center bg-slate-100 font-semibold"
                                    disabled
                                />
                            </div>

                            <!-- OBC Number -->
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
                                    v-model="form.no_obc"
                                    type="text"
                                    class="text-center font-bold"
                                />
                            </div>

                            <!-- Type -->
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
                                        for="produk"
                                        value="Produk"
                                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                                <TextInput
                                    id="produk"
                                    v-model="form.type"
                                    type="text"
                                    class="text-center"
                                />
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-8"
                        >
                            <!-- Sum Rim -->
                            <div
                                class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <BarChart
                                        class="w-5 h-5 text-purple-600 dark:text-purple-400"
                                    />
                                    <InputLabel
                                        for="totalRim"
                                        value="Total Rim"
                                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                                <TextInput
                                    id="totalRim"
                                    v-model="form.sum_rim"
                                    type="number"
                                    class="bg-slate-100 text-center"
                                    disabled
                                />
                            </div>

                            <!-- Start Rim -->
                            <div
                                class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <Calendar
                                        class="w-5 h-5 text-rose-600 dark:text-rose-400"
                                    />
                                    <InputLabel
                                        for="startRim"
                                        value="Nomor Rim Awal"
                                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                                <TextInput
                                    v-model="form.start_rim"
                                    id="startRim"
                                    type="number"
                                    min="1"
                                    class="text-center"
                                />
                            </div>

                            <!-- End Rim -->
                            <div
                                class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <Calendar
                                        class="w-5 h-5 text-amber-600 dark:text-amber-400"
                                    />
                                    <InputLabel
                                        for="endRim"
                                        value="Nomor Rim Akhir"
                                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                                <TextInput
                                    id="endRim"
                                    v-model="form.end_rim"
                                    type="number"
                                    disabled
                                    class="bg-slate-100 text-center"
                                />
                            </div>

                            <!-- Inschiet -->
                            <div
                                class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <ClipboardCheck
                                        class="w-5 h-5 text-teal-600 dark:text-teal-400"
                                    />
                                    <InputLabel
                                        for="inschiet"
                                        value="Inschiet"
                                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                                <TextInput
                                    id="inschiet"
                                    v-model="form.inschiet"
                                    type="number"
                                    class="text-center"
                                />
                            </div>
                        </div>

                        <!-- Team Assignment -->
                        <div class="mt-8">
                            <div
                                class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <Users
                                        class="w-5 h-5 text-blue-600 dark:text-blue-400"
                                    />
                                    <InputLabel
                                        value="Tim yang Ditugaskan"
                                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                                <select
                                    v-model="form.assigned_team"
                                    class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white"
                                >
                                    <option
                                        v-for="team in teams"
                                        :value="team.id"
                                    >
                                        {{ team.workstation }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-8 flex justify-end gap-4">
                            <button
                                class="px-6 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-all"
                            >
                                Batal
                            </button>
                            <button
                                class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all"
                            >
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Status Section -->
            <div
                class="mt-8 bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 border border-slate-100 dark:border-slate-700 container max-w-7xl mx-auto"
            >
                <div class="mb-6">
                    <h1
                        class="text-4xl font-bold text-slate-900 dark:text-white text-center tracking-tight"
                    >
                        Edit Label Produksi
                    </h1>
                    <p
                        class="mt-2 text-slate-600 dark:text-slate-400 text-center"
                    >
                        Edit Nomor Rim Manual
                    </p>
                </div>
                <div class="flex flex-col lg:flex-row justify-between gap-8">
                    <!-- Lembar Kiri -->
                    <div class="flex-1">
                        <h3
                            class="text-2xl font-bold text-slate-800 dark:text-white text-center mb-6"
                        >
                            Lembar Kiri
                        </h3>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                            <template v-for="status in labels">
                                <template v-if="status.potongan == 'Kiri'">
                                    <!-- Selesai -->
                                    <div
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
                                    </div>

                                    <!-- Proses -->
                                    <div
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
                                    </div>

                                    <!-- Belum -->
                                    <div
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
                                    </div>
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
                            <template v-for="status in labels">
                                <template v-if="status.potongan == 'Kanan'">
                                    <!-- Selesai -->
                                    <div
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
                                    </div>

                                    <!-- Proses -->
                                    <div
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
                                    </div>

                                    <!-- Belum -->
                                    <div
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
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
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
