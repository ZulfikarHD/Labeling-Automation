<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthenticatedLayout>
        <div class="absolute top-[30%] left-24">
            <button class="min-w-[60ch] w-fit px-20 py-6 bg-gradient-to-r from-sky-300 to-sky-400 rounded-xl flex align-middle justify-center">
                <Link :href="route('p.pic.monitor-products.index')" class="text-6xl font-extrabold text-sky-50">Monitor Barang</Link>
            </button>
            <button class="flex justify-center px-16 py-6 ml-20 w-fit bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl text-start mt-11">
                <Link :href="route('p.products.index')" class="text-6xl font-extrabold text-yellow-50">List Generated Label</Link>
            </button>
            <button class="min-w-[60ch] w-fit px-16 py-6 bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11 ml-44 flex justify-center">
                <Link :href="route('p.products.create')" class="text-6xl font-extrabold text-yellow-50">Generate New Label</Link>
            </button>
        </div>
        <div class="flex justify-center w-full">
            <button type="btn" class="flex justify-center px-4 py-2 mx-auto w-fit bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl text-start mt-11">
                <Link :href="route('np-choseUser')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-blue-50">
                        <path fill-rule="evenodd"
                            d="M20.25 12a.75.75 0 01-.75.75H6.31l5.47 5.47a.75.75 0 11-1.06 1.06l-6.75-6.75a.75.75 0 010-1.06l6.75-6.75a.75.75 0 111.06 1.06l-5.47 5.47H19.5a.75.75 0 01.75.75z"
                            clip-rule="evenodd" />
                    </svg>
                </Link>
            </button>
        </div>
    </AuthenticatedLayout>
</template>
