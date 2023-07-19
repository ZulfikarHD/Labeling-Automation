<script setup>
import { reactive } from 'vue'
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue'
import { Link,router } from '@inertiajs/vue3';
const props = defineProps({
    showModal: Boolean,
});
const form = reactive({
    po  : '',
    obc : '',
    jml_rim : '',
    seri    : '',
    start_rim : '1',
    produk  : 'PCHT',
    end_rim : '40',
    team    : ''
});

// const showModal = () => {
//     this.show = !this.show
// };

function submit() {
  router.post(route('np.registerProducts.store'), form)
}
</script>

<template>
    <ContentLayout>
        <Modal :show="showModal" @close="showModal = !showModal">
            <div class="px-8 py-4 bg-white rounded-lg shadow drop-shadow shadow-slate-300/25">
                <h1 class="text-2xl font-bold text-center text-green-600 brightness-110">Label Berhasil Di Buat</h1>
            </div>
        </Modal>
        <div class="py-12">
            <form @submit.prevent="submit" method="post">
                <div class="flex flex-col justify-center gap-6 mx-auto mt-20 w-fit">
                    <!-- Nomor PO -->
                    <div>
                        <InputLabel for="po" value="Nomor PO" class="text-4xl font-extrabold text-center"/>

                        <TextInput
                            id="po"
                            ref="po"
                            v-model="form.po"
                            type="number"
                            class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                            autocomplete="po"
                            placeholder="Production Order"
                            required
                        />
                    </div>
                    <div class="flex justify-between gap-6 mb-10 w-fit">
                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel for="obc" value="Nomor OBC" class="text-4xl font-extrabold text-center"/>

                            <TextInput
                                id="obc"
                                ref="obc"
                                v-model="form.obc"
                                type="text"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                                autocomplete="obc"
                                placeholder="Order Bea Cukai"
                            required
                            />
                        </div>

                        <!-- Jumlah Rim -->
                        <div>
                            <InputLabel for="jml_rim" value="Jumlah Rim" class="text-4xl font-extrabold text-center"/>

                            <TextInput
                                id="jml_rim"
                                ref="jml_rim"
                                v-model="form.jml_rim"
                                type="number"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                                autocomplete="jml_rim"
                                placeholder="RIM"
                                min="1"
                            required
                            />
                        </div>
                    </div>
                    <h4 class="text-4xl font-semibold text-center">Nomor RIM</h4>
                    <div class="flex justify-between gap-6 w-fit">
                        <!-- Start RIM -->
                        <div>
                            <InputLabel for="start_rim" value="Dari" class="text-4xl font-extrabold text-center"/>

                            <TextInput
                                id="start_rim"
                                ref="start_rim"
                                v-model="form.start_rim"
                                type="number"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                                autocomplete="start_rim"
                                min="1"
                            />
                        </div>

                        <!-- End Rim -->
                        <div>
                            <InputLabel for="end_rim" value="Sampai" class="text-4xl font-extrabold text-center"/>

                            <TextInput
                                id="end_rim"
                                ref="end_rim"
                                v-model="form.end_rim"
                                type="number"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                                autocomplete="end_rim"
                                min="1"
                            />
                        </div>
                    </div>
                </div>
                <div class="flex justify-center gap-6 mx-auto w-fit">
                    <Link :href="route('np.registerProducts.create')" class="text-2xl font-bold text-violet-50 flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-11">
                        Clear
                    </Link>
                    <button type="submit" class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11">
                        <span class="text-2xl font-bold text-yellow-50">Buat Label</span>
                    </button>
                </div>
            </form>
        </div>
        <div class="flex justify-center w-full">
            <!-- Back Button -->
            <div class="flex gap-6">
                <Link :href="route('np.pic')"
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
    </ContentLayout>
</template>
