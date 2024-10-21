<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';
import NavigateBackButton from '@/Components/NavigateBackButton.vue';
import { ref } from 'vue';

const props = defineProps({
                    products: Object,
                    teamList: Object,
                    crntTeam: Object,
                });

const listProduct = ref(props.products);

const form = useForm({
    team: props.crntTeam,
});

const changeTeam = () => {
    axios.get('/api/order-besar/verif/'+form.team).then((res) => {
        listProduct.value = res.data;
    });
}

</script>
<template>
    <ContentLayout>
        <div class="py-12">
            <!-- Change Team -->
            <div class="mx-auto w-fit">
                <InputLabel for="team" value="Tim" class="text-4xl font-extrabold text-center" />

                <select id="team" ref="team" v-model="form.team" type="text" @change="changeTeam"
                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-3xl mt-2 w-full" autocomplete="team">
                    <option class="px-10 py-4" v-for="teams in props.teamList" :value="teams.id">{{ teams.workstation }}</option>
                </select>
            </div>
            <!-- Header -->
            <h3 class="my-10 text-3xl font-extrabold text-center uppercase text-slate-700">Daftar Produk Siap Periksa</h3>

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
                                        Nomor Rim
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Waktu Dibuat
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Pilih
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in listProduct"
                                    class="font-mono transition py-4 duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100">
                                    <td
                                        class="text-center leading-5 whitespace-nowrap px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.no_po }}
                                    </td>
                                    <td
                                        class="text-center font-semibold leading-5 whitespace-nowrap px-4 py-1.5 text-slate-900 border-r">
                                        <span v-if="product.no_obc.substr(4,1) == 3" class="text-red-800">
                                            {{ product.no_obc }}
                                        </span>
                                        <span v-else class="text-blue-800">
                                            {{ product.no_obc }}
                                        </span>
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.type }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.start_rim }} - {{ product.end_rim }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-2 text-slate-700 border-r">
                                        <div v-if="product.status == 1">
                                            <span class="px-4 py-1.5 font-semibold text-yellow-900 bg-yellow-300 rounded-lg shadow drop-shadow-md">Sedang Di Periksa</span>
                                        </div>
                                        <div v-else-if="product.status == 0">
                                            <span class="px-4 py-1.5 rounded-lg shadow bg-slate-600 drop-shadow-md text-slate-50">Siap Di Periksa</span>
                                        </div>
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.created_at }}
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700">
                                        <Link :href="route('orderBesar.cetakLabel', {team : form.team, id : product.id})" class="flex justify-center px-6 py-2 mx-auto font-semibold drop-shadow-md shadow tracking-wide w-fit bg-blue-600 rounded-xl text-start text-cyan-50">Go</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="flex justify-center gap-6 mx-auto w-fit">
                <div class="flex gap-6 mt-10">
                <!-- Home Button -->
                <Link :href="route('dashboard')"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start drop-shadow-md shadow-md flex items-center gap-1.5">
                    <LucideIcon name="Home" class="w-6 h-6" />
                </Link>
            </div>
        </div>
    </div>
</ContentLayout>
</template>
