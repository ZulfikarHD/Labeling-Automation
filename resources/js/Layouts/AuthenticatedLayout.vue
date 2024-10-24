<script setup>
import { ref } from "vue";
import NavLink from "@/Components/NavLink.vue";
import { Settings, ChevronDown } from "lucide-vue-next";
import { router, usePage } from "@inertiajs/vue3";

const { props } = usePage();
const role = props.auth.user.role;
const showOrderBesarGroup = ref(false);
const showOrderKecilGroup = ref(false);
const showOption = ref(false);

const toggleDropdown = (dropDownId) => {
    if (dropDownId === 1) {
        showOrderBesarGroup.value = !showOrderBesarGroup.value;
        showOrderKecilGroup.value = false;
        showOption.value = false;
    } else if (dropDownId === 2) {
        showOrderKecilGroup.value = !showOrderKecilGroup.value;
        showOrderBesarGroup.value = false;
        showOption.value = false;
    } else if (dropDownId === 3) {
        showOption.value = !showOption.value;
        showOrderKecilGroup.value = false;
        showOrderBesarGroup.value = false;
    }
}

const logout = () => {router.post(route('logout'))};
</script>
<template>
    <div class="h-full min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
        <div class="w-full bg-white shadow-lg px-6 py-4 flex justify-between items-center sticky top-0 z-50 rounded-lg">
            <!-- Logo Peruri -->
            <img :src="'/img/peruri.png'" class="w-24" />

            <!-- Navigation -->
            <div class="flex justify-start items-center gap-8">
                <!-- Order Besar -->
                <div class="relative">
                    <div @click="toggleDropdown(1)" class="flex items-center cursor-pointer hover:text-blue-600 transition duration-200">
                        <span class="text-sm font-semibold text-gray-800">Order Besar</span>
                        <ChevronDown class="w-5 h-5 ml-1" />
                    </div>
                    <Transition name="fade">
                        <div v-show="showOrderBesarGroup" class="absolute left-0 mt-2 w-48 bg-white shadow-lg z-10 rounded-lg">
                            <NavLink
                                :href="route('orderBesar.poSiapVerif')"
                                :active="route().current('orderBesar.poSiapVerif')"
                                class="block text-gray-700 px-4 py-2 transition duration-200 ease-in-out hover:bg-gray-100 w-full">
                                Order Siap Periksa
                            </NavLink>
                            <NavLink
                                :href="route('orderBesar.registerNomorPo')"
                                :active="route().current('orderBesar.registerNomorPo')"
                                class="block text-gray-700 px-4 py-2 transition duration-200 ease-in-out hover:bg-gray-100 w-full">
                                Register Nomor PO
                            </NavLink>
                        </div>
                    </Transition>
                </div>

                <!-- Order Kecil -->
                <div class="relative">
                    <div @click="toggleDropdown(2)" class="flex items-center cursor-pointer hover:text-blue-600 transition duration-200">
                        <span class="text-sm font-semibold text-gray-800">Order Kecil</span>
                        <ChevronDown class="w-5 h-5 ml-1" />
                    </div>
                    <Transition name="fade">
                        <div v-show="showOrderKecilGroup" class="absolute left-0 mt-2 w-48 bg-white shadow-lg z-10 rounded-lg">
                            <NavLink
                                :href="route('orderKecil.cetakLabel')"
                                :active="route().current('orderKecil.cetakLabel')"
                                class="block text-gray-700 px-4 py-2 transition duration-200 ease-in-out hover:bg-gray-100 w-full">
                                Cetak Label
                            </NavLink>
                        </div>
                    </Transition>
                </div>

                <!-- Monitoring Verifikasi -->
                <NavLink
                    :href="route('monitoringProduksi.statusVerif.index')"
                    :active="route().current('monitoringProduksi.statusVerif.index')"
                    class="text-gray-800 px-2 py-1 transition duration-200 ease-in-out hover:text-blue-600">
                    Monitoring Produksi
                </NavLink>

                <!-- Data Production Order -->
                <NavLink
                    :href="route('dataPo.index',0)"
                    :active="route().current('dataPo.index')"
                    class="text-gray-800 px-2 py-1 transition duration-200 ease-in-out hover:text-blue-600">
                    Data PO
                </NavLink>

                <!-- Hasil Produksi Pegawai -->
                <NavLink
                    :href="route('monitoringProduksi.produksiPegawai')"
                    :active="route().current('monitoringProduksi.produksiPegawai')"
                    class="text-gray-800 px-2 py-1 transition duration-200 ease-in-out hover:text-blue-600">
                    Produksi Pegawai
                </NavLink>

                <!-- Create User -->
                <NavLink v-if="role === 1"
                    :href="route('createUser.index')"
                    :active="route().current('createUser.index')"
                    class="text-gray-800 px-2 py-1 transition duration-200 ease-in-out hover:text-blue-600">
                    Create User
                </NavLink>
            </div>

            <!-- Option -->
            <div class="relative">
                <div @click="toggleDropdown(3)" class="flex items-center cursor-pointer p-2 rounded-lg hover:bg-gray-100 transition duration-200 ease-in-out">
                    <Settings class="w-6 h-6 text-gray-800" />
                </div>
                <Transition name="fade">
                    <div v-show="showOption" class="absolute -left-20 mt-2 w-48 bg-white shadow-lg rounded-lg z-10">
                        <div class="flex flex-col">
                            <NavLink
                                :href="route('changePassword.index')"
                                :active="route().current('changePassword.index')"
                                class="text-gray-800 px-2 py-1 transition duration-200 ease-in-out hover:text-blue-600">
                                Ganti Password
                            </NavLink>

                            <button
                                type="button"
                                @click="logout()"
                                class="inline-flex text-nowrap items-center pt-1 border-b-2 border-transparent text-sm font-medium leading-5 hover:border-violet-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 text-gray-800 px-2 py-1 transition duration-200 ease-in-out hover:text-blue-600">
                                Logout
                            </button>

                        </div>
                    </div>
                </Transition>
            </div>
        </div>
        <slot />
    </div>
</template>
