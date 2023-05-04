<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link,useForm } from '@inertiajs/vue3';

const form = useForm({
    po  : '',
    obc : '',
    jml_rim : '',
    seri    : '',
    start_rim : '',
    end_rim : '',
});

</script>

<template>
    <ContentLayout>
        <div class="py-12">
            <form>
                <div class="w-fit mx-auto mt-20 flex flex-col gap-6 justify-center">
                    <!-- Nomor PO -->
                    <div>
                        <InputLabel for="po" value="Nomor PO" class="text-4xl font-extrabold  text-center"/>

                        <TextInput
                            id="po"
                            ref="po"
                            v-model="form.po"
                            type="number"
                            class="mt-2 block w-full px-8 py-2 text-2xl text-center"
                            autocomplete="po"
                            placeholder="Production Order"
                        />
                    </div>
                    <div class="flex justify-between gap-6 w-fit mb-10">
                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel for="obc" value="Nomor OBC" class="text-4xl font-extrabold  text-center"/>

                            <TextInput
                                id="obc"
                                ref="obc"
                                v-model="form.obc"
                                type="text"
                                class="mt-2 block w-full px-8 py-2 text-2xl text-center"
                                autocomplete="obc"
                                placeholder="Order Bea Cukai"
                            />
                        </div>

                        <!-- Jumlah Rim -->
                        <div>
                            <InputLabel for="jml_rim" value="Jumlah Rim" class="text-4xl font-extrabold  text-center"/>

                            <TextInput
                                id="jml_rim"
                                ref="jml_rim"
                                v-model="form.jml_rim"
                                type="number"
                                class="mt-2 block w-full px-8 py-2 text-2xl text-center"
                                autocomplete="jml_rim"
                                placeholder="RIM"
                            />
                        </div>
                    </div>
                    <h4 class="text-4xl font-semibold text-center">Nomor RIM</h4>
                    <div class="flex justify-between gap-6 w-fit">
                        <!-- Start RIM -->
                        <div>
                            <InputLabel for="start_rim" value="Dari" class="text-4xl font-extrabold  text-center"/>

                            <TextInput
                                id="start_rim"
                                ref="start_rim"
                                v-model="form.start_rim"
                                type="number"
                                class="mt-2 block w-full px-8 py-2 text-2xl text-center"
                                autocomplete="start_rim"
                                value="1"
                            />
                        </div>

                        <!-- End Rim -->
                        <div>
                            <InputLabel for="end_rim" value="Sampai" class="text-4xl font-extrabold  text-center"/>

                            <TextInput
                                id="end_rim"
                                ref="end_rim"
                                v-model="form.end_rim"
                                type="number"
                                class="mt-2 block w-full px-8 py-2 text-2xl text-center"
                                autocomplete="end_rim"
                                value="40"
                            />
                        </div>
                    </div>
                </div>
                <div class="flex justify-center w-fit mx-auto gap-6">
                    <button class="w-fit px-8 py-4 bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-11 mx-auto flex justify-center">
                        <Link :href="route('np-kepala-newGen')" class="text-violet-50 text-2xl font-bold">Clear</Link>
                    </button>
                    <button class="w-fit px-8 py-4 bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11 mx-auto flex justify-center">
                        <Link :href="route('np-kepala-newGen')" class="text-yellow-50 text-2xl font-bold">Generate</Link>
                    </button>
                </div>
            </form>
        </div>
    </ContentLayout>
</template>
