<script setup>
import { inject } from 'vue';
import ContentLayout from '@/Layouts/ContentLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import NavigateBackButton from '@/Components/NavigateBackButton.vue';

const swal = inject('$swal');

const props = defineProps({
    products: Object,
    deleteModal: {
        type: Boolean,
        default: false,
    },
    editModal: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    id: '',
    po: '',
})

const deleteOrder = () => {
    router.delete(route('nonPer.nonPersonal.listPo.destroy',form.po), {
        onSuccess: () => {
            // @ts-ignore
            swal.fire({
                icon: 'success',
                title: 'Success',
                text : 'Order ' + form.po + ' Berhasil Di Hapus',
            });
            form.reset();
        }
    });
    // form.post(route('create-user.store'), {
    //     onSuccess: () => {
    //         form.reset();
    //         // @ts-ignore
    //         swal.fire({
    //             icon: 'success',
    //             title: 'Success',
    //             text : 'User Berhasil Di Buat',
    //         });
    //     }
    // });
}
</script>

<template>
    <!-- Delete Modal -->
    <Modal :show="deleteModal" @close="deleteModal = !deleteModal">
        <div class="flex flex-col gap-4 px-8 py-6">
            <div class="flex gap-5 ">
                <div class="p-2 bg-red-100 rounded-full h-fit">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6 text-red-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="flex flex-col gap-2">
                    <h3 class="text-lg font-semibold leading-normal text-slate-900"><i>Delete</i> Order {{ form.po }} ?</h3>
                    <p class="text-base leading-normal text-slate-500">Peringatan, order yang di <i>delete</i> akan menghapus
                        seluruh data label yang ada, baik yang sudah di kerjakan ataupun belum,
                        data yang di <i>delete</i> tidak akan bisa dikembalikan.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-2 px-8 py-3 -mx-8 -mb-6 bg-slate-100">
                <button class="px-4 py-1.5 font-medium text-slate-500 hover:text-slate-600 transition ease-in-out duration-200" @click.prevent="deleteModal = !deleteModal">Batal</button>
                <button type="button" @click.prevent="deleteOrder"
                    class="px-4 py-1.5 font-medium bg-red-600 text-red-50 rounded-md drop-shadow hover:bg-red-700 transition duration-200 ease-in-out">
                    Hapus
                </button>
            </div>
        </div>
    </Modal>

    <!-- Delete Modal -->
    <Modal :show="deleteModal" @close="deleteModal = !deleteModal">
        <div class="flex flex-col gap-4 px-8 py-6">
            <div class="flex gap-5 ">
                <div class="p-2 bg-red-100 rounded-full h-fit">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6 text-red-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="flex flex-col gap-2">
                    <h3 class="text-lg font-semibold leading-normal text-slate-900"><i>Delete</i> Order {{ form.po }} ?</h3>
                    <p class="text-base leading-normal text-slate-500">Peringatan, order yang di <i>delete</i> akan menghapus
                        seluruh data label yang ada, baik yang sudah di kerjakan ataupun belum,
                        data yang di <i>delete</i> tidak akan bisa dikembalikan.
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-2 px-8 py-3 -mx-8 -mb-6 bg-slate-100">
                <button class="px-4 py-1.5 font-medium text-slate-500 hover:text-slate-600 transition ease-in-out duration-200" @click.prevent="deleteModal = !deleteModal">Batal</button>
                <button type="button" @click.prevent="deleteOrder"
                    class="px-4 py-1.5 font-medium bg-red-600 text-red-50 rounded-md drop-shadow hover:bg-red-700 transition duration-200 ease-in-out">
                    Hapus
                </button>
            </div>
        </div>
    </Modal>

    <ContentLayout>
        <div class="py-12">
            <!-- Header -->
            <h3 class="my-10 text-3xl font-extrabold text-center uppercase text-slate-700">List Generated Labels</h3>

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
                                        class="pt-6 pb-1.5 px-4 leading-tight text-center border-slate-300 dark:border-slate-500">
                                        No
                                    </th>
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
                                        Team
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Generated At
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Finish
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in products"
                                    class="font-mono transition duration-300 ease-in-out border-b border-slate-300 text-slate-800 hover:bg-slate-400 hover:bg-opacity-10 dark:text-slate-100">
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 font-medium text-slate-950 border-r">
                                        {{ product.id }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.no_po }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        <span v-if="product.no_obc.substr(4,1) == 3" class="text-red-800">
                                            {{ product.no_obc }}
                                        </span>
                                        <span v-else class="text-blue-800">
                                            {{ product.no_obc }}
                                        </span>
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.workstation }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.created_at }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        <div v-if="product.status == 1">
                                            <span class="px-2 text-xs font-semibold text-yellow-900 bg-yellow-300 rounded-lg shadow drop-shadow-md">Sedang Di Periksa</span>
                                        </div>
                                        <div v-else-if="product.status == 0">
                                            <span class="px-2 text-xs rounded-lg shadow bg-slate-600 drop-shadow-md text-slate-50">Siap Di Periksa</span>
                                        </div>
                                        <div v-else-if="product.status == 2">
                                            <span class="px-2 text-xs rounded-lg shadow bg-green-600 drop-shadow-md text-green-50">Selesai Periksa</span>
                                        </div>
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        <span v-if="product.status == 2">
                                            {{ product.updated_at }}
                                        </span>
                                        <span v-else>
                                            -
                                        </span>
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 brightness-110">
                                        <div class="flex justify-center gap-2">
                                            <!-- Monitor -->
                                            <Link :href="route('nonPer.nonPersonal.listPo.show', product.no_po)"
                                                class="font-bold text-blue-600 transition duration-150 ease-in-out hover:text-blue-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z" clip-rule="evenodd" />
                                                </svg>
                                        </Link>
                                        <!-- Print Ulang -->
                                            <Link :href="route('nonPer.nonPersonal.printLabel.index',{workstation : product.assigned_team, id : product.id})"
                                                class="font-bold text-indigo-700 transition duration-150 ease-in-out hover:text-blue-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                    <path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 1.984A1.75 1.75 0 0 1 14.084 19H5.915a1.75 1.75 0 0 1-1.73-2.016L4.492 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5Zm8.5 3.397a41.533 41.533 0 0 0-7 0V2.75a.25.25 0 0 1 .25-.25h6.5a.25.25 0 0 1 .25.25v3.397ZM6.608 12.5a.25.25 0 0 0-.247.212l-.693 4.5a.25.25 0 0 0 .247.288h8.17a.25.25 0 0 0 .246-.288l-.692-4.5a.25.25 0 0 0-.247-.212H6.608Z" clip-rule="evenodd" />
                                                </svg>
                                            </Link>
                                            <!--  Edit -->
                                            <Link :href="route('nonPer.nonPersonal.generateLabels.index')"
                                                class="font-bold text-green-600 transition duration-150 ease-in-out hover:text-green-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                    <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" />
                                                    <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z" />
                                                </svg>
                                            </Link>
                                            <button type="button"
                                                @click.prevent="deleteModal = !deleteModal; form.id = product.id; form.po = product.no_po"
                                                class="font-bold text-red-600 transition duration-150 ease-in-out hover:text-red-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                                    <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-center w-full">
            <div class="flex gap-6">
                <!-- Back Button -->
                <NavigateBackButton></NavigateBackButton>

                <!-- Home Button -->
                <Link :href="route('dashboard')"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3    px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start drop-shadow-md shadow-md flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                    <path
                        d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                    <path
                        d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
                </svg>
                </Link>
            </div>
        </div>
    </ContentLayout></template>
