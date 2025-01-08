<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import MainInfoForm from "./Partials/MainInfoForm.vue";
import RimInfoForm from "./Partials/RimInfoForm.vue";
import StatusSection from "./Partials/StatusSection.vue";
import EditLabelModal from "./Partials/EditLabelModal.vue";
import AddRimModal from "./Partials/AddRimModal.vue";
import { ref } from "vue";
import axios from "axios";
import Swal from "sweetalert2";

// Props definition
const props = defineProps({
    dataPo: { type: Object, required: true },
    specPo: { type: Object, required: true },
    dataLabel: { type: Object, required: true },
    teamList: { type: Object, required: true },
    inschiet: { type: String, required: true }
});

// Shared state
const showEditModal = ref(false);
const showAddRimModal = ref(false);
const selectedLabel = ref(null);
const labels = ref(props.dataLabel);
const isLoading = ref(false);

// Store the current form data
const currentFormData = ref({
    id: props.dataPo.id,
    no_po: props.dataPo.no_po,
    no_obc: props.dataPo.no_obc,
    type: props.dataPo.type,
    assigned_team: props.dataPo.assigned_team,
    sum_rim: props.dataPo.sum_rim,
    start_rim: props.dataPo.start_rim,
    end_rim: props.dataPo.end_rim,
    inschiet: props.inschiet
});

//Methods
const updateMainInfo = async (formData) => {
    try {
        isLoading.value = true;

        // Update the current form data with main info changes
        currentFormData.value = {
            ...currentFormData.value,
            ...formData
        };

        const response = await axios.post('/api/production-order/update-rim', currentFormData.value);

        // Refresh label data after successful update
        await refreshLabelData();

        await Swal.fire({
            title: 'Berhasil!',
            text: 'Informasi utama berhasil diperbarui',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error("Failed to update main info:", error);
        await Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat memperbarui informasi',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } finally {
        isLoading.value = false;
    }
};

const updateRimInfo = async (formData) => {
    try {
        isLoading.value = true;

        // Update the current form data with rim info changes
        currentFormData.value = {
            ...currentFormData.value,
            ...formData
        };

        const response = await axios.post('/api/production-order/update-rim', currentFormData.value);

        // Refresh label data after successful update
        await refreshLabelData();

        await Swal.fire({
            title: 'Berhasil!',
            text: 'Informasi rim berhasil diperbarui',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error("Failed to update rim info:", error);
        await Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat memperbarui informasi rim',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    } finally {
        isLoading.value = false;
    }
};

const openEditModal = (label) => {
    selectedLabel.value = label;
    showEditModal.value = true;
};

const handleBatchDelete = async (selectedIds) => {
    try {
        await axios.post('/api/production-order/delete-labels', { ids: selectedIds });
        await refreshLabelData();

        await Swal.fire({
            title: 'Berhasil!',
            text: 'Label berhasil dihapus',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    } catch (error) {
        console.error("Failed to delete labels:", error);
        await Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menghapus label',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
};

const refreshLabelData = async () => {
    try {
        const response = await axios.get(`/api/production-order/get-labels/${props.dataPo.no_po}`);
        labels.value = response.data;
    } catch (error) {
        console.error("Failed to refresh label data:", error);
        await Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat memuat ulang data',
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
                <!-- Loading Overlay -->
                <div v-if="isLoading" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                    <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-white"></div>
                </div>

                <!-- Combined Main Form Section -->
                <MainInfoForm
                    :data-po="dataPo"
                    :spec-po="specPo"
                    :team-list="teamList"
                    @update:form="updateMainInfo"
                />

                <!-- Main Form Sections -->
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 border border-slate-100 dark:border-slate-700">
                    <RimInfoForm
                        :data-po="dataPo"
                        :inschiet="inschiet"
                        @update:form="updateRimInfo"
                    />
                </div>

                <!-- Status Section -->
                <StatusSection
                    :labels="labels"
                    @edit-label="openEditModal"
                    @add-rim="showAddRimModal = true"
                    @delete-labels="handleBatchDelete"
                    @refresh-data="refreshLabelData"
                />

                <!-- Modals -->
                <EditLabelModal
                    v-model:show="showEditModal"
                    :selected-label="selectedLabel"
                    :team-list="teamList"
                    :default-team="dataPo.assigned_team"
                    @label-updated="refreshLabelData"
                />

                <AddRimModal
                    v-model:show="showAddRimModal"
                    :data-po="dataPo"
                    :labels="labels"
                    @label-updated="refreshLabelData"
                />

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
