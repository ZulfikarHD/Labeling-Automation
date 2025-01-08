<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import {
    FileText,
    Layers,
    Hash,
    BarChart,
    Users
} from "lucide-vue-next";

const props = defineProps({
    dataPo: {
        type: Object,
        required: true
    },
    specPo: {
        type: Object,
        required: true
    },
    teamList: {
        type: Object,
        required: true
    }
});

const form = useForm({
    id: props.dataPo.id,
    no_po: props.dataPo.no_po,
    no_obc: props.dataPo.no_obc,
    type: props.dataPo.type,
    seri: props.specPo.seri ?? '',
    personal: props.specPo.type ?? '',
    jumlah_cetak: props.specPo.rencet ?? '',
    assigned_team: props.dataPo.assigned_team,
    sum_rim: props.dataPo.sum_rim,
    start_rim: props.dataPo.start_rim,
    end_rim: props.dataPo.end_rim,
});

const emit = defineEmits(['update:form']);

const handleSubmit = async () => {
    try {
       const formData = {
            id: form.id,
            no_po: form.no_po,
            no_obc: form.no_obc,
            type: form.type,
            assigned_team: form.assigned_team
        };

        emit('update:form', formData);
    } catch (error) {
        console.error("Error in MainInfoForm submit:", error);
    }
};
</script>

<template>
   <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 mb-8 border border-slate-100 dark:border-slate-700">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-slate-900 dark:text-white text-center tracking-tight">
                Edit Order
                <span class="text-blue-600 dark:text-blue-400">{{ form.no_po }}</span>
            </h1>
            <p class="mt-2 text-slate-600 dark:text-slate-400 text-center">
                Edit settingan label produksi
            </p>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-8">
            <!-- Team Assignment -->
            <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-3">
                    <Users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    <InputLabel value="Tim yang Ditugaskan" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                </div>
                <select
                    id="team"
                    v-model="form.assigned_team"
                    class="mt-1 w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 text-center"
                >
                    <option v-for="team in teamList" :key="team.id" :value="team.id">
                        {{ team.workstation }}
                    </option>
                </select>
            </div>

            <!-- Main Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- PO Number -->
                <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                    <div class="flex items-center justify-center gap-3 mb-3">
                        <FileText class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        <InputLabel value="Nomor PO" for="noPo" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                    </div>
                    <TextInput v-model="form.no_po" id="noPo" type="text" class="text-center bg-slate-100 font-semibold" disabled />
                </div>

                <!-- OBC Number -->
                <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                    <div class="flex items-center justify-center gap-3 mb-3">
                        <Layers class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                        <InputLabel for="obc" value="Nomor OBC" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                    </div>
                    <TextInput id="obc" v-model="form.no_obc" type="text" class="text-center font-bold" />
                </div>

                <!-- Type -->
                <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                    <div class="flex items-center justify-center gap-3 mb-3">
                        <Hash class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        <InputLabel for="produk" value="Produk" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                    </div>
                    <TextInput id="produk" v-model="form.type" type="text" class="text-center" />
                </div>

                <!-- Seri -->
                <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <Hash class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                        <InputLabel for="seri" value="Seri" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                    </div>
                    <TextInput id="seri" v-model="form.seri" type="text" class="bg-slate-100 text-center" disabled />
                </div>

                <!-- Personal -->
                <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <Layers class="w-5 h-5 text-pink-600 dark:text-pink-400" />
                        <InputLabel for="personal" value="Personal" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                    </div>
                    <TextInput id="personal" v-model="form.personal" type="text" class="bg-slate-100 text-center" disabled />
                </div>

                <!-- Jumlah Cetak -->
                <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <BarChart class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                        <InputLabel for="jumlah_cetak" value="Jumlah Cetak" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                    </div>
                    <TextInput id="jumlah_cetak" v-model="form.jumlah_cetak" type="number" class="bg-slate-100 text-center" disabled />
                </div>
            </div>
        </form>

        <div class="flex justify-end mt-6">
            <button
                @click="handleSubmit"
                class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-300 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Simpan Perubahan
            </button>
        </div>
    </div>
</template>
