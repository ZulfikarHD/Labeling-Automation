<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';
import NavigateBackButton from '@/Components/NavigateBackButton.vue';

const props = defineProps({
    workstations: Object,
});

</script>

<template>
    <ContentLayout>
        <!-- Container -->
        <div class="py-8 flex justify-center flex-col">
            <!-- Header -->
            <div class="mx-auto my-10 w-fit">
                <h1 class="text-3xl font-bold">Work On Progress</h1>
            </div>

            <!-- Main Body -->
            <div class="grid h-full grid-cols-3 gap-6 mt-36 mx-36">
                <Link :href="route('nonPer.nonPersonal.monitor.show', workstation.id)" v-for="workstation in workstations"
                    class="w-full px-8 py-4 text-lg font-bold tracking-wide shadow rounded-xl text-blue-50 bg-blue-700 brightness-110 drop-shadow-md">
                {{ workstation.workstation }}
                </Link>
            </div>

            <div class="flex gap-6 mt-10 mx-auto">
                <!-- Back Button -->
                <NavigateBackButton :link="route('nonPer.nonPersonal.pic.index')" />

                <!-- Home Button -->
                <Link :href="route('dashboard')"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3    px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start drop-shadow-md shadow-md flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                    <path
                        d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                    <path
                        d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
                </svg>
                </Link>
            </div>
        </div>
    </ContentLayout>
</template>
