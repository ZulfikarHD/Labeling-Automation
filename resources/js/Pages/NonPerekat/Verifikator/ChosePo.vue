<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link,useForm } from '@inertiajs/vue3';

const form = useForm({
    team  : '',
});
</script>

<template>
    <ContentLayout>
        <div class="py-12">
            <!-- Nomor PO -->
            <div class="mx-auto w-fit">
                <InputLabel for="team" value="Team" class="text-4xl font-extrabold text-center"/>

                <TextInput
                    id="team"
                    ref="team"
                    v-model="form.team"
                    type="text"
                    class="mt-2 w-fit block px-8 py-2 text-2xl text-center"
                    autocomplete="team"
                    value="Non Personal 1"
                    disabled
                />
            </div>
            <!-- Header -->
            <h3 class="text-3xl font-extrabold uppercase text-center my-10 text-slate-700">List Produk Siap Periksa</h3>

            <!-- Table -->
            <div class="mx-auto w-fit h-full px-4 py-4 bg-white md:py-6 drop-shadow-sm rounded-xl dark:bg-slate-800 dark:bg-opacity-60 dark:backdrop-blur-sm dark:backdrop-filter">
                <div>
                    <div class="overflow-hidden -mx-4 -mt-6 rounded-t-xl">
                        <table class="min-w-full table-auto">
                            <thead
                                class="font-bold border-b-2 border-slate-300 text-slate-700 dark:border-slate-500 dark:text-slate-400 pb-4 bg-slate-200">
                                <tr>
                                    <th scope="col" class="pt-6 pb-1.5 px-6 leading-tight text-left border-slate-300 dark:border-slate-500 ">
                                        Nomor PO
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5 px-6 leading-tight text-left border-slate-300 dark:border-slate-500">
                                        OBC
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5 px-6 leading-tight text-center border-slate-300 dark:border-slate-500">
                                        Jenis
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Range
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Status
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Generated At
                                    </th>
                                    <th scope="col" class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Chose
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="transition duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100 font-mono">
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        3000160783
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        SID215509
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        NP
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        1 - 40
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        Waiting for Verif
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        04-05-2023 13:17:22
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700">
                                        <input type="checkbox">
                                    </td>
                                </tr>

                                <tr
                                    class="transition duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100 font-mono">
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        3000160782
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        SID215509
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        NP
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        41 - 80
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        On Process ...
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        04-05-2023 13:17:22
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700">
                                        <input type="checkbox" disabled>
                                    </td>
                                </tr>

                                <tr
                                    class="transition duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100 font-mono">
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        3000160780
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        SID215509
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        NP
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        1 - 40
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        Waiting For Verif
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        04-05-2023 13:17:22
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700">
                                        <input type="checkbox">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="flex justify-center w-fit mx-auto gap-6">
                <button class="w-fit px-8 py-4 bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-11 mx-auto flex justify-center">
                    <Link :href="route('np-kepala-newGen')" class="text-violet-50 text-2xl font-bold">Clear</Link>
                </button>
                <button class="w-fit px-8 py-4 bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11 mx-auto flex justify-center">
                    <Link :href="route('np-verif-genLabel')" class="text-yellow-50 text-2xl font-bold">Next</Link>
                </button>
            </div>
        </div>
    </ContentLayout>
</template>
