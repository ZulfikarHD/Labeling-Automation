<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <div class="absolute top-[35%] left-28 flex flex-col gap-4">
            <Link :href="route('np.choseUser')" class="text-5xl font-extrabold text-sky-50 w-fit px-12 py-6 bg-gradient-to-r from-sky-300 to-sky-400 rounded-xl flex align-middle">Pita Cukai Non-Perekat</Link>
            <Link :href="route('p.generateLabels')" class="text-5xl font-extrabold text-yellow-50 w-fit px-12 py-6 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl text-start mt-10 ml-12">Pita Cukai Perekat</Link>
        </div>
    </AuthenticatedLayout>
</template>
