<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <div class="absolute top-[30%] left-24">
            <button class="min-w-[60ch] w-fit px-20 py-6 bg-gradient-to-r from-sky-300 to-sky-400 rounded-xl flex align-middle justify-center">
                <Link :href="route('p.genLabels.index')" class="text-6xl font-extrabold text-sky-50">Start Verifikasi</Link>
            </button>
        </div>
    </AuthenticatedLayout>
</template>
