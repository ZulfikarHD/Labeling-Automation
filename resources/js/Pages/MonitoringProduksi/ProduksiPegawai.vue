<script setup lang="ts">
import InputLabel from '@/Components/InputLabel.vue';
import TableVerifikasiPegawai from '@/Components/TableVerifikasiPegawai.vue';
import TextInput from '@/Components/TextInput.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Calendar } from 'lucide-vue-next';

const props = defineProps({
    teams: Object
})

const getDate = new Date()

const form = useForm({
    date: getDate.toISOString().substr(0, 10)
})
const today = ref(form.date)

</script>
<template>
    <Head title="Produksi Pegawai" />
    <AuthenticatedLayout>
        <div class="min-h-screen py-12 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
            <div class="max-w-[95%] mx-auto">
                <!-- Header Section -->
                <div class="text-center mb-12">
                    <h1 class="text-4xl font-bold text-slate-900 dark:text-white tracking-tight sm:text-5xl">
                        Monitoring Produksi Harian
                    </h1>
                </div>

                <!-- Date Filter -->
                <div class="max-w-md mx-auto mb-12">
                    <div class="relative">
                        <InputLabel
                            for="dateFilter"
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                        >
                            Tanggal Produksi
                        </InputLabel>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 dark:text-slate-500" />
                            <TextInput
                                id="dateFilter"
                                type="date"
                                v-model="form.date"
                                class="pl-12 w-full rounded-xl border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Teams Grid -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-12">
                    <template v-for="team in props.teams" :key="'team'+team.id">
                        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden hover:shadow-lg transition-all duration-300">
                            <div class="pt-4">
                                <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-4 text-center">
                                    Team {{ team.workstation }}
                                </h3>
                                <TableVerifikasiPegawai :team="team.id" :date="form.date" />
                            </div>
                        </div>
                    </template>
                </div>

                <!-- All Teams Section -->
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden hover:shadow-lg transition-all duration-300 w-fit mx-auto">
                    <div class="pt-4">
                        <h3 class="text-xl font-semibold text-slate-900 dark:text-white mb-4 text-center">
                            Semua Team
                        </h3>
                        <TableVerifikasiPegawai :team="0" :date="form.date" />
                    </div>
                </div>
            </div>

            <!-- Decorative Background -->
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                <div class="absolute left-0 right-0 top-0 -z-10 m-auto h-[310px] w-[310px] rounded-full bg-blue-400 dark:bg-blue-600 opacity-20 blur-[100px]"></div>
                <div class="absolute right-0 top-0 -z-10 h-[310px] w-[310px] rounded-full bg-indigo-400 dark:bg-indigo-600 opacity-20 blur-[100px]"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
