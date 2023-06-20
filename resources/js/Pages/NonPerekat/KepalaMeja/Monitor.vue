<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link,useForm } from '@inertiajs/vue3';

const props = defineProps({
    'spec' : Object,
    'dataRim' : Object
});

const form = useForm({
    team: '',
    po  : props.spec.no_po,
    obc : props.spec.no_obc,
    seri: '3',
});

</script>

<template>
    <ContentLayout>
        <div class="py-8">
            <!-- Detail Produk -->
            <!-- Team -->
            <div class="mx-auto mt-2 w-fit">
                <InputLabel for="team" value="Team" class="text-2xl font-extrabold text-center uppercase"/>

                <TextInput
                    id="team"
                    ref="team"
                    v-model="form.team"
                    type="text"
                    class="block w-full px-4 py-2 mt-2 text-2xl text-center bg-slate-300"
                    autocomplete="team"
                    value="Pita Cukai NP 1"
                    disabled
                />
            </div>
            <div class="flex flex-row justify-between gap-8 mx-auto mt-10 w-fit">
                <!-- PO -->
                <div class="w-fit">
                    <InputLabel for="po" value="PO" class="text-2xl font-extrabold text-center uppercase"/>

                    <TextInput
                        id="po"
                        ref="po"
                        v-model="form.po"
                        type="number"
                        class="block w-full px-0 py-2 mt-2 text-2xl text-center bg-slate-300"
                        autocomplete="po"
                        disabled
                    />
                </div>

                <!-- OBC -->
                <div class="w-fit">
                    <InputLabel for="obc" value="OBC" class="text-2xl font-extrabold text-center uppercase"/>

                    <TextInput
                        id="obc"
                        ref="obc"
                        v-model="form.obc"
                        type="text"
                        class="block w-full px-0 py-2 mt-2 text-2xl text-center bg-slate-300"
                        autocomplete="obc"
                        disabled
                    />
                </div>

                <!-- Seri -->
                <div class="w-fit">
                    <InputLabel for="seri" value="Seri" class="text-2xl font-extrabold text-center uppercase"/>

                    <TextInput
                        id="seri"
                        ref="seri"
                        v-model="form.seri"
                        type="number"
                        class="block w-full px-0 py-2 mt-2 text-2xl text-center bg-slate-300"
                        autocomplete="seri"
                        disabled
                    />
                </div>
            </div>

            <!-- Loading State -->
            <div class="mx-auto mt-6 w-fit">
                <div class="flex items-start justify-between w-full gap-12">
                    <!-- Row Lembar Kiri -->
                    <div class="flex flex-col justify-center gap-4 w-fit">
                        <!-- Header Lembar Kiri -->
                        <h3 class="text-3xl font-medium text-center text-slate-700">
                            Lembar Kiri
                        </h3>

                        <!-- State Kiri -->
                        <div class="grid grid-cols-5 gap-2">
                            <!-- 1 -->
                            <template  v-for="status in dataRim">
                                <template v-if="status.potongan == 'Kiri'">
                                    <!-- V-if Sudah Di Periksa -->
                                    <div v-if="status.finish != null" class="px-2 py-1 rounded-md shadow bg-gradient-to-br from-green-300 to-green-400 drop-shadow shadow-green-400/30">
                                        <div class="flex flex-col justify-center w-[4ch]">
                                            <span class="text-xs font-extrabold leading-5 text-center text-green-950">{{ status.np_users }}</span>
                                            <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ status.no_rim }}</span>
                                        </div>
                                    </div>

                                    <!-- V-if Sedang Di Periksa -->
                                    <div v-else-if="status.start != null && status.finish == null" class="px-2 py-1 rounded-md shadow bg-gradient-to-br from-yellow-300 to-yellow-400 drop-shadow shadow-yellow-400/30">
                                        <div class="flex flex-col justify-center w-[4ch]">
                                            <span class="text-xs font-extrabold leading-5 text-center text-green-950">{{ status.np_users }}</span>
                                            <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ status.no_rim }}</span>
                                        </div>
                                    </div>

                                    <!-- V-if Belum Periksa -->
                                    <div v-else class="px-2 py-1 rounded-md shadow bg-gradient-to-br from-slate-300 to-slate-400 drop-shadow shadow-slate-400/30">
                                        <div class="flex flex-col justify-center w-[4ch]">
                                            <span class="text-xs font-extrabold leading-5 text-center text-green-950">-</span>
                                            <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ status.no_rim }}</span>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="flex flex-col justify-center gap-4 mt-16 w-fit">
                        <div class="flex items-center gap-2">
                            <span class="px-6 py-2 rounded-md shadow bg-gradient-to-br from-green-300 to-green-400 drop-shadow shadow-green-400/30"></span>
                            <span class="font-medium text-slate-600">Selesai Verif</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-6 py-2 rounded-md shadow bg-gradient-to-br from-yellow-300 to-yellow-400 drop-shadow shadow-yellow-400/30"></span>
                            <span class="font-medium text-slate-600">Proses Verif</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-6 py-2 rounded-md shadow bg-gradient-to-br from-slate-300 to-slate-400 drop-shadow shadow-slate-400/30"></span>
                            <span class="font-medium text-slate-600">Belum Verif</span>
                        </div>
                    </div>

                    <!-- Row Lembar Kanan -->
                    <div class="flex flex-col justify-center gap-4 w-fit">
                        <!-- Header Lembar Kanan -->
                        <h3 class="text-3xl font-medium text-center text-slate-700">
                            Lembar Kanan
                        </h3>

                        <!-- State Kanan -->
                        <div class="grid grid-cols-5 gap-2">
                            <template  v-for="status in dataRim">
                                <template v-if="status.potongan == 'Kanan'">
                                    <!-- V-if Sudah Di Periksa -->
                                    <div v-if="status.finish != null" class="px-2 py-1 rounded-md shadow bg-gradient-to-br from-green-300 to-green-400 drop-shadow shadow-green-400/30">
                                        <div class="flex flex-col justify-center w-[4ch]">
                                            <span class="text-xs font-extrabold leading-5 text-center text-green-950">{{ status.np_users }}</span>
                                            <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ status.no_rim }}</span>
                                        </div>
                                    </div>

                                    <!-- V-if Sedang Di Periksa -->
                                    <div v-else-if="status.start != null && status.finish == null" class="px-2 py-1 rounded-md shadow bg-gradient-to-br from-yellow-300 to-yellow-400 drop-shadow shadow-yellow-400/30">
                                        <div class="flex flex-col justify-center w-[4ch]">
                                            <span class="text-xs font-extrabold leading-5 text-center text-green-950">{{ status.np_users }}</span>
                                            <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ status.no_rim }}</span>
                                        </div>
                                    </div>

                                    <!-- V-if Belum Periksa -->
                                    <div v-else class="px-2 py-1 rounded-md shadow bg-gradient-to-br from-slate-300 to-slate-400 drop-shadow shadow-slate-400/30">
                                        <div class="flex flex-col justify-center w-[4ch]">
                                            <span class="text-xs font-extrabold leading-5 text-center text-green-950">-</span>
                                            <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">{{ status.no_rim }}</span>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-center w-full">
            <button type="btn" class="flex justify-center px-4 py-2 mx-auto w-fit bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl text-start mt-11">
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
