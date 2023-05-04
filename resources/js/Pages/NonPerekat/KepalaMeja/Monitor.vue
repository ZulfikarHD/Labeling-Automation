<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link,useForm } from '@inertiajs/vue3';

const form = useForm({
    team: '',
    po  : '',
    obc : '',
    seri: '',
});

</script>

<template>
    <ContentLayout>
        <div class="py-8">
            <!-- Detail Produk -->
            <!-- Team -->
            <div class="w-fit mx-auto mt-2">
                <InputLabel for="team" value="Team" class="text-2xl font-extrabold uppercase text-center"/>

                <TextInput
                    id="team"
                    ref="team"
                    v-model="form.team"
                    type="text"
                    class="mt-2 block w-full px-4 py-2 text-2xl text-center bg-slate-300"
                    autocomplete="team"
                    value="Pita Cukai NP 1"
                    disabled
                />
            </div>
            <div class="mt-10 mx-auto w-fit flex flex-row gap-8 justify-between">
                <!-- PO -->
                <div class="w-fit">
                    <InputLabel for="po" value="PO" class="text-2xl font-extrabold uppercase text-center"/>

                    <TextInput
                        id="po"
                        ref="po"
                        v-model="form.po"
                        type="number"
                        class="mt-2 block w-full px-0 py-2 text-2xl text-center bg-slate-300"
                        autocomplete="po"
                        value="3000160399"
                        disabled
                    />
                </div>

                <!-- OBC -->
                <div class="w-fit">
                    <InputLabel for="obc" value="OBC" class="text-2xl font-extrabold uppercase text-center"/>

                    <TextInput
                        id="obc"
                        ref="obc"
                        v-model="form.obc"
                        type="text"
                        class="mt-2 block w-full px-0 py-2 text-2xl text-center bg-slate-300"
                        autocomplete="obc"
                        value="PST 030001"
                        disabled
                    />
                </div>

                <!-- Seri -->
                <div class="w-fit">
                    <InputLabel for="seri" value="Seri" class="text-2xl font-extrabold uppercase text-center"/>

                    <TextInput
                        id="seri"
                        ref="seri"
                        v-model="form.seri"
                        type="number"
                        class="mt-2 block w-full px-0 py-2 text-2xl text-center bg-slate-300"
                        autocomplete="seri"
                        value="1"
                        disabled
                    />
                </div>
            </div>

            <!-- Loading State -->
            <div class="mt-6 mx-auto w-fit">
                <div class="flex justify-between w-full gap-12 items-start">
                    <!-- Row Lembar Kiri -->
                    <div class="flex flex-col gap-4 w-fit justify-center">
                        <!-- Header Lembar Kiri -->
                        <h3 class="text-3xl text-center font-medium text-slate-700">
                            Lembar Kiri
                        </h3>

                        <!-- State Kiri -->
                        <div class="grid grid-cols-5 gap-2">
                            <!-- 1 -->
                            <div class="rounded-md bg-gradient-to-br from-green-300 to-green-400 px-2 py-1 drop-shadow shadow shadow-green-400/30"
                                 v-for="n in 14">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center text-green-950">7418</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ n }}</span>
                                </div>
                            </div>
                            <!-- 1 -->
                            <div class="rounded-md bg-gradient-to-br from-yellow-300 to-yellow-400 px-2 py-1 drop-shadow shadow shadow-yellow-400/30"
                                 v-for="n in 8">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center text-yellow-950">7206</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ n+12 }}</span>
                                </div>
                            </div>
                            <!-- 1 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-300 to-slate-400 px-2 py-1 drop-shadow shadow"
                                 v-for="n in 18">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center text-slate-950">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ n+22 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="flex flex-col gap-4 w-fit justify-center mt-16">
                        <div class="flex gap-2 items-center">
                            <span class="rounded-md bg-gradient-to-br from-green-300 to-green-400 px-6 py-2 drop-shadow shadow shadow-green-400/30"></span>
                            <span class="text-slate-600 font-medium">Selesai Verif</span>
                        </div>
                        <div class="flex gap-2 items-center">
                            <span class="rounded-md bg-gradient-to-br from-yellow-300 to-yellow-400 px-6 py-2 drop-shadow shadow shadow-yellow-400/30"></span>
                            <span class="text-slate-600 font-medium">Proses Verif</span>
                        </div>
                        <div class="flex gap-2 items-center">
                            <span class="rounded-md bg-gradient-to-br from-slate-300 to-slate-400 px-6 py-2 drop-shadow shadow shadow-slate-400/30"></span>
                            <span class="text-slate-600 font-medium">Belum Verif</span>
                        </div>
                    </div>

                    <!-- Row Lembar Kanan -->
                    <div class="flex flex-col gap-4 w-fit justify-center">
                        <!-- Header Lembar Kanan -->
                        <h3 class="text-3xl text-center font-medium text-slate-700">
                            Lembar Kanan
                        </h3>

                        <!-- State Kiri -->
                        <div class="grid grid-cols-5 gap-2">
                            <!-- 1 -->
                            <div class="rounded-md bg-gradient-to-br from-green-300 to-green-400 px-2 py-1 drop-shadow shadow shadow-green-400/30"
                                 v-for="n in 16">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center text-green-950">7418</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ n }}</span>
                                </div>
                            </div>
                            <!-- 1 -->
                            <div class="rounded-md bg-gradient-to-br from-yellow-300 to-yellow-400 px-2 py-1 drop-shadow shadow shadow-yellow-400/30"
                                 v-for="n in 10">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center text-yellow-950">7206</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ n+16 }}</span>
                                </div>
                            </div>
                            <!-- 1 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-300 to-slate-400 px-2 py-1 drop-shadow shadow"
                                 v-for="n in 14">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center text-slate-950">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ n+26 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ContentLayout>
</template>
