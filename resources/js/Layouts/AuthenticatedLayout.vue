<script setup lang="ts">
import { ref } from 'vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="bg-slate-100 w-full h-full min-h-screen">
        <div class="w-full bg-slate-50 drop-shadow-md px-8 py-4 flex justify-between gap-4 items-center sticky top-0 z-50 dark:bg-slate-900">
            <!-- Logo Peruri -->
            <img :src="'/img/peruri.png'" class="w-24" />

            <!-- Navigation -->
            <div class="flex justify-start items-center gap-6">
                <NavLink :href="'/'" :active="$page.url === '/'
                        ? true
                        : false
                    ">Pilih Produk</NavLink>
                <NavLink :href="'/non-perekat/non-personal/entry-po'">Entry PO</NavLink>
                <NavLink :href="'/non-perekat/non-personal/pic/listPo/0'">List PO Terdaftar</NavLink>
                <NavLink :href="'/non-perekat/non-personal/pic/monitorVerifikasi'">Monitoring Verifikasi</NavLink>
                <NavLink :href="'/non-perekat/non-personal/verif'">Pilih Barang Verifikasi</NavLink>
                <NavLink :href="'/non-perekat/personal'">Cetak Label Personal</NavLink>
                <NavLink :href="'/produksi-pegawai'">Produksi Pegawai</NavLink>
            </div>

            <!-- Option -->
            <div>

            </div>
        </div>
        <slot />
    </div>
</template>
