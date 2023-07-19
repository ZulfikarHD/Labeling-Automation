<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <div class="absolute top-[30%] left-24">
            <Link :href="route('np.monitor.index')" class="text-6xl font-extrabold text-sky-50  w-fit px-20 py-6 bg-gradient-to-r from-sky-300 to-sky-400 rounded-xl flex align-middle justify-center">Monitor Barang</Link>
            <Link :href="route('np.listProducts.index')" class="text-6xl font-extrabold text-yellow-50 flex justify-center px-16 py-6 ml-20 w-fit bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl text-start mt-11">List Generated Label</Link>
            <Link :href="route('np.registerProducts.create')" class="text-6xl font-extrabold text-yellow-50  w-fit px-16 py-6 bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11 ml-44 flex justify-center">Generate New Label</Link>
        </div>
        <div class="flex justify-center w-full">
            <Link :href="route('np.choseUser')" class="flex justify-center px-4 py-2 mx-auto w-fit bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl text-start mt-11">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-blue-50">
                    <path fill-rule="evenodd"
                        d="M20.25 12a.75.75 0 01-.75.75H6.31l5.47 5.47a.75.75 0 11-1.06 1.06l-6.75-6.75a.75.75 0 010-1.06l6.75-6.75a.75.75 0 111.06 1.06l-5.47 5.47H19.5a.75.75 0 01.75.75z"
                        clip-rule="evenodd" />
                </svg>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
