<script setup>
import NavigateBackButton from "@/Components/NavigateBackButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <AuthenticatedLayout>
        <div class="min-w-screen min-h-screen flex flex-col gap-10 justify-center items-center">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 px-4">
            <!-- Menu Non Personal -->
            <Link
                :href="route('nonPer.nonPersonal.index')"
                class="text-5xl  font-extrabold text-blue-50 w-full px-12 py-6 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl text-center drop-shadow-md shadow-md shadow-blue-300/40"
            >
                Non Personal
            </Link>

            <!-- Menu Personal -->
            <Link
                :href="route('nonPer.personal')"
                class="text-5xl font-extrabold text-amber-50 w-full px-12 py-6 bg-gradient-to-br  from-amber-400 to-amber-500 rounded-xl text-center drop-shadow-md shadow-md shadow-amber-500/40"
            >
                Personal
            </Link>
            </div>

            <div class="flex gap-6">
                <!-- Back Button -->
                <NavigateBackButton :link="route('dashboard')"/>

                <!-- Home Button -->
                <Link
                    :href="route('dashboard')"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start drop-shadow-md shadow-md flex items-center gap-1.5"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="w-6 h-6"
                    >
                        <path
                            d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z"
                        />
                        <path
                            d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z"
                        />
                    </svg>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
