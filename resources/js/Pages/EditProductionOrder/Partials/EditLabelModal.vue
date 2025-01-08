<script setup>
import { ref, computed, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { X, Trash2 } from "lucide-vue-next";
import axios from "axios";
import Swal from "sweetalert2";

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    selectedLabel: {
        type: Object,
        required: true
    },
    teamList: {
        type: Object,
        required: true
    },
    defaultTeam: {
        type: [String, Number],
        required: true
    }
});

const emit = defineEmits(['update:show', 'labelUpdated']);

// Form initialization
const form = useForm({
    id: '',
    np_users: '',
    np_user_p2: '',
    no_rim: '',
    start: '',
    finish: '',
    status: '',
    team: '',
    potongan: ''
});

// Watch for selected label changes
watch(() => props.selectedLabel, (newLabel) => {
    if (newLabel) {
        form.id = newLabel.id;
        form.np_users = newLabel.np_users ?? '';
        form.np_user_p2 = newLabel.np_user_p2 ?? '';
        form.no_rim = newLabel.no_rim ?? '';
        form.start = newLabel.start ?? '';
        form.finish = newLabel.finish ?? '';
        form.status = newLabel.status ?? '';
        form.team = newLabel.team ?? props.defaultTeam;
        form.potongan = newLabel.potongan ?? '';
    }
}, { immediate: true });

// Computed properties for status
const isCompleted = computed(() => {
    return form.finish && form.start && form.np_users;
});

const isInProgress = computed(() => {
    return form.start && !form.finish && (form.np_users || form.np_user_p2);
});

const statusText = computed(() => {
    if (isCompleted.value) return 'Selesai';
    if (isInProgress.value) return 'Sedang Dikerjakan';
    return 'Belum Dikerjakan';
});

// Methods
const clearForm = () => {
    form.np_users= '';
    form.np_user_p2 = '';
    form.start = '';
    form.finish = '';
};

const updateLabel = async () => {
    try {
        // Set status based on conditions
        if (isCompleted.value) {
            form.status = 'completed';
        } else if (isInProgress.value) {
            form.status = 'in_progress';
        } else {
            form.status = 'pending';
        }

        await axios.post('/api/production-order/update-label', form);
        emit('update:show', false);
        emit('labelUpdated');

        await Swal.fire({
            title: 'Berhasil!',
            text: 'Label berhasil diperbarui',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error("Failed to update label:", error);
        await Swal.fire({
            title: 'Gagal!',
            text: error.response?.data?.message || 'Terjadi kesalahan saat memperbarui label',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
};
</script>

<template>
    <Modal :show="show" @close="$emit('update:show', false)">
        <div class="p-6">
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">
                Edit Label
            </h3>

            <form @submit.prevent="updateLabel" class="space-y-4">
                <!-- Rim Number and Side -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="no_rim" value="Nomor Rim" />
                        <TextInput
                            id="no_rim"
                            v-model="form.no_rim"
                            type="text"
                            class="bg-slate-100 mt-1 w-full text-center"
                            disabled
                        />
                    </div>
                    <div>
                        <InputLabel value="Posisi" />
                        <TextInput
                            v-model="form.potongan"
                            type="text"
                            class="bg-slate-100 mt-1 w-full text-center"
                            disabled
                        />
                    </div>
                </div>

                <!-- Team Selection -->
                <div>
                    <InputLabel for="team" value="Tim" />
                    <select
                        id="team"
                        v-model="form.team"
                        class="mt-1 w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-900 text-center"
                    >
                        <option value="">Pilih Tim</option>
                        <option v-for="team in teamList" :key="team.id" :value="team.id">
                            {{ team.workstation }}
                        </option>
                    </select>
                </div>

                <!-- Verification Users -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="np_users" value="Periksa 1" />
                        <TextInput
                            id="np_users"
                            v-model="form.np_users"
                            type="text"
                            class="mt-1 w-full text-center"
                        />
                    </div>

                    <div>
                        <InputLabel for="np_user_p2" value="Periksa 2" />
                        <TextInput
                            id="np_user_p2"
                            v-model="form.np_user_p2"
                            type="text"
                            class="mt-1 w-full text-center"
                        />
                    </div>
                </div>

                <!-- Timestamps -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="start_time" value="Waktu Mulai" />
                        <div class="relative">
                            <TextInput
                                id="start_time"
                                v-model="form.start"
                                type="datetime-local"
                                class="mt-1 w-full text-center pr-10"
                            />
                            <button
                                type="button"
                                @click="form.start = ''"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="end_time" value="Waktu Selesai" />
                        <div class="relative">
                            <TextInput
                                id="end_time"
                                v-model="form.finish"
                                type="datetime-local"
                                class="mt-1 w-full text-center pr-10"
                            />
                            <button
                                type="button"
                                @click="form.finish = ''"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                            >
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Status Display -->
                <div>
                    <InputLabel value="Status" />
                    <div class="mt-1 flex items-center justify-center w-full">
                        <span
                            :class="{
                                'px-3 py-1 rounded-lg text-lg font-medium w-full flex items-center justify-center': true,
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400': isCompleted,
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': isInProgress,
                                'bg-slate-100 text-slate-700 dark:bg-slate-700/50 dark:text-slate-400': !isInProgress && !isCompleted
                            }"
                        >
                            {{ statusText }}
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between gap-4 mt-6">
                    <button
                        type="button"
                        @click="clearForm"
                        class="px-4 py-2 bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 rounded-xl hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors"
                    >
                        <div class="flex items-center gap-2">
                            <Trash2 class="w-4 h-4" />
                            Hapus
                        </div>
                    </button>
                    <div class="flex gap-4">
                        <button
                            type="button"
                            @click="$emit('update:show', false)"
                            class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
                        >
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>
