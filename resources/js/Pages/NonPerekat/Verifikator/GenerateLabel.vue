<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link,useForm } from '@inertiajs/vue3';

const form = useForm({
    po  : '',
    obc : '',
    team    : '',
    seri    : '',
    jml_rim : '',
    lbr_ptg : '',
});

</script>

<template>
    <ContentLayout>
        <div class="py-8">
            <form>
                <div class="w-fit mx-auto flex flex-col gap-6 justify-center">
                    <!-- Team -->
                    <div class="mx-auto mb-7">
                        <InputLabel for="team" value="Team" class="text-2xl  font-extrabold  text-center"/>

                        <TextInput
                            id="team"
                            ref="team"
                            v-model="form.team"
                            type="text"
                            class="mt-2 block w-fit px-4 py-2 text-lg text-center bg-slate-300 shadow drop-shadow"
                            autocomplete="team"
                            value="Non Personal 1"
                            disabled
                        />
                    </div>
                    <div class="flex justify-between gap-6 w-fit mb-6">
                        <!-- PO -->
                        <div>
                            <InputLabel for="po" value="Nomor PO" class="text-2xl  font-extrabold  text-center"/>

                            <TextInput
                                id="po"
                                ref="po"
                                v-model="form.po"
                                type="number"
                                class="mt-2 block w-full px-4 py-2 text-lg text-center bg-slate-300 shadow drop-shadow"
                                autocomplete="po"
                                value="3000160783"
                                disabled
                            />
                        </div>

                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel for="obc" value="Nomor OBC" class="text-2xl  font-extrabold  text-center"/>

                            <TextInput
                                id="obc"
                                ref="obc"
                                v-model="form.obc"
                                type="text"
                                class="mt-2 block w-full px-4 py-2 text-lg text-center bg-slate-300 shadow drop-shadow"
                                autocomplete="obc"
                                value="SID215509"
                                disabled
                            />
                        </div>

                        <!-- Seri -->
                        <div>
                            <InputLabel for="seri" value="Seri" class="text-2xl  font-extrabold  text-center "/>

                            <TextInput
                                id="seri"
                                ref="seri"
                                v-model="form.seri"
                                type="number"
                                class="mt-2 block w-full px-4 py-2 text-lg text-center bg-slate-300 shadow drop-shadow"
                                autocomplete="seri"
                                value="1"
                            />
                        </div>
                    </div>

                    <div class="flex justify-between gap-6 w-fit mx-auto">
                        <!-- Start RIM -->
                        <div>
                            <InputLabel for="jml_rim" value="Jumlah RIM" class="text-2xl  font-extrabold  text-center"/>

                            <TextInput
                                id="jml_rim"
                                ref="jml_rim"
                                v-model="form.jml_rim"
                                type="number"
                                class="mt-2 block w-full px-4 py-2 text-lg text-center"
                                autocomplete="jml_rim"
                                value="1"
                            />
                        </div>

                        <!-- End Rim -->
                        <div>
                            <InputLabel for="lbr_ptg" value="Lembar Potong" class="text-2xl  font-extrabold  text-center"/>

                            <TextInput
                                id="lbr_ptg"
                                ref="lbr_ptg"
                                v-model="form.lbr_ptg"
                                type="text"
                                class="mt-2 block w-full px-4 py-2 text-lg text-center"
                                autocomplete="lbr_ptg"
                                value="Kiri"
                                disabled
                            />
                        </div>
                    </div>
                </div>

                <!-- Keypad -->
                <div class="grid grid-cols-2 gap-6 w-fit mx-auto">
                    <div class="grid grid-cols-3 gap-3 mt-10">
                        <button type="btn" class="w-full px-2 py-4 bg-gradient-to-r from-sky-300 to-sky-400 shadow shadow-sky-400/30 drop-shadow rounded-xl text-start mx-auto flex justify-center text-sky-50 font-extrabold hover:brightness-90 transition ease-in-out duration-150" v-for="n in 9">{{ n }}</button>
                        <button type="btn" class="w-full px-2 py-4 bg-gradient-to-r from-sky-300 to-sky-400 shadow shadow-sky-400/30 drop-shadow rounded-xl text-start mx-auto flex justify-center text-sky-50 font-extrabold hover:brightness-90 transition ease-in-out duration-150 col-span-2">0</button>
                        <button type="btn" class="w-full px-2 py-4 bg-gradient-to-r from-sky-300 to-sky-400 shadow shadow-sky-400/30 drop-shadow rounded-xl text-start mx-auto flex justify-center text-sky-50 font-extrabold hover:brightness-90 transition ease-in-out duration-150">Backspace</button>
                    </div>
                    <div class="grid grid-cols-2 mt-10">
                        <button type="btn" class="w-2/3 px-2 py-4 bg-gradient-to-r from-emerald-400 to-emerald-300 shadow shadow-emerald-400/30 rounded-xl text-start mx-auto flex justify-center text-green-50 font-extrabold hover:brightness-90 transition ease-in-out duration-150">
                            <div class="flex flex-col justify-center items-center gap-4 h-full px-6">
                                <div class="flex gap-2">
                                    <span class="rounded-full bg-emerald-50 p-2"></span>
                                </div>
                                <span class="font-extrabold uppercase">Kiri</span>
                            </div>
                        </button>
                        <button type="btn" class="w-2/3 px-2 py-4 bg-gradient-to-r from-emerald-300 to-emerald-400 shadow shadow-emerald-400/30 rounded-xl text-start mx-auto flex justify-center text-green-50 font-extrabold hover:brightness-90 transition ease-in-out duration-150">
                            <div class="flex flex-col justify-center items-center gap-4 h-full px-6">
                                <div class="flex gap-2">
                                    <span class="rounded-full bg-emerald-50 p-2"></span>
                                    <span class="rounded-full bg-emerald-50 p-2"></span>
                                </div>
                                <span class="font-extrabold uppercase">Kanan</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-center w-fit mx-auto gap-6">
                    <button class="w-fit px-4 py-4 bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-8 mx-auto flex justify-center">
                        <Link :href="route('np-kepala-newGen')" class="text-violet-50 text-lg font-bold">Clear</Link>
                    </button>
                    <button class="w-fit px-4 py-4 bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-8 mx-auto flex justify-center">
                        <Link :href="route('np-kepala-newGen')" class="text-yellow-50 text-lg font-bold">Generate</Link>
                    </button>
                </div>
            </form>
        </div>
    </ContentLayout>
</template>
