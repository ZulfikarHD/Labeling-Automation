<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    workstations: Object,
});

</script>

<template>
    <ContentLayout>
        <!-- Container -->
        <div class="py-8">
            <!-- Header -->
            <div class="mx-auto my-10 w-fit">
                <h1 class="text-3xl font-bold">Work On Progress</h1>
            </div>

            <!-- Main Body -->
            <div class="grid h-full grid-cols-3 gap-6 mt-36 mx-36">
                <Link :href="route('np.pic.monitor-products.show', workstation.id)" v-for="workstation in workstations"
                    class="w-full px-8 py-4 text-lg font-bold tracking-wide shadow rounded-xl text-blue-50 bg-gradient-to-br from-blue-400 to-green-300 brightness-110 drop-shadow-md">
                {{ workstation.workstation }}
                </Link>
            </div>
        </div>
    </ContentLayout>
</template>
