<script setup>
import { ref, computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { AlertCircle } from "lucide-vue-next";
import axios from "axios";
import Swal from "sweetalert2";

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    dataPo: {
        type: Object,
        required: true
    },
    labels: {
        type: Array,
        required: true
    }
});

const emit = defineEmits(['update:show', 'labelUpdated']);

const form = useForm({
    po_id: props.dataPo.id,
    no_rim: '',
    potongan: 'both', // Changed default to 'both' to match Index.vue
});

// Add rimAvailability computed property to match Index.vue
const rimAvailability = computed(() => {
    const rimNumber = form.no_rim.toString();
    if (!rimNumber) {
        return {
            left: false,
            right: false,
            canAddBoth: false,
            canAddLeft: false,
            canAddRight: false
        };
    }

    const leftExists = props.labels.some(label =>
        label.no_rim.toString() === rimNumber && label.potongan === 'Kiri'
    );
    const rightExists = props.labels.some(label =>
        label.no_rim.toString() === rimNumber && label.potongan === 'Kanan'
    );

    return {
        left: !leftExists,
        right: !rightExists,
        canAddBoth: !leftExists && !rightExists,
        canAddLeft: !leftExists,
        canAddRight: !rightExists
    };
});

// Add watch effect for rim number changes
watch(() => form.no_rim, (newValue) => {
    if (!newValue) return;

    const availability = rimAvailability.value;

    // If current selection is not available, reset to available option
    if (form.potongan === 'both' && !availability.canAddBoth) {
        if (availability.canAddLeft) {
            form.potongan = 'Kiri';
        } else if (availability.canAddRight) {
            form.potongan = 'Kanan';
        }
    } else if (form.potongan === 'Kiri' && !availability.canAddLeft) {
        if (availability.canAddRight) {
            form.potongan = 'Kanan';
        } else if (availability.canAddBoth) {
            form.potongan = 'both';
        }
    } else if (form.potongan === 'Kanan' && !availability.canAddRight) {
        if (availability.canAddLeft) {
            form.potongan = 'Kiri';
        } else if (availability.canAddBoth) {
            form.potongan = 'both';
        }
    }
});

const addNewRim = async () => {
    try {
        if (!form.no_rim) {
            await Swal.fire({
                title: 'Peringatan!',
                text: 'Nomor rim harus diisi',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        const availability = rimAvailability.value;

        // Validate if selected option is available
        if (
            (form.potongan === 'both' && !availability.canAddBoth) ||
            (form.potongan === 'Kiri' && !availability.canAddLeft) ||
            (form.potongan === 'Kanan' && !availability.canAddRight)
        ) {
            await Swal.fire({
                title: 'Peringatan!',
                text: 'Posisi rim yang dipilih sudah terisi',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        const payload = {
            no_po: props.dataPo.no_po,
            no_rim: form.no_rim,
            potongan: form.potongan,
            team: props.dataPo.assigned_team
        };

        await axios.post('/api/production-order/add-rim', payload);
        emit('update:show', false);
        emit('labelUpdated');

        // Reset form
        form.no_rim = '';
        form.potongan = 'both';

        await Swal.fire({
            title: 'Berhasil!',
            text: 'Rim berhasil ditambahkan',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error("Failed to add rim:", error);
        await Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menambahkan rim',
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
                Tambah Rim Baru
            </h3>

            <form @submit.prevent="addNewRim" class="space-y-6">
                <!-- Rim Number Input -->
                <div>
                    <InputLabel for="add_rim_number" value="Nomor Rim" />
                    <TextInput
                        id="add_rim_number"
                        v-model="form.no_rim"
                        type="number"
                        class="mt-1 w-full text-center"
                        min="1"
                        required
                    />
                </div>

                <!-- Side Selection with Radio Buttons -->
                <div>
                    <InputLabel value="Posisi Rim" />
                    <div class="mt-4 space-y-2 flex flex-row gap-6 justify-center items-center">
                        <label class="flex items-center gap-2" :class="{'opacity-50': !rimAvailability.canAddBoth}">
                            <input
                                type="radio"
                                v-model="form.potongan"
                                value="both"
                                :disabled="!rimAvailability.canAddBoth"
                                class="text-blue-600 disabled:opacity-50"
                            />
                            <span>Kedua Sisi</span>
                        </label>
                        <label class="flex items-center gap-2" :class="{'opacity-50': !rimAvailability.canAddLeft}">
                            <input
                                type="radio"
                                v-model="form.potongan"
                                value="Kiri"
                                :disabled="!rimAvailability.canAddLeft"
                                class="text-blue-600 disabled:opacity-50"
                            />
                            <span>Sisi Kiri</span>
                        </label>
                        <label class="flex items-center gap-2" :class="{'opacity-50': !rimAvailability.canAddRight}">
                            <input
                                type="radio"
                                v-model="form.potongan"
                                value="Kanan"
                                :disabled="!rimAvailability.canAddRight"
                                class="text-blue-600 disabled:opacity-50"
                            />
                            <span>Sisi Kanan</span>
                        </label>
                    </div>

                    <!-- Availability Message -->
                    <div v-if="form.no_rim" class="mt-2 text-sm text-center">
                        <p v-if="!rimAvailability.canAddLeft && !rimAvailability.canAddRight"
                           class="text-red-500">
                            Rim nomor {{ form.no_rim }} sudah terisi di kedua sisi
                        </p>
                        <p v-else-if="!rimAvailability.canAddLeft"
                           class="text-amber-500">
                            Rim nomor {{ form.no_rim }} sudah terisi di sisi kiri
                        </p>
                        <p v-else-if="!rimAvailability.canAddRight"
                           class="text-amber-500">
                            Rim nomor {{ form.no_rim }} sudah terisi di sisi kanan
                        </p>
                        <p v-else class="text-emerald-500">
                            Rim nomor {{ form.no_rim }} tersedia untuk ditambahkan
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4">
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
                        Tambah
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
