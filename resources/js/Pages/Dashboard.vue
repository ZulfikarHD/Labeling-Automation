<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <div class="absolute top-[35%] left-28">
            <button class="w-[60ch] px-12 py-6 bg-gradient-to-r from-sky-300 to-sky-400 rounded-xl flex align-middle">
                <Link :href="route('np-choseUser')" class="text-5xl font-extrabold text-sky-50">Pita Cukai Non-Perekat</Link>
            </button>
            <button class="w-[60ch] px-12 py-6 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl text-start mt-10 ml-12">
                <Link :href="route('p-choseUser')" class="text-5xl font-extrabold text-yellow-50">Pita Cukai Perekat</Link>
            </button>
        </div>
    </AuthenticatedLayout>
</template>
