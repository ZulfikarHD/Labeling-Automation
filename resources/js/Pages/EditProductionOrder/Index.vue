<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";
import axios from "axios";
import { Head, router, useForm } from "@inertiajs/vue3";
import {
    FileText,
    Layers,
    Hash,
    Calendar,
    Users,
    BarChart,
    ClipboardCheck,
    CheckCircle2,
    Clock,
    AlertCircle,
    Edit,
    X,
    Trash2,
    PlusCircle,
    CheckIcon
} from "lucide-vue-next";
import { ref, watch, computed } from "vue";
import Swal from 'sweetalert2';

// Props definition with validation
const props = defineProps({
    dataPo: {
        type: Object,
        required: true
    },
    specPo: {
        type: Object,
        required: true
    },
    dataLabel: {
        type: Object,
        required: true
    },
    teamList: {
        type: Object,
        required: true
    },
    inschiet: {
        type: String,
        required: true
    }
});

// Reactive references
const teams = ref(props.teamList); // List of teams
const labels = ref(props.dataLabel); // List of labels
const selectedLabel = ref(null); // Currently selected label for editing
const showEditModal = ref(false); // Control visibility of the edit modal
const showAddRimModal = ref(false); // Control visibility of the add rim modal

// Form initialization with default values
const form = useForm({
    id: props.dataPo.id,
    no_po: props.dataPo.no_po,
    no_obc: props.dataPo.no_obc,
    type: props.dataPo.type,
    sum_rim: props.dataPo.sum_rim,
    start_rim: props.dataPo.start_rim,
    end_rim: props.dataPo.end_rim,
    assigned_team: props.dataPo.assigned_team,
    status: props.dataPo.status,
    inschiet: props.inschiet,
    seri: props.specPo.seri ?? '',
    personal: props.specPo.type ?? '',
    jumlah_cetak: props.specPo.rencet ?? '',
});

// Label form initialization
const labelForm = useForm({
    id: '',
    np_users: '',
    np_user_p2: '',
    start: '',
    finish: '',
    status: '',
    team: props.dataPo.assigned_team
});

// Add these new refs and form for adding rims
const addRimForm = useForm({
    no_rim: '',
    potongan: 'both',
});

// Add these refs
const batchDeleteMode = ref(false);
const selectedLabels = ref(new Set());
const deletePassword = ref('');

// Add this computed
const hasSelectedLabels = computed(() => selectedLabels.value.size > 0);

// Add these methods
const toggleBatchDeleteMode = () => {
  batchDeleteMode.value = !batchDeleteMode.value;
  if (!batchDeleteMode.value) {
    selectedLabels.value.clear();
  }
};

const toggleLabelSelection = (labelId) => {
  if (selectedLabels.value.has(labelId)) {
    selectedLabels.value.delete(labelId);
  } else {
    selectedLabels.value.add(labelId);
  }
};

const confirmBatchDelete = async () => {
  try {
    const result = await Swal.fire({
      title: 'Konfirmasi Penghapusan',
      html: `
        <p class="mb-4">Anda akan menghapus ${selectedLabels.value.size} label. Tindakan ini tidak dapat dibatalkan.</p>
        <input
          type="password"
          id="password"
          class="swal2-input"
          placeholder="Masukkan password Anda"
        >
      `,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      preConfirm: () => {
        const password = document.getElementById('password').value;
        if (!password) {
          Swal.showValidationMessage('Password harus diisi');
        }
        return password;
      }
    });

    if (result.isConfirmed) {
      await axios.post('/api/production-order/batch-delete-labels', {
        labelIds: Array.from(selectedLabels.value),
        password: result.value
      });

      await refreshLabelData();
      toggleBatchDeleteMode();

      await Swal.fire({
        title: 'Berhasil!',
        text: 'Label berhasil dihapus',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
      });
    }
  } catch (error) {
    console.error('Failed to delete labels:', error);
    await Swal.fire({
      title: 'Gagal!',
      text: error.response?.data?.message || 'Terjadi kesalahan saat menghapus label',
      icon: 'error',
      confirmButtonText: 'OK'
    });
  }
};

