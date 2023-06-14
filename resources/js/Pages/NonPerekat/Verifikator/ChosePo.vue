<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

defineProps({
    products: Object,
});

const form = useForm({
    team: '',
});


</script>

<template>
    <ContentLayout>
        <div class="py-12">
            <!-- Nomor PO -->
            <div class="mx-auto w-fit">
                <InputLabel for="team" value="Team" class="text-4xl font-extrabold text-center" />

                <TextInput id="team" ref="team" v-model="form.team" type="text"
                    class="block px-8 py-2 mt-2 text-2xl text-center w-fit" autocomplete="team" value="Non Personal 1"
                    disabled />
            </div>
            <!-- Header -->
            <h3 class="my-10 text-3xl font-extrabold text-center uppercase text-slate-700">List Produk Siap Periksa</h3>

            <!-- Table -->
            <div
                class="h-full px-4 py-4 mx-auto bg-white w-fit md:py-6 drop-shadow-sm rounded-xl dark:bg-slate-800 dark:bg-opacity-60 dark:backdrop-blur-sm dark:backdrop-filter">
                <div>
                    <div class="-mx-4 -mt-6 overflow-hidden rounded-t-xl">
                        <table class="min-w-full table-auto">
                            <thead
                                class="pb-4 font-bold border-b-2 border-slate-300 text-slate-700 dark:border-slate-500 dark:text-slate-400 bg-slate-200">
                                <tr>
                                    <th scope="col"
                                        class="pt-6 pb-1.5 px-6 leading-tight text-left border-slate-300 dark:border-slate-500 ">
                                        Nomor PO
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5 px-6 leading-tight text-left border-slate-300 dark:border-slate-500">
                                        OBC
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5 px-6 leading-tight text-center border-slate-300 dark:border-slate-500">
                                        Jenis
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Range
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Generated At
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Chose
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in products"
                                    class="font-mono transition duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100">
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.no_po }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.no_obc }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.type }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.start_rim }} - {{ product.end_rim }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        Waiting for Verif
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.created_at }}
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700">
                                        <button class="flex justify-center px-4 py-1 mx-auto font-semibold tracking-wide w-fit bg-gradient-to-r from-cyan-300 to-cyan-400 rounded-xl text-start text-cyan-50">
                                            <Link :href="route('np.genLabels.show', product.id)">Go</Link>
                                        </button>
                                        <!-- <input type="checkbox" value="{{ product.id }}"> -->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="flex justify-center gap-6 mx-auto w-fit">
                <button
                    class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-11">
                    <Link :href="route('np-choseUser')" class="text-2xl font-bold text-violet-50">Clear</Link>
                </button>
            <button
                class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11">
                <Link :href="route('np-verif-genLabel')" class="text-2xl font-bold text-yellow-50">Next</Link>
            </button>
        </div>
    </div>
</ContentLayout></template>
