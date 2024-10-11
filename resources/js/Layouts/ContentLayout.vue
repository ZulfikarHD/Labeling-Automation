<script setup>
import { ref } from "vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";
import GroupLink from "@/Components/GroupLink.vue";

const showingNavigationDropdown = ref(false);
const showOrderBesarGroup = ref(false);
</script>

<template>
    <div class="h-full min-h-screen bg-gradient-to-b from-slate-50 to-slate-100">
        <div class="w-full bg-slate-50 drop-shadow-md px-8 py-4 flex justify-between gap-4 items-center sticky top-0 z-50">
            <!-- Logo Peruri -->
            <img :src="'/img/peruri.png'" class="w-24" />

            <!-- Navigation -->
            <div class="flex justify-start items-center gap-6">
                <GroupLink @click="showOrderBesarGroup = !showOrderBesarGroup"
                    :active="$page.url == route('orderBesar.poSiapVerif') || route('dashboard')">
                    Order Besar
                </GroupLink>
                <!-- Order Besar -->
                <Transition
                    name="accordOrderBesar"
                    enter-active-class="animate__animated animate__tada"
                    leave-active-class="animate__animated animate__bounceOutRight">
                    <div v-if="showOrderBesarGroup">
                        <NavLink
                            :href="route('orderBesar.poSiapVerif') || route('dashboard')"
                            :active="$page.url == route('orderBesar.poSiapVerif') || route('dashboard')">
                            Order Siap Periksa
                        </NavLink>

                        <NavLink
                            :href="route('orderBesar.registerNomorPo')"
                            :active="$page.url == route('orderBesar.registerNomorPo')">
                            Register Nomor PO
                        </NavLink>
                    </div>
                </Transition>

                <!-- Order Kecil -->
                <NavLink
                    :href="route('orderBesar.cetakLabel')"
                    :active="$page.url == route('orderBesar.cetakLabel')">
                    Cetak Label
                </NavLink>

                <!-- Monitoring Verifikasi -->
                <NavLink
                    :href="'/non-perekat/non-personal/pic/monitorVerifikasi'">
                    Monitoring Verifikasi
                </NavLink>

                <!-- Data Production Order -->
                <NavLink
                    :href="'/non-perekat/non-personal/pic/monitorVerifikasi'">
                    Data PO
                </NavLink>

                <!-- Hasil Produksi Pegawai -->
                <NavLink :href="'/non-perekat/non-personal/pic/monitorVerifikasi'">Produksi Pegawai</NavLink>
            </div>

            <!-- Option -->
            <div></div>
        </div>
        <slot />
    </div>
</template>