// Watch effect for automatic rim calculation
watch(
    [() => form.start_rim, () => form.sum_rim],
    ([newStartRim, newSumRim], [oldStartRim, oldSumRim]) => {
        // Input validation to ensure start rim is positive
        if (newStartRim <= 0) {
            form.start_rim = 1; // Set start rim to 1 if invalid
        }

        // Calculate end rim only if values have changed
        if (newStartRim !== oldStartRim || newSumRim !== oldSumRim) {
            form.end_rim = parseInt(form.start_rim) + Math.ceil(parseInt(form.sum_rim)/2) - 1; // Calculate end rim
        }
    },
    { immediate: true }
);

// Function to fetch updated label data
const refreshLabelData = async () => {
    try {
        const response = await axios.get(`/api/production-order/get-labels/${form.no_po}`);
        labels.value = response.data; // Update labels with fetched data
    } catch (error) {
        console.error("Failed to refresh label data:", error); // Log error if fetching fails
    }
};

// Function to open the edit modal with selected label data
const openEditModal = (label) => {
    selectedLabel.value = label; // Set selected label
    labelForm.id = label.id;
    labelForm.np_users = label.np_users ?? '';
    labelForm.np_user_p2 = label.np_user_p2 ?? '';
    labelForm.no_rim = label.no_rim ?? '';
    labelForm.start = label.start ?? '';
    labelForm.finish = label.finish ?? '';
    labelForm.status = label.status ?? '';
    showEditModal.value = true; // Show the edit modal
};

