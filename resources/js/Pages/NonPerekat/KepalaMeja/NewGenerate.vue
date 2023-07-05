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
    end_rim : '40',
});

// const showModal = () => {
//     this.show = !this.show
// };

function submit() {
  router.post(route('np.products.store'), form)
}
</script>

<template>
    <ContentLayout>
        <Modal :show="showModal" @close="showModal = !showModal">
            <div class="bg-white shadow drop-shadow shadow-slate-300/25 rounded-lg px-8 py-4">
                <h1 class="text-2xl text-green-600 brightness-110 font-bold text-center">Label Berhasil Di Buat</h1>
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
                    <button class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-11">
                        <Link :href="route('np.products.create')" class="text-2xl font-bold text-violet-50">Clear</Link>
                    </button>
                    <button type="submit" class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11">
                        <span class="text-2xl font-bold text-yellow-50">Generate</span>
                    </button>
                </div>
            </form>
        </div>
        <div class="flex justify-center w-full">
            <button type="btn" class="flex justify-center px-4 py-2 mx-auto w-fit bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl text-start mt-11" @click="showModal = !showModal">
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
