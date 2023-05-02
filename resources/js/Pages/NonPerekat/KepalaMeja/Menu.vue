<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <div class="absolute top-[35%] left-28">
            <button class="min-w-[60ch] w-fit px-20 py-6 bg-gradient-to-r from-sky-300 to-sky-400 rounded-xl flex align-middle justify-center">
                <Link :href="route('dashboard')" class="text-sky-50 text-6xl font-extrabold">Monitor Barang</Link>
            </button>
            <button class="w-fit px-16 py-6 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl text-start mt-11 ml-20 flex justify-center">
                <Link :href="route('dashboard')" class="text-yellow-50 text-6xl font-extrabold">List Generated Label</Link>
            </button>
            <button class="min-w-[60ch] w-fit px-16 py-6 bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11 ml-44 flex justify-center">
                <Link :href="route('dashboard')" class="text-yellow-50 text-6xl font-extrabold">Generate New Label</Link>
            </button>
        </div>
    </AuthenticatedLayout>
</template>