// Function to update the label
const updateLabel = async () => {
    // Set status based on conditions
    if (isCompleted.value) {
        labelForm.status = 'completed'; // Set status to completed
    } else if (isInProgress.value) {
        labelForm.status = 'in_progress'; // Set status to in progress
    } else {
        labelForm.status = 'pending'; // Set status to pending
    }

    try {
        await axios.post(`/api/production-order/update-label`, labelForm); // Send update request
        // await router.post(`/api/production-order/update-label`, labelForm); // Send update request
        await refreshLabelData(); // Refresh label data after update
        showEditModal.value = false; // Close the edit modal

        // Show success message
        await Swal.fire({
            title: 'Berhasil!',
            text: 'Label berhasil diperbarui', // Success message in Indonesian
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error("Failed to update label:", error); // Log error if updating fails
        // Show error message
        await Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat memperbarui label', // Error message in Indonesian
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
};

// Updated form submission handler
const updateOrder = async () => {
    // Show confirmation dialog
    const result = await Swal.fire({
        title: 'Konfirmasi Perubahan', // Confirmation title in Indonesian
        text: 'Apakah Anda yakin ingin menyimpan perubahan ini?', // Confirmation text in Indonesian
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan', // Confirm button text in Indonesian
        cancelButtonText: 'Batal', // Cancel button text in Indonesian
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
    });

    if (result.isConfirmed) {
        try {
            const response = await axios.post("/api/production-order/update-rim", form); // Send update request

            // Refresh label data
            await refreshLabelData();

            // Show success message
            await Swal.fire({
                title: 'Berhasil!', // Success title in Indonesian
                text: 'Data berhasil diperbarui', // Success text in Indonesian
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });

        } catch (error) {
            console.error("Failed to update order:", error); // Log error if updating fails
            // Show error message
            await Swal.fire({
                title: 'Gagal!', // Error title in Indonesian
                text: 'Terjadi kesalahan saat memperbarui data', // Error text in Indonesian
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }
};

// Computed properties to determine the status of the label
const isCompleted = computed(() => {
    return labelForm.np_users &&
           labelForm.start &&
           labelForm.finish; // Check if label is completed
});

const isInProgress = computed(() => {
    return (labelForm.np_users || labelForm.np_user_p2) &&
           labelForm.start &&
           !labelForm.finish; // Check if label is in progress
});

// Computed property to get the status text
const statusText = computed(() => {
    if (isCompleted.value) return 'Selesai Verif'; // Status text in Indonesian
    if (isInProgress.value) return 'Proses Verif'; // Status text in Indonesian
    return 'Belum Verif'; // Status text in Indonesian
});

// Function to clear the label form
const clearForm = () => {
    labelForm.np_users = '';
    labelForm.np_user_p2 = '';
    labelForm.start = '';
    labelForm.finish = '';
    labelForm.status = 'pending'; // Reset status to pending
};

// Add this computed property to check rim availability
const rimAvailability = computed(() => {
    const rimNumber = addRimForm.no_rim.toString(); // Convert to string for consistent comparison
    if (!rimNumber) {
        return {
            left: false,
            right: false,
            canAddBoth: false,
            canAddLeft: false,
            canAddRight: false
        };
    }

    const leftExists = labels.value.some(label =>
        label.no_rim.toString() === rimNumber && label.potongan === 'Kiri' // Check if left rim exists
    );
    const rightExists = labels.value.some(label =>
        label.no_rim.toString() === rimNumber && label.potongan === 'Kanan' // Check if right rim exists
    );

    return {
        left: !leftExists,
        right: !rightExists,
        canAddBoth: !leftExists && !rightExists,
        canAddLeft: !leftExists,
        canAddRight: !rightExists
    };
});

// Add this watch effect to handle rim number changes
watch(() => addRimForm.no_rim, (newValue) => {
    if (!newValue) return; // Exit if no rim number is provided

    const availability = rimAvailability.value;

    // If current selection is not available, reset to available option
    if (addRimForm.potongan === 'both' && !availability.canAddBoth) {
        if (availability.canAddLeft) {
            addRimForm.potongan = 'Kiri'; // Set to left if available
        } else if (availability.canAddRight) {
            addRimForm.potongan = 'Kanan'; // Set to right if available
        }
    } else if (addRimForm.potongan === 'Kiri' && !availability.canAddLeft) {
        if (availability.canAddRight) {
            addRimForm.potongan = 'Kanan'; // Set to right if available
        } else if (availability.canAddBoth) {
            addRimForm.potongan = 'both'; // Set to both if available
        }
    } else if (addRimForm.potongan === 'Kanan' && !availability.canAddRight) {
        if (availability.canAddLeft) {
            addRimForm.potongan = 'Kiri'; // Set to left if available
        } else if (availability.canAddBoth) {
            addRimForm.potongan = 'both'; // Set to both if available
        }
    }
});

// Add this method to handle rim addition
const addNewRim = async () => {
    if (!addRimForm.no_rim) {
        await Swal.fire({
            title: 'Peringatan!', // Warning title in Indonesian
            text: 'Nomor rim harus diisi', // Warning text in Indonesian
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return; // Exit if no rim number is provided
    }

    const availability = rimAvailability.value;

    // Validate if selected option is available
    if (
        (addRimForm.potongan === 'both' && !availability.canAddBoth) ||
        (addRimForm.potongan === 'Kiri' && !availability.canAddLeft) ||
        (addRimForm.potongan === 'Kanan' && !availability.canAddRight)
    ) {
        await Swal.fire({
            title: 'Peringatan!', // Warning title in Indonesian
            text: 'Posisi rim yang dipilih sudah terisi', // Warning text in Indonesian
            icon: 'warning',
            confirmButtonText: 'OK'
        });
        return; // Exit if selected position is not available
    }

    try {
        const payload = {
            no_po: form.no_po,
            no_rim: addRimForm.no_rim,
            potongan: addRimForm.potongan,
            team: form.assigned_team
        };

        await axios.post('/api/production-order/add-rim', payload); // Send request to add rim
        // await router.post('/api/production-order/add-rim', payload); // Send request to add rim
        await refreshLabelData(); // Refresh label data
        showAddRimModal.value = false; // Close the add rim modal
        addRimForm.reset(); // Reset the add rim form

        await Swal.fire({
            title: 'Berhasil!', // Success title in Indonesian
            text: 'Rim berhasil ditambahkan', // Success text in Indonesian
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error("Failed to add rim:", error); // Log error if adding fails
        await Swal.fire({
            title: 'Gagal!', // Error title in Indonesian
            text: 'Terjadi kesalahan saat menambahkan rim', // Error text in Indonesian
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
};
</script>

<template>
    <Head title="Edit Production Order" />
    <AuthenticatedLayout>
        <div class="min-h-screen py-12 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
            <div class="container mx-auto px-4 max-w-7xl">
                <!-- Header Section -->
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 mb-8 border border-slate-100 dark:border-slate-700 transition-all duration-300">
                    <div class="mb-6">
                        <h1 class="text-4xl font-bold text-slate-900 dark:text-white text-center tracking-tight">
                            Edit Order
                            <span class="text-blue-600 dark:text-blue-400">{{ form.no_po }}</span>
                        </h1>
                        <p class="mt-2 text-slate-600 dark:text-slate-400 text-center">
                            Edit settingan label produksi
                        </p>
                    </div>

                    <form @submit.prevent="updateOrder" class="space-y-4">

                        <!-- Team Assignment -->
                        <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <Users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                <InputLabel value="Tim yang Ditugaskan" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                            </div>
                            <select v-model="form.assigned_team" class="w-full bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 rounded-xl shadow-sm dark:text-white p-2">
                                <option v-for="team in teams" :key="team.id" :value="team.id">
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
                                <TextInput id="personal" v-model="form.personal" type="text" class="bg-slate-100 text-center" disabled/>
                            </div>

                            <!-- Jumlah Cetak -->
                            <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                                <div class="flex items-center gap-3 mb-3">
                                    <BarChart class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                                    <InputLabel for="jumlah_cetak" value="Jumlah Cetak" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                                </div>
                                <TextInput id="jumlah_cetak" v-model="form.jumlah_cetak" type="number" class="bg-slate-100 text-center" disabled/>
                            </div>
                        </div>

                        <!-- Rim Info Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                            <!-- Sum Rim -->
                            <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                                <div class="flex items-center gap-3 mb-3">
                                    <BarChart class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                    <InputLabel for="totalRim" value="Total Rim" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                                </div>
                                <TextInput id="totalRim" v-model="form.sum_rim" type="number" class="bg-slate-100 text-center" disabled />
                            </div>

                            <!-- Start Rim -->
                            <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                                <div class="flex items-center gap-3 mb-3">
                                    <Calendar class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                                    <InputLabel for="startRim" value="Nomor Rim Awal" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                                </div>
                                <TextInput v-model="form.start_rim" id="startRim" type="number" min="1" class="text-center" />
                            </div>

                            <!-- End Rim -->
                            <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                                <div class="flex items-center gap-3 mb-3">
                                    <Calendar class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                                    <InputLabel for="endRim" value="Nomor Rim Akhir" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                                </div>
                                <TextInput id="endRim" v-model="form.end_rim" type="number" disabled class="bg-slate-100 text-center" />
                            </div>

                            <!-- Inschiet -->
                            <div class="bg-slate-50/50 dark:bg-slate-700/50 rounded-xl p-6">
                                <div class="flex items-center gap-3 mb-3">
                                    <ClipboardCheck class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                                    <InputLabel for="inschiet" value="Inschiet" class="text-lg font-semibold text-slate-700 dark:text-slate-300" />
                                </div>
                                <TextInput id="inschiet" v-model="form.inschiet" type="number" class="text-center" />
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-4">
                            <button type="button" class="px-6 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl hover:bg-slate-300 dark:hover:bg-slate-600 transition-all duration-300">
                                Batal
                            </button>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-300 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Status Section -->
                <div class="mt-8 bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 border border-slate-100 dark:border-slate-700">
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                                    Edit Label Produksi
                                </h1>
                                <p class="mt-2 text-slate-600 dark:text-slate-400">
                                    Edit Nomor Rim Manual
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="showAddRimModal = true"
                                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                    <PlusCircle class="w-5 h-5" />
                                    Tambah Rim
                                </button>
                                <button
                                    @click="toggleBatchDeleteMode"
                                    class="flex items-center gap-2 px-4 py-2 text-white transition-all duration-200 rounded-xl"
                                    :class="batchDeleteMode ? 'bg-red-500 hover:bg-red-600' : 'bg-slate-500 hover:bg-slate-600'"
                                >
                                    <Trash2 class="w-5 h-5" />
                                    {{ batchDeleteMode ? 'Batal' : 'Hapus Batch' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row justify-between gap-8 mb-12">
                        <!-- Lembar Kiri -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white text-center mb-6">
                                Lembar Kiri
                            </h3>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                                <template v-for="status in labels" :key="`${status.id}-${status.updated_at}`">
                                    <template v-if="status.potongan == 'Kiri'">
                                        <div
                                            :class="[
                                                'relative transition-all duration-300 hover:shadow-md cursor-pointer rounded-xl',
                                                batchDeleteMode ? 'hover:scale-100' : 'hover:scale-105'
                                            ]"
                                            @click="batchDeleteMode ? toggleLabelSelection(status.id) : openEditModal(status)"
                                        >
                                            <!-- Selection overlay -->
                                            <div
                                                v-if="batchDeleteMode"
                                                class="absolute inset-0 z-10 rounded-xl"
                                                :class="selectedLabels.has(status.id) ? 'bg-red-500/20 ring-2 ring-red-500' : 'hover:bg-slate-500/10'"
                                            >
                                            </div>

                                            <!-- Existing status cards -->
                                            <div v-if="status.finish != null"
                                                 class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl p-3">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ status.np_users }}</span>
                                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ status.no_rim }}</span>
                                                </div>
                                            </div>
                                            <div v-else-if="status.start != null && status.finish == null"
                                                 class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl p-3">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="text-sm font-bold text-amber-700 dark:text-amber-400">{{ status.np_users }}</span>
                                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ status.no_rim }}</span>
                                                </div>
                                            </div>
                                            <div v-else
                                                 class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl p-3">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="text-sm font-bold text-slate-400 dark:text-slate-500">-</span>
                                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ status.no_rim }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="flex flex-row lg:flex-col justify-center gap-6 bg-slate-50/50 dark:bg-slate-700/50 p-6 rounded-xl">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 flex items-center justify-center shadow-sm">
                                    <CheckCircle2 class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Selesai Verifikasi</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 flex items-center justify-center shadow-sm">
                                    <Clock class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                                </div>
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Proses Verifikasi</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 flex items-center justify-center shadow-sm">
                                    <AlertCircle class="w-6 h-6 text-slate-400 dark:text-slate-500" />
                                </div>
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Belum Verifikasi</span>
                            </div>
                        </div>

                        <!-- Lembar Kanan -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-slate-800 dark:text-white text-center mb-6">
                                Lembar Kanan
                            </h3>
                            <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                                <template v-for="status in labels" :key="`${status.id}-${status.updated_at}`">
                                    <template v-if="status.potongan == 'Kanan'">
                                        <div
                                            :class="[
                                                'relative transition-all duration-300 hover:shadow-md cursor-pointer rounded-xl',
                                                batchDeleteMode ? 'hover:scale-100' : 'hover:scale-105'
                                            ]"
                                            @click="batchDeleteMode ? toggleLabelSelection(status.id) : openEditModal(status)"
                                        >
                                            <!-- Selection overlay -->
                                            <div
                                                v-if="batchDeleteMode"
                                                class="absolute inset-0 z-10 rounded-xl"
                                                :class="selectedLabels.has(status.id) ? 'bg-red-500/20 ring-2 ring-red-500' : 'hover:bg-slate-500/10'"
                                            >
                                            </div>

                                            <!-- Existing status cards -->
                                            <div v-if="status.finish != null"
                                                 class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl p-3">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ status.np_users }}</span>
                                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ status.no_rim }}</span>
                                                </div>
                                            </div>
                                            <div v-else-if="status.start != null && status.finish == null"
                                                 class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl p-3">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="text-sm font-bold text-amber-700 dark:text-amber-400">{{ status.np_users }}</span>
                                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ status.no_rim }}</span>
                                                </div>
                                            </div>
                                            <div v-else
                                                 class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl p-3">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="text-sm font-bold text-slate-400 dark:text-slate-500">-</span>
                                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ status.no_rim }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </template>
                            </div>
                        </div>

                        <div
                            v-if="batchDeleteMode"
                            class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50"
                        >
                            <button
                            @click="confirmBatchDelete"
                            :disabled="!hasSelectedLabels"
                            class="flex items-center gap-2 px-6 py-3 text-white bg-red-500 rounded-xl shadow-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-red-600 transition-colors"
                            >
                            <Trash2 class="w-5 h-5" />
                            Hapus {{ selectedLabels.size }} Label
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Edit Label Modal -->
                <Modal :show="showEditModal" @close="showEditModal = false">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">
                            Edit Label
                        </h3>

                        <form @submit.prevent="updateLabel" class="space-y-4">
                            <div>
                                <InputLabel for="no_rim" value="Nomor Rim" />
                                <TextInput id="no_rim" v-model="labelForm.no_rim" type="text" class="bg-slate-100 mt-1 w-full text-center" disabled />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="np_users" value="Periksa 1" />
                                    <TextInput id="np_users" v-model="labelForm.np_users" type="text" class="mt-1 w-full text-center" />
                                </div>

                                <div>
                                    <InputLabel for="np_user_p2" value="Periksa 2" />
                                    <TextInput id="np_user_p2" v-model="labelForm.np_user_p2" type="text" class="mt-1 w-full" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="start_time" value="Waktu Mulai" />
                                    <div class="relative">
                                        <TextInput id="start_time" v-model="labelForm.start" type="datetime-local" class="mt-1 w-full text-center pr-10" />
                                        <button
                                            type="button"
                                            @click="labelForm.start = ''"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                        >
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <InputLabel for="end_time" value="Waktu Selesai" />
                                    <div class="relative">
                                        <TextInput id="end_time" v-model="labelForm.finish" type="datetime-local" class="mt-1 w-full text-center pr-10" />
                                        <button
                                            type="button"
                                            @click="labelForm.finish = ''"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                        >
                                            <X class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

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
                                        @click="showEditModal = false"
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

                <!-- Add Rim Modal -->
                <Modal :show="showAddRimModal" @close="showAddRimModal = false">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">
                            Tambah Rim Baru
                        </h3>

                        <form @submit.prevent="addNewRim" class="space-y-4">
                            <div>
                                <InputLabel for="add_rim_number" value="Nomor Rim" />
                                <TextInput
                                    id="add_rim_number"
                                    v-model="addRimForm.no_rim"
                                    type="number"
                                    class="mt-1 w-full text-center"
                                    min="1"
                                />
                            </div>

                            <div>
                                <InputLabel value="Posisi Rim" />
                                <div class="mt-4 space-y-2 flex flex-row gap-6 justify-center items-center">
                                    <label class="flex items-center gap-2" :class="{'opacity-50': !rimAvailability.canAddBoth}">
                                        <input
                                            type="radio"
                                            v-model="addRimForm.potongan"
                                            value="both"
                                            :disabled="!rimAvailability.canAddBoth"
                                            class="text-blue-600 disabled:opacity-50"
                                        />
                                        <span>Kedua Sisi</span>
                                    </label>
                                    <label class="flex items-center gap-2" :class="{'opacity-50': !rimAvailability.canAddLeft}">
                                        <input
                                            type="radio"
                                            v-model="addRimForm.potongan"
                                            value="Kiri"
                                            :disabled="!rimAvailability.canAddLeft"
                                            class="text-blue-600 disabled:opacity-50"
                                        />
                                        <span>Sisi Kiri</span>
                                    </label>
                                    <label class="flex items-center gap-2" :class="{'opacity-50': !rimAvailability.canAddRight}">
                                        <input
                                            type="radio"
                                            v-model="addRimForm.potongan"
                                            value="Kanan"
                                            :disabled="!rimAvailability.canAddRight"
                                            class="text-blue-600 disabled:opacity-50"
                                        />
                                        <span>Sisi Kanan</span>
                                    </label>
                                </div>

                                <!-- Add availability message -->
                                <div v-if="addRimForm.no_rim" class="mt-2 text-sm text-center">
                                    <p v-if="!rimAvailability.canAddLeft && !rimAvailability.canAddRight"
                                       class="text-red-500">
                                        Rim nomor {{ addRimForm.no_rim }} sudah terisi di kedua sisi
                                    </p>
                                    <p v-else-if="!rimAvailability.canAddLeft"
                                       class="text-amber-500">
                                        Rim nomor {{ addRimForm.no_rim }} sudah terisi di sisi kiri
                                    </p>
                                    <p v-else-if="!rimAvailability.canAddRight"
                                       class="text-amber-500">
                                        Rim nomor {{ addRimForm.no_rim }} sudah terisi di sisi kanan
                                    </p>
                                    <p v-else class="text-emerald-500">
                                        Rim nomor {{ addRimForm.no_rim }} tersedia untuk ditambahkan
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-end gap-4 mt-6">
                                <button
                                    type="button"
                                    @click="showAddRimModal = false"
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

                <!-- Decorative Background -->
                <div class="absolute inset-0 -z-10 overflow-hidden">
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                    <div class="absolute left-0 right-0 top-0 -z-10 m-auto h-[310px] w-[310px] rounded-full bg-blue-400 dark:bg-blue-600 opacity-20 blur-[100px]"></div>
                    <div class="absolute right-0 top-0 -z-10 h-[310px] w-[310px] rounded-full bg-indigo-400 dark:bg-indigo-600 opacity-20 blur-[100px]"></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
