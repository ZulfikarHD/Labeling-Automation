<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { BarChart, Calendar, ClipboardCheck } from "lucide-vue-next";
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
    dataPo: {
        type: Object,
        required: true
    },
    inschiet: {
        type: String,
        required: true
    }
});

const form = useForm({
    id: props.dataPo.id,
    sum_rim: props.dataPo.sum_rim,
    start_rim: props.dataPo.start_rim,
    end_rim: props.dataPo.end_rim,
    inschiet: props.inschiet
});

// Watch effect for automatic rim calculation
watch(
    [() => form.start_rim, () => form.sum_rim],
    ([newStartRim, newSumRim], [oldStartRim, oldSumRim]) => {
        // Input validation to ensure start rim is positive
        if (newStartRim <= 0) {
            form.start_rim = 1;
        }

        // Calculate end rim only if values have changed
        if (newStartRim !== oldStartRim || newSumRim !== oldSumRim) {
            form.end_rim = parseInt(form.start_rim) + Math.ceil(parseInt(form.sum_rim)/2) - 1;
        }
    },
    { immediate: true }
);

const emit = defineEmits(['update:form']);

const handleSubmit = async () => {
    try {
        const formData = {
            id: form.id,
            sum_rim: form.sum_rim,
            start_rim: form.start_rim,
            end_rim: form.end_rim,
            inschiet: form.inschiet
        };

        emit('update:form', formData);
    } catch (error) {
        console.error("Error in RimInfoForm submit:", error);
    }
};
</script>

<template>
    <div class="mt-8">
        <h3 class="text-xl font-semibold text-slate-800 dark:text-white mb-6">
            Informasi Rim
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Sum Rim -->
            <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-3">
                    <BarChart class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                    <InputLabel
                        for="totalRim"
                        value="Total Rim"
                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                    />
                </div>
                <TextInput
                    id="totalRim"
                    v-model="form.sum_rim"
                    type="number"
                    class="bg-slate-100 text-center"
                    disabled
                />
            </div>

            <!-- Start Rim -->
            <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-3">
                    <Calendar class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                    <InputLabel
                        for="startRim"
                        value="Nomor Rim Awal"
                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                    />
                </div>
                <TextInput
                    v-model="form.start_rim"
                    id="startRim"
                    type="number"
                    min="1"
                    class="text-center"
                />
            </div>

            <!-- End Rim -->
            <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-3">
                    <Calendar class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    <InputLabel
                        for="endRim"
                        value="Nomor Rim Akhir"
                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                    />
                </div>
                <TextInput
                    id="endRim"
                    v-model="form.end_rim"
                    type="number"
                    disabled
                    class="bg-slate-100 text-center"
                />
            </div>

            <!-- Inschiet -->
            <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-3">
                    <ClipboardCheck class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                    <InputLabel
                        for="inschiet"
                        value="Inschiet"
                        class="text-lg font-semibold text-slate-700 dark:text-slate-300"
                    />
                </div>
                <TextInput
                    id="inschiet"
                    v-model="form.inschiet"
                    type="number"
                    class="text-center"
                />
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end mt-6">
            <button
                @click="handleSubmit"
                class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-300 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Simpan Perubahan Rim
            </button>
        </div>
    </div>
</template>
