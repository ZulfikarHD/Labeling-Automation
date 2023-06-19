<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link,useForm } from '@inertiajs/vue3';

const props = defineProps({
    workstations : Object,
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
            <div class="grid grid-cols-3 gap-6 mx-10">
                <Link :href="route('np.pic.monitor-products.show',workstation.id)"   v-for="workstation in workstations" class="w-full">
                    {{ workstation.workstation }}
                </Link>
            </div>
        </div>
    </ContentLayout>
</template>
