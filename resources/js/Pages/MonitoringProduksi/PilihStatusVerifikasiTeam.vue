<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Settings, ArrowLeftCircle, Activity } from 'lucide-vue-next';

const props = defineProps({
    workstations: Object,
});
</script>

<template>
    <Head title="Monitor Produksi" />
    <AuthenticatedLayout>
        <div class="min-h-screen py-16 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
            <!-- Hero Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center space-y-4">
                    <h1 class="text-4xl font-bold text-slate-900 dark:text-white tracking-tight sm:text-5xl md:text-6xl">
                        Status Verifikasi <span class="text-blue-600 dark:text-blue-400">Team</span>
                    </h1>
                    <p class="mt-3 max-w-2xl mx-auto text-lg text-slate-600 dark:text-slate-400">
                        Pantau Status Verifikasi Yang Sedang Berjalan Pada Masing Masing Team
                    </p>
                </div>
            </div>

            <!-- Workstation Grid -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="workstation in workstations"
                        :key="workstation.id"
                        :href="route('monitoringProduksi.statusVerif.show', workstation.id)"
                        class="group relative bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl p-8 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-100 dark:border-slate-700 hover:border-blue-200 dark:hover:border-blue-500"
                    >
                        <div class="flex flex-col space-y-4">
                            <div>
                                <h3 class="text-xl font-semibold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">
                                    {{ workstation.workstation }}
                                </h3>
                                <p class="mt-2 text-slate-600 dark:text-slate-400">Lihat detail status verifikasi</p>
                            </div>
                            <div class="absolute bottom-8 right-8 opacity-0 transform translate-x-4 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300">
                                <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-900">
                                    <ArrowLeftCircle class="w-5 h-5 text-blue-600 dark:text-blue-400 rotate-180" />
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Back to Dashboard Button -->
            <div class="flex justify-center mt-16">
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center px-8 py-4 text-base font-medium rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 dark:from-blue-500 dark:to-indigo-500 dark:hover:from-blue-600 dark:hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-blue-400 shadow-md hover:shadow-lg transition-all duration-300"
                >
                    <ArrowLeftCircle class="w-5 h-5 mr-2" />
                    Kembali ke Dashboard
                </Link>
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
