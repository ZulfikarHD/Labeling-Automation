<script setup>
import NavLink from "@/Components/Navigation/NavLink.vue"
import NavDropdown from "@/Components/Navigation/NavDropdown.vue"
import DropdownMenu from "@/Components/Navigation/DropdownMenu.vue"
import DropdownLink from "@/Components/DropdownLink.vue"
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
    <div class="flex flex-wrap items-center gap-4 md:gap-6">
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
                <DropdownLink
                    :href="route('orderBesar.poSiapVerif')"
                    class="flex items-center gap-2 hover:text-blue-600 dark:hover:text-blue-400"
                >
                    <FileCheck
                        class="w-4 h-4 text-slate-500 dark:text-slate-400"
                    />
                    <span>Order Siap Periksa</span>
                </DropdownLink>
                <DropdownLink
                    :href="route('orderBesar.registerNomorPo')"
                    class="flex items-center gap-2 hover:text-blue-600 dark:hover:text-blue-400"
                >
                    <ClipboardList
                        class="w-4 h-4 text-slate-500 dark:text-slate-400"
                    />
                    <span>Register Nomor PO</span>
                </DropdownLink>
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
                <DropdownLink
                    :href="route('orderKecil.cetakLabel')"
                    class="flex items-center gap-2 hover:text-blue-600 dark:hover:text-blue-400"
                >
                    <FileText
                        class="w-4 h-4 text-slate-500 dark:text-slate-400"
                    />
                    <span>Cetak Label</span>
                </DropdownLink>
            </DropdownMenu>
        </div>

        <!-- Regular Navigation Links -->
        <NavLink
            :href="route('monitoringProduksi.statusVerif.index')"
        >
            <Activity class="w-4 h-4 group-hover:text-blue-600" />
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">
                Monitoring Produksi
            </span>
        </NavLink>

        <NavLink
            :href="route('dataPo.index', 0)"
        >
            <FileSpreadsheet class="w-4 h-4 group-hover:text-blue-600" />
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">
                Data PO
            </span>
        </NavLink>

        <NavLink
            :href="route('monitoringProduksi.produksiPegawai')"
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
        >
            <Users class="w-4 h-4 group-hover:text-blue-600" />
            <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">
                Create User
            </span>
        </NavLink>
    </div>
</template>
