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
                <h1 class="text-3xl font-bold text-center text-gray-800">Proses Pengerjaan</h1>
            </div>

            <!-- Main Body -->
            <div class="grid h-full grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-10 mx-4 sm:mx-10 lg:mx-36">
                <Link :href="route('monitoringProduksi.statusVerif.show', workstation.id)" v-for="workstation in workstations"
                    class="w-full px-6 py-4 text-lg font-bold tracking-wide shadow-lg rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition duration-200 ease-in-out">
                {{ workstation.workstation }}
                </Link>
            </div>

            <div class="flex gap-6 mt-10 mx-auto">
                <!-- Home Button -->
                <Link :href="route('dashboard')"
                    class="text-xl font-extrabold text-white w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start shadow-md flex items-center gap-1.5 hover:shadow-lg transition duration-200 ease-in-out">
                <LucideIcon name="home" class="w-6 h-6" />
                </Link>
            </div>
        </div>
    </ContentLayout>
</template>
