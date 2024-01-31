<script setup>
import { ref } from "vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="h-full min-h-screen bg-gradient-to-br from-slate-50 to-slate-200">
        <div class="w-full bg-slate-50 drop-shadow-md px-8 py-4 flex justify-between gap-4 items-center sticky top-0 z-50">
            <!-- Logo Peruri -->
            <img :src="'/img/peruri.png'" class="w-24" />

            <!-- Navigation -->
            <div class="flex justify-start items-center gap-6">
                <NavLink :href="'/'" :active="$page.url === '/'
                        ? true
                        : false
                    ">Pilih Produk</NavLink>
                <NavLink :href="'/non-perekat/non-personal/generateLabels'" :active="$page.url === '/non-perekat/non-personal/generateLabels'
                        ? true
                        : false
                    ">Generate Label Verifikasi</NavLink>
                <NavLink :href="'/non-perekat/non-personal/pic/listPo/0'" :active="$page.url === '/non-perekat/non-personal/pic/listPo/0'
                        ? true
                        : false
                    ">List PO Terdaftar</NavLink>
                <NavLink :href="'/non-perekat/non-personal/pic/monitorVerifikasi'" :active="$page.url ===
                        '/non-perekat/non-personal/pic/monitorVerifikasi'
                        ? true
                        : false
                    ">Monitoring Verifikasi</NavLink>
                <NavLink :href="'/non-perekat/non-personal/verif'" :active="$page.url === '/non-perekat/non-personal/verif'
                        ? true
                        : false
                    ">Pilih Barang Verifikasi</NavLink>
                <NavLink :href="'/non-perekat/personal'" :active="$page.url === '/non-perekat/personal'
                        ? true
                        : false
                    ">Cetak Label Personal</NavLink>
                <NavLink :href="'/produksi-pegawai'" :active="$page.url === '/produksi-pegawai'
                        ? true
                        : false
                    ">Produksi Pegawai</NavLink>
            </div>

            <!-- Option -->
            <div></div>
        </div>
        <slot />
    </div>
</template>
