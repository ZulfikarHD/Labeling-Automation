<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ products:Object })

</script>

<template>
    <ContentLayout>
        <div class="py-12">
            <!-- Header -->
            <h3 class="my-10 text-3xl font-extrabold text-center uppercase text-slate-700">List Generated Labels</h3>

            <!-- Table -->
            <div class="h-full px-4 py-4 mx-auto bg-white w-fit md:py-6 drop-shadow-sm rounded-xl dark:bg-slate-800 dark:bg-opacity-60 dark:backdrop-blur-sm dark:backdrop-filter">
                <div>
                    <div class="-mx-4 -mt-6 overflow-hidden rounded-t-xl">
                        <table class="min-w-full table-auto">
                            <thead
                                class="pb-4 font-bold border-b-2 border-slate-300 text-slate-700 dark:border-slate-500 dark:text-slate-400 bg-slate-200">
                                <tr>
                                    <th scope="col" class="pt-6 pb-1.5 px-4 leading-tight text-center border-slate-300 dark:border-slate-500">
                                        No
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5 px-6 leading-tight text-left border-slate-300 dark:border-slate-500 ">
                                        Nomor PO
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5 px-6 leading-tight text-left border-slate-300 dark:border-slate-500">
                                        OBC
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5 px-6 leading-tight text-center border-slate-300 dark:border-slate-500">
                                        Group
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Generated At
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Start
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Finish
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in products"
                                    class="font-mono transition duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100">
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 font-medium text-slate-950 border-r">
                                        {{ product.id }}
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.no_po }}
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.no_obc }}
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.assigned_team }}
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.created_at }}
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.status }}
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.status }}
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700">
                                        <div class="flex justify-center gap-2">
                                             <Link :href="route('p.products.create')" class="font-bold text-blue-400 transition duration-150 ease-in-out hover:text-blue-700">Info</Link>
                                             <Link :href="route('p.products.create')" class="font-bold text-green-400 transition duration-150 ease-in-out hover:text-green-700">Edit</Link>
                                             <Link :href="route('p.products.create')" class="font-bold text-red-400 transition duration-150 ease-in-out hover:text-red-700">Delete</Link>
                                        </div>
                                    </td>
                                </tr>
<!--
                                <tr
                                    class="font-mono transition duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100">
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 font-medium text-slate-950 border-r">
                                        2
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        3000160782
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        SID215509
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        -
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        04-05-2023 13:17:22
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        04-05-2023 13:07:22
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        On Progress ...
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700">
                                        <div class="flex justify-center gap-2">
                                             <Link :href="route('p.products.create')" class="font-bold text-blue-400 transition duration-150 ease-in-out hover:text-blue-700">Info</Link>
                                        </div>
                                    </td>
                                </tr>

                                <tr
                                    class="font-mono transition duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100">
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 font-medium text-slate-950 border-r">
                                        3
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        3000160781
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        SID215509
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        -
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        04-05-2023 12:46:22
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        04-05-2023 12:48:52
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        04-05-2023 13:05:36
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700">
                                        <div class="flex justify-center gap-2">
                                             <Link :href="route('p.products.create')" class="font-bold text-blue-400 transition duration-150 ease-in-out hover:text-blue-700">Info</Link>
                                        </div>
                                    </td>
                                </tr> -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-center w-full">
            <button type="btn" class="flex justify-center px-4 py-2 mx-auto w-fit bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl text-start mt-11">
                <Link :href="route('np-kepala-menu')">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-blue-50">
                        <path fill-rule="evenodd"
                            d="M20.25 12a.75.75 0 01-.75.75H6.31l5.47 5.47a.75.75 0 11-1.06 1.06l-6.75-6.75a.75.75 0 010-1.06l6.75-6.75a.75.75 0 111.06 1.06l-5.47 5.47H19.5a.75.75 0 01.75.75z"
                            clip-rule="evenodd" />
                    </svg>
                </Link>
            </button>
        </div>
    </ContentLayout>
</template>
