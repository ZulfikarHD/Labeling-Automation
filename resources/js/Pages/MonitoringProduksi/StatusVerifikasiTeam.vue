<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import NavigateBackButton from '@/Components/NavigateBackButton.vue'
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, CheckCircle2, Clock, AlertCircle, FileText, Layers, Hash } from 'lucide-vue-next';

const props = defineProps({
    spec: Object,
    dataRim: Object,
    team: Object,
});

const form = useForm({
    team: props.team.workstation,
    po: props.spec.no_po,
    obc: props.spec.no_obc,
    seri: props.spec.no_obc.substr(4,1),
});

</script>

<template>
    <Head title="Monitor Produksi" />
    <AuthenticatedLayout>
        <div class="min-h-screen py-12 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
            <div class="container mx-auto px-4 max-w-7xl">
                <!-- Header Section -->
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 mb-8 border border-slate-100">
                    <div class="mb-6">
                        <h1 class="text-4xl font-bold text-slate-900 text-center tracking-tight">
                            Tim {{ form.team }}
                        </h1>
                        <p class="mt-2 text-slate-600 text-center">
                            Status Verifikasi Produksi
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- PO -->
                        <div class="bg-slate-50/50 rounded-xl p-6">
                            <div class="flex items-center justify-center gap-3 mb-3">
                                <FileText class="w-5 h-5 text-blue-600" />
                                <InputLabel for="po" value="Nomor PO" class="text-lg font-semibold text-slate-700"/>
                            </div>
                            <TextInput
                                id="po"
                                ref="po"
                                v-model="form.po"
                                type="number"
                                class="text-xl text-center bg-white border-slate-200 rounded-xl shadow-sm"
                                autocomplete="po"
                                disabled
                            />
                        </div>

                        <!-- OBC -->
                        <div class="bg-slate-50/50 rounded-xl p-6">
                            <div class="flex items-center justify-center gap-3 mb-3">
                                <Layers class="w-5 h-5 text-indigo-600" />
                                <InputLabel for="obc" value="Nomor OBC" class="text-lg font-semibold text-slate-700"/>
                            </div>
                            <TextInput
                                id="obc"
                                ref="obc"
                                v-model="form.obc"
                                type="text"
                                class="text-xl text-center bg-white border-slate-200 rounded-xl shadow-sm"
                                autocomplete="obc"
                                disabled
                            />
                        </div>

                        <!-- Seri -->
                        <div class="bg-slate-50/50 rounded-xl p-6">
                            <div class="flex items-center justify-center gap-3 mb-3">
                                <Hash class="w-5 h-5 text-emerald-600" />
                                <InputLabel for="seri" value="Nomor Seri" class="text-lg font-semibold text-slate-700"/>
                            </div>
                            <TextInput
                                id="seri"
                                ref="seri"
                                v-model="form.seri"
                                type="number"
                                class="text-xl text-center bg-white border-slate-200 rounded-xl shadow-sm"
                                autocomplete="seri"
                                disabled
                            />
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 border border-slate-100">
                    <div class="flex flex-col lg:flex-row justify-between gap-8">
                        <!-- Lembar Kiri -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-slate-800 text-center mb-6">
                                Lembar Kiri
                            </h3>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                                <template v-for="status in dataRim">
                                    <template v-if="status.potongan == 'Kiri'">
                                        <!-- Selesai -->
                                        <div v-if="status.finish != null"
                                            class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-sm font-bold text-emerald-700">{{ status.np_users }}</span>
                                                <span class="text-sm font-medium text-indigo-600">{{ status.no_rim }}</span>
                                            </div>
                                        </div>

                                        <!-- Proses -->
                                        <div v-else-if="status.start != null && status.finish == null"
                                            class="bg-amber-50 border border-amber-200 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-sm font-bold text-amber-700">{{ status.np_users }}</span>
                                                <span class="text-sm font-medium text-indigo-600">{{ status.no_rim }}</span>
                                            </div>
                                        </div>

                                        <!-- Belum -->
                                        <div v-else
                                            class="bg-slate-50 border border-slate-200 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-sm font-bold text-slate-400">-</span>
                                                <span class="text-sm font-medium text-indigo-600">{{ status.no_rim }}</span>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="flex flex-row lg:flex-col justify-center gap-6 bg-slate-50/50 p-6 rounded-xl">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center shadow-sm">
                                    <CheckCircle2 class="w-6 h-6 text-emerald-600" />
                                </div>
                                <span class="text-sm font-medium text-slate-700">Selesai Verifikasi</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center shadow-sm">
                                    <Clock class="w-6 h-6 text-amber-600" />
                                </div>
                                <span class="text-sm font-medium text-slate-700">Proses Verifikasi</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center shadow-sm">
                                    <AlertCircle class="w-6 h-6 text-slate-400" />
                                </div>
                                <span class="text-sm font-medium text-slate-700">Belum Verifikasi</span>
                            </div>
                        </div>

                        <!-- Lembar Kanan -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-slate-800 text-center mb-6">
                                Lembar Kanan
                            </h3>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                                <template v-for="status in dataRim">
                                    <template v-if="status.potongan == 'Kanan'">
                                        <!-- Selesai -->
                                        <div v-if="status.finish != null"
                                            class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-sm font-bold text-emerald-700">{{ status.np_users }}</span>
                                                <span class="text-sm font-medium text-indigo-600">{{ status.no_rim }}</span>
                                            </div>
                                        </div>

                                        <!-- Proses -->
                                        <div v-else-if="status.start != null && status.finish == null"
                                            class="bg-amber-50 border border-amber-200 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-sm font-bold text-amber-700">{{ status.np_users }}</span>
                                                <span class="text-sm font-medium text-indigo-600">{{ status.no_rim }}</span>
                                            </div>
                                        </div>

                                        <!-- Belum -->
                                        <div v-else
                                            class="bg-slate-50 border border-slate-200 rounded-xl p-3 transition-all hover:shadow-md hover:scale-105">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-sm font-bold text-slate-400">-</span>
                                                <span class="text-sm font-medium text-indigo-600">{{ status.no_rim }}</span>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex justify-center mt-8">
                    <Link :href="route('dashboard')"
                        class="inline-flex items-center gap-3 px-8 py-4 text-lg font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                        <span>Kembali ke Dashboard</span>
                        <ArrowRight class="w-5 h-5" />
                    </Link>
                </div>
            </div>

            <!-- Decorative Background -->
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                <div class="absolute left-0 right-0 top-0 -z-10 m-auto h-[310px] w-[310px] rounded-full bg-blue-400 opacity-20 blur-[100px]"></div>
                <div class="absolute right-0 top-0 -z-10 h-[310px] w-[310px] rounded-full bg-indigo-400 opacity-20 blur-[100px]"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
