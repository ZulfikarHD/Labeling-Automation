<script setup>
import { ref } from "vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";
import GroupLink from "@/Components/GroupLink.vue";

const showingNavigationDropdown = ref(false);
const showOrderBesarGroup = ref(false);
const showOrderKecilGroup = ref(false);

const toogleDropdown = (dropDownId) => {
    if(dropDownId == 1){
        showOrderBesarGroup.value = showOrderBesarGroup.value == false ? true : false;
        showOrderKecilGroup.value = false;
    } else if (dropDownId == 2) {
        showOrderKecilGroup.value = showOrderKecilGroup.value == false ? true : false;
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
                <GroupLink @click="toogleDropdown(1)"
                    :active="$page.url == route('orderBesar.poSiapVerif') || route('dashboard')"
                    class="relative group">
                    <div class="flex items-center">
                        <span class="text-lg font-semibold text-gray-800">Order Besar</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 ml-1 inline-block">
                            <path fill-rule="evenodd" d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <Transition animation="slide-in-right">
                        <div class="absolute flex flex-col gap-2 bg-white shadow-lg rounded-md -left-28 px-4 py-2 top-3 hidden group-hover:block">
                            <NavLink @mouseover="showOrderBesarGroup = true"
                                :href="route('orderBesar.poSiapVerif') || route('dashboard')"
                                :active="$page.url == route('orderBesar.poSiapVerif') || route('dashboard')"
                                class="text-gray-700 hover:bg-gray-100 rounded-md px-2 py-1">
                                Order Siap Periksa
                            </NavLink>

                            <NavLink @mouseover="showOrderBesarGroup = true"
                                :href="route('orderBesar.registerNomorPo')"
                                :active="$page.url == route('orderBesar.registerNomorPo')"
                                class="text-gray-700 hover:bg-gray-100 rounded-md px-2 py-1">
                                Register Nomor PO
                            </NavLink>
                        </div>
                    </Transition>
                </GroupLink>

                <!-- Order Kecil -->
                <GroupLink @click="toogleDropdown(2)"
                    :active="$page.url == route('orderKecil.cetakLabel')"
                    class="relative group">
                    <div class="flex items-center">
                        <span class="text-lg font-semibold text-gray-800">Order Kecil</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 ml-1 inline-block">
                            <path fill-rule="evenodd" d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <Transition animation="slide-in-right">
                        <div class="absolute flex flex-col gap-2 bg-white shadow-lg rounded-md -left-28 px-4 py-2 top-3 hidden group-hover:block">
                            <NavLink
                                :href="route('orderKecil.cetakLabel')"
                                :active="$page.url == route('orderKecil.cetakLabel')"
                                class="text-gray-700 hover:bg-gray-100 rounded-md px-2 py-1">
                                Cetak Label
                            </NavLink>
                        </div>
                    </Transition>
                </GroupLink>

                <!-- Monitoring Verifikasi -->
                <NavLink
                    :href="route('monitoringProduksi.statusVerif.index')"
                    :active="$page.url == route('monitoringProduksi.statusVerif.index')"
                    class="text-gray-800 hover:bg-gray-100 rounded-md px-2 py-1">
                    Monitoring Produksi
                </NavLink>

                <!-- Data Production Order -->
                <NavLink
                    :href="route('dataPo.index',0)"
                    class="text-gray-800 hover:bg-gray-100 rounded-md px-2 py-1">
                    Data PO
                </NavLink>

                <!-- Hasil Produksi Pegawai -->
                <NavLink
                    :href="route('monitoringProduksi.produksiPegawai')"
                    class="text-gray-800 hover:bg-gray-100 rounded-md px-2 py-1">
                    Produksi Pegawai
                </NavLink>

                <!-- Create User -->
                <NavLink
                    :href="route('createUser.index')"
                    class="text-gray-800 hover:bg-gray-100 rounded-md px-2 py-1">
                    Create User
                </NavLink>
            </div>

            <!-- Option -->
            <div></div>
        </div>
        <slot />
    </div>
</template>
