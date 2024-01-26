<script setup>
import { reactive } from "vue";
import Modal from "@/Components/Modal.vue";
import ContentLayout from "@/Layouts/ContentLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import axios from "axios";
import NavigateBackButton from "@/Components/NavigateBackButton.vue";

const form = reactive({
    po: "",
    obc: "",
    jml_lembar: "",
    jml_label: "",
    seri: "",
    npPeriksa : "",
});

const fetchData = () => {
    axios.get('/api/gen-perso-label/'+form.po)
        .then(res => {
            let total_label = Math.ceil(res.data.rencet / 500)

            form.obc    = res.data.no_obc
            form.jml_lembar = res.data.rencet+' / '+total_label+' Rim'
            form.jml_label  = total_label
        })
}

</script>

<template>
    <ContentLayout>
        <!-- Modal -->
        <!-- <Modal :show="showModal" @close="showModal = !showModal">
            <div
                class="px-8 py-4 bg-white rounded-lg shadow drop-shadow shadow-slate-300/25"
            >
                <h1
                    class="text-2xl font-bold text-center text-green-600 brightness-110"
                >
                    Label Berhasil Di Buat
                </h1>
            </div>
        </Modal> -->

        <!-- Form -->
        <div class="py-12">
            <form @submit.prevent="submit">
                <div
                    class="flex flex-col justify-center gap-6 mx-auto mt-20 w-fit"
                >
                    <!-- Nomor PO -->
                    <div>
                        <InputLabel
                            for="po"
                            value="Nomor PO"
                            class="text-4xl font-extrabold text-center"
                        />

                        <TextInput
                            id="po"
                            ref="po"
                            v-model="form.po"
                            @input="fetchData"
                            type="number"
                            class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                            autocomplete="po"
                            placeholder="Production Order"
                            required
                            autofocus
                        />
                    </div>
                    <div class="flex justify-between gap-6 mb-10 w-fit">
                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel
                                for="obc"
                                value="Nomor OBC"
                                class="text-4xl font-extrabold text-center"
                            />

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

                        <!-- Jumlah Lembar/Rim -->
                        <div>
                            <InputLabel
                                for="jml_lembar"
                                value="Lembar / Rim"
                                class="text-4xl font-extrabold text-center"
                            />

                            <TextInput
                                id="jml_lembar"
                                ref="jml_lembar"
                                v-model="form.jml_lembar"
                                type="text"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center bg-slate-200/70"
                                autocomplete="jml_lembar"
                                placeholder="Lembar/Rim"
                                min="1"
                                disabled
                            />
                        </div>

                        <!-- Jumlah Label -->
                        <div>
                            <InputLabel
                                for="jml_label"
                                value="Jumlah Label"
                                class="text-4xl font-extrabold text-center"
                            />

                            <TextInput
                                id="jml_label"
                                ref="jml_label"
                                v-model="form.jml_label"
                                type="number"
                                class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                                autocomplete="jml_label"
                                placeholder="Label"
                                min="1"
                                required
                            />
                        </div>
                    </div>

                    <!-- Nomor Rim -->
                    <!-- <h4 class="text-4xl font-semibold text-center">
                        Nomor RIM
                    </h4> -->

                    <!-- NP Pemeriksa -->
                    <div>
                        <InputLabel
                            for="npPeriksa"
                            value="NP Pemeriksa"
                            class="text-4xl font-extrabold text-center"
                        />

                        <TextInput
                            id="npPeriksa"
                            ref="npPeriksa"
                            v-model="form.npPeriksa"
                            type="text"
                            class="block w-full px-8 py-2 mt-2 text-2xl text-center"
                            autocomplete="jml_rim"
                            placeholder="NP"
                            required
                        />
                    </div>
                </div>
                <div class="flex justify-center gap-6 mx-auto w-fit">
                    <button
                        class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-11"
                    >
                        <!-- <Link
                            :href="route('p.products.create')"
                            class="text-2xl font-bold text-violet-50"
                            >Clear</Link
                        > -->
                    </button>
                    <button
                        type="submit"
                        class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11"
                    >
                        <span class="text-2xl font-bold text-yellow-50"
                            >Generate</span
                        >
                    </button>
                </div>
            </form>
        </div>
        <div class="flex justify-center w-full">
            <NavigateBackButton :link="route('nonPer.index')"/>
        </div>
    </ContentLayout>
</template>
