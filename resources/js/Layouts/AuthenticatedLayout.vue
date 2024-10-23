<script setup>
import { ref } from "vue";
import NavLink from "@/Components/NavLink.vue";
import { usePage } from "@inertiajs/vue3";

const { props } = usePage();
const role = props.auth.user.role;
const showOrderBesarGroup = ref(false);
const showOrderKecilGroup = ref(false);

const toggleDropdown = (dropDownId) => {
    if (dropDownId === 1) {
        showOrderBesarGroup.value = !showOrderBesarGroup.value;
        showOrderKecilGroup.value = false;
    } else if (dropDownId === 2) {
        showOrderKecilGroup.value = !showOrderKecilGroup.value;
        showOrderBesarGroup.value = false;
    }
}
</script>
<template>
    <div class="h-full min-h-screen bg-gradient-to-b from-slate-50 to-slate-100">
        <div class="w-full bg-white drop-shadow-md px-8 py-4 flex justify-between gap-4 items-center sticky top-0 z-50">
            <!-- Logo Peruri -->
            <img :src="'/img/peruri.png'" class="w-24" />

            <!-- Navigation -->
            <div class="flex justify-start items-center gap-6">
                <!-- Order Besar -->
                <div class="relative">
                    <div @click="toggleDropdown(1)" class="flex items-center cursor-pointer">
                        <span class="text-sm font-semibold text-gray-800">Order Besar</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 ml-1">
                            <path fill-rule="evenodd" d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <Transition name="fade">
                        <div v-show="showOrderBesarGroup" class="absolute left-0 mt-2 w-48 bg-white shadow-lg z-10">
                            <NavLink
                                :href="route('orderBesar.poSiapVerif')"
                                :active="route().current('orderBesar.poSiapVerif')"
                                class="block text-gray-700  px-4 py-2 transition duration-200 ease-in-out w-full">
                                Order Siap Periksa
                            </NavLink>
                            <NavLink
                                :href="route('orderBesar.registerNomorPo')"
                                :active="route().current('orderBesar.registerNomorPo')"
                                class="block text-gray-700 px-4 py-2 transition duration-200 ease-in-out w-full">
                                Register Nomor PO
                            </NavLink>
                        </div>
                    </Transition>
                </div>

                <!-- Order Kecil -->
                <div class="relative">
                    <div @click="toggleDropdown(2)" class="flex items-center cursor-pointer">
                        <span class="text-sm font-semibold text-gray-800">Order Kecil</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 ml-1">
                            <path fill-rule="evenodd" d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <Transition name="fade">
                        <div v-show="showOrderKecilGroup" class="absolute left-0 mt-2 w-48 bg-white shadow-lg z-10">
                            <NavLink
                                :href="route('orderKecil.cetakLabel')"
                                :active="route().current('orderKecil.cetakLabel')"
                                class="block text-gray-700  px-4 py-2 transition duration-200 ease-in-out w-full">
                                Cetak Label
                            </NavLink>
                        </div>
                    </Transition>
                </div>

                <!-- Monitoring Verifikasi -->
                <NavLink
                    :href="route('monitoringProduksi.statusVerif.index')"
                    :active="route().current('monitoringProduksi.statusVerif.index')"
                    class="text-gray-800  px-2 py-1 transition duration-200 ease-in-out">
                    Monitoring Produksi
                </NavLink>

                <!-- Data Production Order -->
                <NavLink
                    :href="route('dataPo.index',0)"
                    :active="route().current('dataPo.index')"
                    class="text-gray-800  px-2 py-1 transition duration-200 ease-in-out">
                    Data PO
                </NavLink>

                <!-- Hasil Produksi Pegawai -->
                <NavLink
                    :href="route('monitoringProduksi.produksiPegawai')"
                    :active="route().current('monitoringProduksi.produksiPegawai')"
                    class="text-gray-800 px-2 py-1 transition duration-200 ease-in-out">
                    Produksi Pegawai
                </NavLink>

                <!-- Create User -->
                <NavLink v-if="role === 1"
                    :href="route('createUser.index')"
                    :active="route().current('createUser.index')"
                    class="text-gray-800  px-2 py-1 transition duration-200 ease-in-out">
                    Create User
                </NavLink>
            </div>

            <!-- Option -->
            <div></div>
        </div>
        <slot />
    </div>
</template>
