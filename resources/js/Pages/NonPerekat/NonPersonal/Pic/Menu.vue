<script setup>
import NavigateBackButton from "@/Components/NavigateBackButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <AuthenticatedLayout>
        <div
            class="min-w-screen min-h-screen flex flex-col gap-10 justify-center items-center"
        >
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 px-4">
            <!-- Membuat Label Baru -->
            <Link
                :href="route('nonPer.nonPersonal.entryPo.index')"
                    class="text-5xl col-span-1 lg:col-span-2 font-extrabold text-blue-50 w-full px-12 py-6 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl text-center drop-shadow-md shadow-md shadow-emerald-300/40"
                >Entry Po</Link
            >

            <!-- Monitoring Verifikasi -->
            <Link
                :href="route('nonPer.nonPersonal.monitor.index')"
                    class="text-5xl col-span-2 lg:col-span-1 font-extrabold text-blue-50 w-full px-12 py-6 bg-gradient-to-br from-sky-400 to-sky-600 rounded-xl text-center drop-shadow-md shadow-md shadow-sky-300/40"
                >Monitor Verifikasi</Link
            >

            <!-- List PO yang dibuat -->
            <Link
                :href="route('nonPer.nonPersonal.listPo.index',0)"
                    class="text-5xl col-span-2 lg:col-span-1 font-extrabold text-blue-50 w-full px-12 py-6 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl text-center drop-shadow-md shadow-md shadow-yellow-300/40"
                >Daftar Label Dibuat</Link
            >
            </div>
            <!-- Return Buttons -->
            <div class="flex gap-6">
                <!-- Back Button -->
                <NavigateBackButton :link="route('nonPer.nonPersonal.index')" />

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
