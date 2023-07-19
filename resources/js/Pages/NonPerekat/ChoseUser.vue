<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <div class="absolute top-[35%] left-28">
            <Link :href="route('np.pic')"
                class="text-6xl font-extrabold text-sky-50 w-fit px-24 py-6 bg-gradient-to-r from-sky-300 to-sky-400 rounded-xl flex align-middle justify-center">
                Kepala Meja
            </Link>
            <Link :href="route('np.generateLabels.index')"
                class="text-6xl font-extrabold text-yellow-50 w-fit py-6 px-24 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl text-start mt-10 ml-12 flex justify-center">
                Verifikator
            </Link>
        </div>
</AuthenticatedLayout></template>
