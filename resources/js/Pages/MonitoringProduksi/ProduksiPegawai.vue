<script setup lang="ts">
import InputLabel from '@/Components/InputLabel.vue';
import TableVerifikasiPegawai from '@/Components/TableVerifikasiPegawai.vue';
import TextInput from '@/Components/TextInput.vue';
import ContentLayout from '@/Layouts/ContentLayout.vue';
import { useForm } from '@inertiajs/vue3';
import {ref} from 'vue';

const props = defineProps({
    teams : Object
})

const getDate = new Date()

const form = useForm({
    date : getDate.toISOString().substr(0, 10)
})
const today = ref(form.date)

</script>
<template>
    <ContentLayout>
        <div class="px-4 py-6 mx-auto">
            <div class="px-10 mx-4 mb-4 ml-28">
                <InputLabel for="dateFilter" class="pb-2 pl-1 text-lg font-medium text-gray-700">Tanggal Produksi</InputLabel>
                <TextInput id="dateFilter" type="date" v-model="form.date" class="border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-300"/>
            </div>
            <div class="w-full flex flex-wrap justify-center items-baseline gap-6 mb-10">
                <!-- Team Verifikasi-->
                <template v-for="team in props.teams" :key="'team'+team.id">
                    <div class="flex flex-col justify-center gap-2 bg-white shadow-md rounded-lg p-4">
                        <h3 class="text-xl font-semibold text-slate-950 text-center mb-2">Team {{ team.workstation }}</h3>
                        <TableVerifikasiPegawai :team="team.id" :date="form.date" />
                    </div>
                </template>
            </div>

            <!-- All Team -->
            <div class="flex flex-col justify-center">
                <h3 class="text-xl font-semibold text-slate-950 text-center mb-2">All Team</h3>
                <TableVerifikasiPegawai :team="0" :date="form.date" />
            </div>
        </div>
    </ContentLayout>
</template>
