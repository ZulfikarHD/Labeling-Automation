<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import NavLink from "@/Components/Navigation/NavLink.vue";
import NavDropdown from "@/Components/Navigation/NavDropdown.vue";
import DropdownMenu from "@/Components/Navigation/DropdownMenu.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import {
    FileText,
    FileCheck,
    ClipboardList,
    Users,
    FileSpreadsheet,
    Activity,
} from "lucide-vue-next";

defineProps({
    role: {
        type: Number,
        required: true,
    },
});

// Define dropdown state interface
const dropdownState = {
    orderBesar: false,
    cetakLabel: false,
    options: false,
    monitoringProduksi: false,
};

const dropdowns = ref({ ...dropdownState });

// Track if component is mounted
const isMounted = ref(false);

const resetDropdowns = () => {
    Object.keys(dropdowns.value).forEach((key) => {
        dropdowns.value[key] = false;
    });
};

const closeDropdowns = (e) => {
    try {
        if (isMounted.value && !e.target.closest(".dropdown-trigger")) {
            resetDropdowns();
        }
    } catch (error) {
        console.error("Error closing dropdowns:", error);
        resetDropdowns();
    }
};

const toggleDropdown = (dropdown) => {
    try {
        if (!Object.keys(dropdownState).includes(dropdown)) {
            console.warn(`Invalid dropdown key: ${dropdown}`);
            return;
        }

        // Close other dropdowns
        Object.keys(dropdowns.value).forEach((key) => {
            if (key !== dropdown) dropdowns.value[key] = false;
        });

        // Toggle target dropdown
        dropdowns.value[dropdown] = !dropdowns.value[dropdown];
    } catch (error) {
        console.error("Error toggling dropdown:", error);
        resetDropdowns();
    }
};

// Handle escape key to close dropdowns
const handleEscKey = (e) => {
    if (e.key === "Escape") {
        resetDropdowns();
    }
};

onMounted(() => {
    isMounted.value = true;

    if (typeof window !== "undefined") {
        document.addEventListener("click", closeDropdowns);
        document.addEventListener("keydown", handleEscKey);
    }
});

onBeforeUnmount(() => {
    isMounted.value = false;

    if (typeof window !== "undefined") {
        document.removeEventListener("click", closeDropdowns);
        document.removeEventListener("keydown", handleEscKey);
    }
});
</script>

<template>
    <nav class="flex flex-col lg:flex-row lg:items-center gap-2 lg:gap-4">
        <!-- Register Nomor PO -->
        <NavLink :href="route('orderBesar.registerNomorPo')">
            <ClipboardList />
            Register Nomor PO
        </NavLink>

        <!-- Order Siap Periksa -->
        <NavLink :href="route('orderBesar.poSiapVerif')">
            <FileCheck />
            Order Siap Periksa
        </NavLink>

        <!-- Cetak Label Dropdown -->
        <div class="relative">
            <NavDropdown
                label="Cetak Label"
                :icon="FileText"
                :is-open="dropdowns.cetakLabel"
                @toggle="toggleDropdown('cetakLabel')"
            />
            <DropdownMenu
                :show="dropdowns.cetakLabel"
                class="lg:absolute lg:top-full lg:left-0 w-64"
            >
                <DropdownLink :href="route('orderKecil.cetakLabel')">
                    <div class="flex items-center gap-3">
                        <FileText
                            class="h-4 w-4 text-slate-500 dark:text-slate-400"
                        />
                        <span>Cetak Label Personal</span>
                    </div>
                </DropdownLink>
                <DropdownLink :href="route('printLabel.inspeksi')">
                    <div class="flex items-center gap-3">
                        <FileText
                            class="h-4 w-4 text-slate-500 dark:text-slate-400"
                        />
                        <span>Cetak Label Inspeksi</span>
                    </div>
                </DropdownLink>
                <DropdownLink :href="route('printLabel.mmea')">
                    <div class="flex items-center gap-3">
                        <FileText
                            class="h-4 w-4 text-slate-500 dark:text-slate-400"
                        />
                        <span>Cetak Label MMEA</span>
                    </div>
                </DropdownLink>
            </DropdownMenu>
        </div>

        <!-- Data PO -->
        <NavLink :href="route('dataPo.index', 0)">
            <FileSpreadsheet />
            Data PO
        </NavLink>

        <!-- Monitoring Produksi Dropdown -->
        <div class="relative">
            <NavDropdown
                label="Monitoring Produksi"
                :icon="Activity"
                :is-open="dropdowns.monitoringProduksi"
                @toggle="toggleDropdown('monitoringProduksi')"
            />
            <DropdownMenu
                :show="dropdowns.monitoringProduksi"
                class="lg:absolute lg:top-full lg:left-0 w-64"
            >
                <DropdownLink
                    :href="route('monitoringProduksi.produksiPegawai')"
                >
                    <div class="flex items-center gap-3">
                        <Users
                            class="h-4 w-4 text-slate-500 dark:text-slate-400"
                        />
                        <span>Produksi Pegawai</span>
                    </div>
                </DropdownLink>
                <DropdownLink
                    :href="route('monitoringProduksi.statusVerif.index')"
                >
                    <div class="flex items-center gap-3">
                        <Activity
                            class="h-4 w-4 text-slate-500 dark:text-slate-400"
                        />
                        <span>Status Verifikasi</span>
                    </div>
                </DropdownLink>
            </DropdownMenu>
        </div>

        <!-- Admin Only Navigation -->
        <NavLink v-if="role === 1" :href="route('createUser.index')">
            <Users />
            Create User
        </NavLink>
    </nav>
</template>
