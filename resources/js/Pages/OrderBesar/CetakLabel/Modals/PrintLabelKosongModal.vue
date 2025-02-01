<template>
    <Modal :show="show" @close="$emit('close')">
        <form @submit.prevent="printLabel" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex flex-col gap-4">
                <!-- Modal header -->
                <h1 class="text-xl font-bold text-center text-gray-800 dark:text-gray-200">
                    Print Label Manual
                </h1>
                <p class="text-red-600 dark:text-red-400 text-center">
                    Label yang dibuat disini tidak tersimpan di database.
                </p>

                <!-- Input fields -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Left side inputs -->
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="npPetugas" value="NP Petugas 1" class="mb-1 text-sm dark:text-gray-300" />
                            <TextInput
                                id="npPetugas"
                                type="text"
                                v-model="form.npPetugas"
                                class="w-full uppercase text-sm dark:bg-gray-700 dark:text-gray-300"
                                maxlength="4"
                                required
                            />
                        </div>
                        <div>
                            <InputLabel for="npPetugas2" value="NP Petugas 2" class="mb-1 text-sm dark:text-gray-300" />
                            <TextInput
                                id="npPetugas2"
                                type="text"
                                v-model="form.npPetugas2"
                                class="w-full uppercase text-sm dark:bg-gray-700 dark:text-gray-300"
                                maxlength="4"
                            />
                        </div>
                    </div>

                    <!-- Right side inputs -->
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="jmlLabel" value="Jumlah Label" class="mb-1 text-sm dark:text-gray-300" />
                            <TextInput
                                id="jmlLabel"
                                type="number"
                                v-model="form.jml_label"
                                class="w-full text-sm dark:bg-gray-700 dark:text-gray-300"
                                min="1"
                                max="100"
                                required
                            />
                        </div>
                        <div>
                            <InputLabel for="dataRim" value="Potongan" class="mb-1 text-sm dark:text-gray-300" />
                            <select
                                id="dataRim"
                                v-model="form.dataRim"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300"
                            >
                                <option value="">-</option>
                                <option value="Kiri">Kiri (*)</option>
                                <option value="Kanan">Kanan (**)</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="lembar" value="Lembar" class="mb-1 text-sm dark:text-gray-300" />
                            <TextInput
                                id="lembar"
                                type="number"
                                v-model="form.lembar"
                                class="w-full text-sm dark:bg-gray-700 dark:text-gray-300"
                                min="1"
                                max="500"
                                required
                            />
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex justify-end gap-3 mt-4">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        :class="[
                            'px-4 py-2 text-white bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 rounded-lg transition-colors',
                            loading ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                    >
                        {{ loading ? 'Memproses...' : 'Print' }}
                    </button>
                </div>
            </div>
        </form>
    </Modal>
</template>

<script setup>
import { reactive, ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { batchSingleLabel } from '@/Components/PrintPages/index';

const props = defineProps({
    show: Boolean,
    obc: String,
    colorObc: String,
    team: Number,
});

const emit = defineEmits(['close', 'success', 'error']);

const loading = ref(false);

const form = reactive({
    dataRim: '',
    npPetugas: '',
    npPetugas2: '',
    lembar: 500,
    jml_label: 1,
});

const printLabel = async () => {
    try {
        loading.value = true;
        const printLabel = batchSingleLabel(
            props.obc,
            '',
            props.colorObc,
            form.dataRim,
            form.npPetugas,
            form.npPetugas2,
            form.jml_label,
            form.lembar,
        );

        emit('print', printLabel);
        resetForm();
        emit('success');
        emit('close');
    } catch (error) {
        console.error('Error:', error);
        emit('error', 'Gagal mencetak label');
    } finally {
        loading.value = false;
    }
};

const resetForm = () => {
    form.npPetugas = '';
    form.npPetugas2 = '';
    form.jml_label = 1;
    form.dataRim = '';
    form.lembar = 500;
};
</script>
