<script setup>
import NavLink from "@/Components/Navigation/NavLink.vue"
import NavDropdown from "@/Components/Navigation/NavDropdown.vue"
import DropdownMenu from "@/Components/Navigation/DropdownMenu.vue"
import {
    FileText,
    FileCheck,
    ClipboardList,
    Users,
    FileSpreadsheet,
    Activity
} from "lucide-vue-next"

defineProps({
    dropdowns: {
        type: Object,
        required: true
    },
    role: {
        type: Number,
        required: true
    }
})

const emit = defineEmits(['toggleDropdown'])
</script>

<template>
    <div class="flex items-center gap-6">
        <!-- Order Besar Dropdown -->
        <div class="relative">
            <NavDropdown
                label="Order Besar"
                :icon="FileText"
                :is-open="dropdowns.orderBesar"
                @toggle="$emit('toggleDropdown', 'orderBesar')"
            />
            <DropdownMenu
                :show="dropdowns.orderBesar"
                class="w-56"
            >
                <NavLink
                    :href="route('orderBesar.poSiapVerif')"
                    class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full"
                >
                    <FileCheck
                        class="w-4 h-4 text-slate-500 dark:text-slate-400"
                    />
                    <span class="text-sm text-slate-600 dark:text-slate-300">
                        Order Siap Periksa
                    </span>
                </NavLink>
                <NavLink
                    :href="route('orderBesar.registerNomorPo')"
                    class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full"
                >
                    <ClipboardList
                        class="w-4 h-4 text-slate-500 dark:text-slate-400"
                    />
                    <span class="text-sm text-slate-600 dark:text-slate-300">
                        Register Nomor PO
                    </span>
                </NavLink>
            </DropdownMenu>
        </div>

        <!-- Order Kecil Dropdown -->
        <div class="relative">
            <NavDropdown
                label="Order Kecil"
                :icon="FileText"
                :is-open="dropdowns.orderKecil"
                @toggle="$emit('toggleDropdown', 'orderKecil')"
            />
            <DropdownMenu
                :show="dropdowns.orderKecil"
                class="w-48"
            >
                <NavLink
                    :href="route('orderKecil.cetakLabel')"
                    class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full"
                >
                    <FileText
                        class="w-4 h-4 text-slate-500 dark:text-slate-400"
                    />
                    <span class="text-sm text-slate-600 dark:text-slate-300">
                        Cetak Label
                    </span>
                </NavLink>
            </DropdownMenu>
        </div>

        <!-- Regular Navigation Links -->
        <NavLink
            :href="route('monitoringProduksi.statusVerif.index')"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all group"
        >
            <Activity class="w-4 h-4 group-hover:text-blue-600" />
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">
                Monitoring Produksi
            </span>
        </NavLink>

        <NavLink
            :href="route('dataPo.index', 0)"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all group"
        >
            <FileSpreadsheet class="w-4 h-4 group-hover:text-blue-600" />
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">
                Data PO
            </span>
        </NavLink>

        <NavLink
            :href="route('monitoringProduksi.produksiPegawai')"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all group"
        >
            <Users class="w-4 h-4 group-hover:text-blue-600" />
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">
                Produksi Pegawai
            </span>
        </NavLink>

        <!-- Admin Only Navigation -->
        <NavLink
            v-if="role === 1"
            :href="route('createUser.index')"
            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all group"
        >
            <Users class="w-4 h-4 group-hover:text-blue-600" />
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">
                Create User
            </span>
        </NavLink>
    </div>
</template>
