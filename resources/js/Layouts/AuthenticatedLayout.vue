<script setup>
import { ref, watch } from "vue";
import NavLink from "@/Components/NavLink.vue";
import { Settings, ChevronDown, FileText, FileCheck, ClipboardList, Users, FileSpreadsheet, Activity, KeyRound, LogOut, Sun, Moon } from "lucide-vue-next";
import { router, usePage } from "@inertiajs/vue3";

const { props } = usePage();
const role = props.auth.user.role;

// Dark mode state
const isDark = ref(localStorage.getItem('darkMode') === 'true' ||
                  (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches));

// Toggle dark mode
const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    localStorage.setItem('darkMode', isDark.value);
    document.documentElement.classList.toggle('dark', isDark.value);
};

// Initialize dark mode on mount
if (typeof window !== 'undefined') {
    document.documentElement.classList.toggle('dark', isDark.value);
}

// Watch for system preference changes
if (typeof window !== 'undefined') {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('darkMode')) { // Only change if manual toggle is not set
            isDark.value = e.matches;
            document.documentElement.classList.toggle('dark', isDark.value);
        }
    });
}

// Use single dropdown state object
const dropdowns = ref({
    orderBesar: false,
    orderKecil: false,
    options: false
});

// Close all dropdowns when clicking outside
const closeDropdowns = (e) => {
    if (!e.target.closest('.dropdown-trigger')) {
        Object.keys(dropdowns.value).forEach(key => {
            dropdowns.value[key] = false;
        });
    }
};

// Add event listener
if (typeof window !== 'undefined') {
    document.addEventListener('click', closeDropdowns);
}

const toggleDropdown = (dropdown) => {
    // Close other dropdowns
    Object.keys(dropdowns.value).forEach(key => {
        if (key !== dropdown) dropdowns.value[key] = false;
    });
    // Toggle target dropdown
    dropdowns.value[dropdown] = !dropdowns.value[dropdown];
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
        <nav class="w-full bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm shadow-lg px-8 py-4 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <!-- Logo -->
                <img :src="'/img/peruri.png'" class="w-24 hover:opacity-80 transition-opacity" alt="Peruri Logo" />

                <!-- Main Navigation -->
                <div class="flex items-center gap-6">
                    <!-- Order Besar Dropdown -->
                    <div class="relative">
                        <button @click.stop="toggleDropdown('orderBesar')"
                                class="dropdown-trigger group flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                            <FileText class="w-4 h-4 text-slate-600 dark:text-slate-300 group-hover:text-blue-600" />
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">Order Besar</span>
                            <ChevronDown class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition-transform duration-200"
                                       :class="{ 'rotate-180': dropdowns.orderBesar }" />
                        </button>

                        <transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 translate-y-1"
                        >
                            <div v-show="dropdowns.orderBesar"
                                class="absolute mt-2 w-56 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <NavLink :href="route('orderBesar.poSiapVerif')"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full">
                                    <FileCheck class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                                    <span class="text-sm text-slate-600 dark:text-slate-300">Order Siap Periksa</span>
                                </NavLink>
                                <NavLink :href="route('orderBesar.registerNomorPo')"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full">
                                    <ClipboardList class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                                    <span class="text-sm text-slate-600 dark:text-slate-300">Register Nomor PO</span>
                                </NavLink>
                            </div>
                        </transition>
                    </div>

                    <!-- Order Kecil Dropdown -->
                    <div class="relative">
                        <button @click.stop="toggleDropdown('orderKecil')"
                                class="dropdown-trigger group flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                            <FileText class="w-4 h-4 text-slate-600 dark:text-slate-300 group-hover:text-blue-600" />
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">Order Kecil</span>
                            <ChevronDown class="w-4 h-4 text-slate-400 group-hover:text-blue-600 transition-transform duration-200"
                                       :class="{ 'rotate-180': dropdowns.orderKecil }" />
                        </button>

                        <transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 translate-y-1"
                        >
                            <div v-show="dropdowns.orderKecil"
                                class="absolute mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <NavLink :href="route('orderKecil.cetakLabel')"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors w-full">
                                    <FileText class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                                    <span class="text-sm text-slate-600 dark:text-slate-300">Cetak Label</span>
                                </NavLink>
                            </div>
                        </transition>
                    </div>

                    <!-- Regular Nav Links -->
                    <NavLink :href="route('monitoringProduksi.statusVerif.index')"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all group">
                        <Activity class="w-4 h-4 group-hover:text-blue-600" />
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">Monitoring Produksi</span>
                    </NavLink>

                    <NavLink :href="route('dataPo.index',0)"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all group">
                        <FileSpreadsheet class="w-4 h-4 group-hover:text-blue-600" />
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">Data PO</span>
                    </NavLink>

                    <NavLink :href="route('monitoringProduksi.produksiPegawai')"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all group">
                        <Users class="w-4 h-4 group-hover:text-blue-600" />
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">Produksi Pegawai</span>
                    </NavLink>

                    <NavLink v-if="role === 1" :href="route('createUser.index')"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all group">
                        <Users class="w-4 h-4 group-hover:text-blue-600" />
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-blue-600">Create User</span>
                    </NavLink>
                </div>

                <!-- Options Dropdown -->
                <div class="flex items-center gap-2">
                    <!-- Dark Mode Toggle -->
                    <button @click="toggleDarkMode"
                            class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                        <Sun v-if="!isDark" class="w-5 h-5 text-slate-600 hover:text-blue-600" />
                        <Moon v-else class="w-5 h-5 text-slate-300 hover:text-blue-600" />
                    </button>

                    <div class="relative">
                        <button @click.stop="toggleDropdown('options')"
                                class="dropdown-trigger p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                            <Settings class="w-5 h-5 text-slate-600 dark:text-slate-300 hover:text-blue-600" />
                        </button>

                        <transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 translate-y-1"
                        >
                            <div v-show="dropdowns.options"
                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-100 dark:border-slate-700 overflow-hidden">
                                <NavLink :href="route('changePassword.index')"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    <KeyRound class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                                    <span class="text-sm text-slate-600 dark:text-slate-300">Ganti Password</span>
                                </NavLink>

                                <button @click="logout"
                                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    <LogOut class="w-4 h-4 text-slate-500 dark:text-slate-400" />
                                    <span class="text-sm text-slate-600 dark:text-slate-300">Logout</span>
                                </button>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-full mx-auto px-4 py-4">
            <slot />
        </main>
    </div>
</template>
