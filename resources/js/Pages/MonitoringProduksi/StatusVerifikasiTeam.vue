<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import NavigateBackButton from '@/Components/NavigateBackButton.vue'
import { Link,useForm } from '@inertiajs/vue3';

const props = defineProps({
    spec: Object,
    dataRim : Object,
    team: Object,
});

const form = useForm({
    team: props.team.workstation,
    po  : props.spec.no_po,
    obc : props.spec.no_obc,
    seri: props.spec.no_obc.substr(4,1),
});

</script>
<template>
    <ContentLayout>
        <div class="py-8">
            <!-- Detail Produk -->
            <!-- Team -->
            <div class="mx-auto mt-2 w-fit">
                <InputLabel for="team" value="Tim" class="text-2xl font-extrabold text-center uppercase"/>

                <TextInput
                    id="team"
                    ref="team"
                    v-model="form.team"
                    type="text"
                    class="block w-full px-4 py-2 mt-2 text-2xl text-center bg-slate-300 rounded-md shadow-md"
                    autocomplete="team"
                    disabled
                />
            </div>
            <div class="flex flex-col md:flex-row justify-between gap-8 mx-auto mt-10 w-fit">
                <!-- PO -->
                <div class="w-fit">
                    <InputLabel for="po" value="PO" class="text-2xl font-extrabold text-center uppercase"/>

                    <TextInput
                        id="po"
                        ref="po"
                        v-model="form.po"
                        type="number"
                        class="block w-full px-4 py-2 mt-2 text-2xl text-center bg-slate-300 rounded-md shadow-md"
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
                        class="block w-full px-4 py-2 mt-2 text-2xl text-center bg-slate-300 rounded-md shadow-md"
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
                        class="block w-full px-4 py-2 mt-2 text-2xl text-center bg-slate-300 rounded-md shadow-md"
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
                        <div class="grid grid-cols-6 gap-2">
                            <template v-for="status in dataRim">
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
                        <div class="grid grid-cols-6 gap-2">
                            <template v-for="status in dataRim">
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
            <div class="flex gap-6 mt-10 ml-12">
                <Link :href="route('dashboard')"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start drop-shadow-md shadow-md flex items-center gap-1.5">
                <LucideIcon name="arrow-right" class="w-6 h-6" />
                </Link>
            </div>
        </div>
    </ContentLayout>
</template>
