<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import HeaderSection from "./Partials/HeaderSection.vue";
import MainInfoForm from "./Partials/MainInfoForm.vue";
import RimInfoForm from "./Partials/RimInfoForm.vue";
import StatusSection from "./Partials/StatusSection.vue";
import EditLabelModal from "./Partials/EditLabelModal.vue";
import AddRimModal from "./Partials/AddRimModal.vue";
import { ref } from "vue";

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
</script>

<template>
    <Head title="Edit Production Order" />
    <AuthenticatedLayout>
        <div class="min-h-screen py-12 bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
            <div class="container mx-auto px-4 max-w-7xl">
                <!-- Header Section -->
                <HeaderSection
                    :data-po="dataPo"
                    :spec-po="specPo"
                    :team-list="teamList"
                />

                <!-- Main Form Sections -->
                <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl rounded-2xl shadow-lg p-8 border border-slate-100 dark:border-slate-700">
                    <MainInfoForm
                        :data-po="dataPo"
                        :spec-po="specPo"
                        @update:form="updateMainInfo"
                    />

                    <RimInfoForm
                        :data-po="dataPo"
                        :inschiet="inschiet"
                    />
                </div>

                <!-- Status Section -->
                <StatusSection
                    :labels="labels"
                    @edit-label="(label) => { selectedLabel = label; showEditModal = true }"
                    @add-rim="() => showAddRimModal = true"
                />

                <!-- Modals -->
                <EditLabelModal
                    v-model:show="showEditModal"
                    :selected-label="selectedLabel"
                    @update="refreshLabelData"
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
