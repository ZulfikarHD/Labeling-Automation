<script setup>
import { inject } from 'vue';
import ContentLayout from '@/Layouts/ContentLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

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
                                        Group
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Generated At
                                    </th>
                                    <th scope="col"
                                        class="pt-6 pb-1.5  px-6 leading-tight border-slate-300 dark:border-slate-500 text-center">
                                        Start
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
                                        {{ product.no_obc }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.assigned_team }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.created_at }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.status }}
                                    </td>
                                    <td
                                        class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700 border-r">
                                        {{ product.status }}
                                    </td>
                                    <td class="text-center leading-5 whitespace-nowrap text-sm px-4 py-1.5 text-slate-700">
                                        <div class="flex justify-center gap-2">
                                            <Link :href="route('nonPer.nonPersonal.printLabel.index', product.id)"
                                                class="font-bold text-blue-400 transition duration-150 ease-in-out hover:text-blue-700">
                                            Info</Link>
                                            <Link :href="route('nonPer.nonPersonal.generateLabels.index')"
                                                class="font-bold text-green-400 transition duration-150 ease-in-out hover:text-green-700">
                                            Edit</Link>
                                            <button type="button"
                                                @click.prevent="deleteModal = !deleteModal; form.id = product.id; form.po = product.no_po"
                                                class="font-bold text-red-400 transition duration-150 ease-in-out hover:text-red-700">Delete</button>
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
            <!-- Back Button -->
            <div class="flex gap-6">
                <Link :href="route('nonPer.nonPersonal.pic.index')"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start  drop-shadow-md shadow-md flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" />
                </svg>
                Back
                </Link>
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
