<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <AuthenticatedLayout>
        <div class="min-w-screen min-h-screen flex flex-col gap-6 justify-center items-center">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 px-4">
                <Link
                    :href="route('nonPer.index')"
                    class="text-5xl font-extrabold text-blue-50 w-full px-12 py-6 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl text-center drop-shadow-md shadow-md shadow-blue-300/40"
                    >Pita Cukai Non-Perekat</Link
                >
                <Link
                    :href="route('p.generateLabels')"
                    class="text-5xl font-extrabold text-amber-50 w-full px-12 py-6 bg-gradient-to-br  from-amber-400 to-amber-500 rounded-xl text-center drop-shadow-md shadow-md shadow-amber-500/40"
                    >Pita Cukai Ber-Perekat</Link
                >
            </div>
        </div>
    </AuthenticatedLayout>
</template>
