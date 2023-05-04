<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <div class="absolute top-[35%] left-28">
            <button class="w-[60ch] px-24 py-6 bg-gradient-to-r from-sky-300 to-sky-400 rounded-xl flex align-middle justify-center">
                <Link :href="route('np-kepala-menu')" class="text-sky-50 text-6xl font-extrabold">Kepala Meja</Link>
            </button>
            <button class="w-[60ch] px-24 py-6 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl text-start mt-10 ml-12 flex justify-center">
                <Link :href="route('np-verif-chosePo')" class="text-yellow-50 text-6xl font-extrabold">Verifikator</Link>
            </button>
        </div>
    </AuthenticatedLayout>
</template>
