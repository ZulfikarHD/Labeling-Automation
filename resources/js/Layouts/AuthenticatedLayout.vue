<script setup>
import { usePage } from "@inertiajs/vue3"
import { router } from '@inertiajs/vue3'
import AppTheme from "@/Layouts/Navigation/AppTheme.vue"
import MainNavigation from "@/Layouts/Navigation/MainNavigation.vue"
import NavLink from "@/Components/Navigation/NavLink.vue"
import {
    Settings,
    KeyRound,
    LogOut,
    Sun,
    Moon,
    Menu,
    X,
} from "lucide-vue-next"
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

// Get user role from auth props
const { props } = usePage()
const role = props.auth.user.role
const userName = props.auth.user.name
const isMobileMenuOpen = ref(false)
const isOptionsOpen = ref(false)
const isMounted = ref(false)

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const toggleOptions = () => {
    isOptionsOpen.value = !isOptionsOpen.value
}

const logout = async () => {
    try {
        await router.post(route('logout'))
    } catch (error) {
        console.error('Error during logout:', error)
    }
}

// Close mobile menu when clicking outside
const closeMobileMenu = () => {
    isMobileMenuOpen.value = false
}

// Close user profile dropdown when clicking outside
const closeOptionsDropdown = (e) => {
    try {
        if (isMounted.value && !e.target.closest('[data-user-dropdown]')) {
            isOptionsOpen.value = false
        }
    } catch (error) {
        console.error('Error closing options dropdown:', error)
        isOptionsOpen.value = false
    }
}

// Handle escape key to close dropdowns
const handleEscKey = (e) => {
    if (e.key === "Escape") {
        if (isOptionsOpen.value) {
            isOptionsOpen.value = false
        }
        if (isMobileMenuOpen.value) {
            isMobileMenuOpen.value = false
        }
    }
}

onMounted(() => {
    isMounted.value = true
    if (typeof window !== "undefined") {
        document.addEventListener("click", closeOptionsDropdown)
        document.addEventListener("keydown", handleEscKey)
    }
})

onBeforeUnmount(() => {
    isMounted.value = false
    if (typeof window !== "undefined") {
        document.removeEventListener("click", closeOptionsDropdown)
        document.removeEventListener("keydown", handleEscKey)
    }
})

// Get user initials for avatar
const userInitials = computed(() => {
    return userName
        ? userName
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2)
        : "U"
})
</script>

<template>
    <AppTheme v-slot="{ isDark, toggleDarkMode }">
        <div
            class="flex flex-col min-h-screen bg-gradient-to-br transition-colors duration-300 from-slate-50 via-blue-50/30 to-indigo-50/50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-800"
        >
            <!-- Enhanced Navigation Bar -->
            <nav
                class="sticky top-0 z-[100] w-full border-b border-slate-200/60 dark:border-slate-700/60 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl shadow-sm py-3"
            >
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <!-- Logo Section -->
                        <div class="flex items-center space-x-4">
                            <div
                                class="flex-shrink-0 p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg"
                            >
                                <img
                                    src="/labeling/img/peruri.png"
                                    class="object-contain w-8 h-8 brightness-0 invert"
                                    alt="Logo Peruri"
                                />
                            </div>
                        </div>

                        <!-- Desktop Navigation -->
                        <div class="hidden lg:block">
                            <MainNavigation :role="role" />
                        </div>

                        <!-- Right Side Actions -->
                        <div class="flex items-center space-x-2">

                            <!-- Dark Mode Toggle -->
                            <button
                                @click="toggleDarkMode"
                                class="p-2 rounded-full transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                                :aria-label="
                                    isDark
                                        ? 'Switch to light mode'
                                        : 'Switch to dark mode'
                                "
                            >
                                <Moon
                                    v-if="isDark"
                                    class="w-5 h-5 text-amber-400"
                                />
                                <Sun v-else class="w-5 h-5" />
                            </button>

                            <!-- User Profile Dropdown -->
                            <div class="hidden relative md:block" data-user-dropdown>
                                <button
                                    @click.stop="toggleOptions"
                                    class="flex items-center p-1 pr-3 space-x-3 text-sm rounded-full transition-all duration-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700"
                                    :class="
                                        isOptionsOpen
                                            ? 'ring-2 ring-blue-500 ring-offset-2 dark:ring-offset-slate-900'
                                            : ''
                                    "
                                >
                                    <div
                                        class="flex justify-center items-center w-8 h-8 text-xs font-semibold text-white bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full shadow-md"
                                    >
                                        {{ userInitials }}
                                    </div>
                                    <span
                                        class="hidden font-medium sm:block text-slate-700 dark:text-slate-200"
                                    >
                                        {{ userName }}
                                    </span>
                                    <Settings class="w-4 h-4 text-slate-500" />
                                </button>

                                <!-- Enhanced Dropdown Menu -->
                                <div v-show="isOptionsOpen"
                                    class="absolute right-0 mt-2 w-64 origin-top-right rounded-xl bg-white dark:bg-slate-800 shadow-xl ring-1 ring-black ring-opacity-5 border border-slate-200 dark:border-slate-700 z-[110]"
                                    @click.stop>
                                    <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="flex justify-center items-center w-10 h-10 text-sm font-semibold text-white bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full"
                                            >
                                                {{ userInitials }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                    {{ userName }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{
                                                        role === 1
                                                            ? "Administrator"
                                                            : "User"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="py-2">
                                        <NavLink
                                            :href="route('changePassword.index')"
                                            class="flex items-center px-4 py-3 space-x-3 text-sm transition-colors duration-200 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50"
                                        >
                                            <KeyRound class="w-4 h-4" />
                                            <span>Ganti Password</span>
                                        </NavLink>

                                        <button
                                            @click="logout"
                                            class="flex items-center px-4 py-3 space-x-3 w-full text-sm text-red-600 transition-colors duration-200 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                        >
                                            <LogOut class="w-4 h-4" />
                                            <span>Logout</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Menu Button -->
                            <button
                                @click="toggleMobileMenu"
                                class="p-2 rounded-md transition-all duration-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100 lg:hidden"
                                aria-label="Toggle mobile menu"
                            >
                                <Menu
                                    v-if="!isMobileMenuOpen"
                                    class="w-6 h-6"
                                />
                                <X v-else class="w-6 h-6" />
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Enhanced Mobile Navigation Menu -->
            <div
                v-show="isMobileMenuOpen"
                class="fixed inset-0 z-[90] lg:hidden"
                @click="closeMobileMenu"
            >
                <div
                    class="fixed inset-y-0 right-0 w-full max-w-sm bg-white border-l shadow-xl dark:bg-slate-900 border-slate-200 dark:border-slate-700"
                    @click.stop
                >
                    <!-- Mobile Menu Header -->
                    <div
                        class="flex justify-between items-center p-4 border-b border-slate-200 dark:border-slate-700"
                    >
                        <div class="flex items-center space-x-3">
                            <div
                                class="flex justify-center items-center w-10 h-10 text-sm font-semibold text-white bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full"
                            >
                                {{ userInitials }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                    {{ userName }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{
                                        role === 1 ? "Administrator" : "User"
                                    }}
                                </p>
                            </div>
                        </div>
                        <button
                            @click="closeMobileMenu"
                            class="p-2 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                        >
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Mobile Navigation Content -->
                    <div class="overflow-y-auto flex-1 p-4">
                        <div class="space-y-6">
                            <!-- Navigation Links -->
                            <div>
                                <h3
                                    class="mb-3 text-xs font-semibold tracking-wider uppercase text-slate-500 dark:text-slate-400"
                                >
                                    Navigation
                                </h3>
                                <MainNavigation :role="role" />
                            </div>

                            <!-- Account Options -->
                            <div class="pt-6 border-t border-slate-200 dark:border-slate-700">
                                <h3
                                    class="mb-3 text-xs font-semibold tracking-wider uppercase text-slate-500 dark:text-slate-400"
                                >
                                    Account
                                </h3>
                                <div class="space-y-1">
                                    <NavLink
                                        :href="route('changePassword.index')"
                                        class="flex items-center px-3 py-2 space-x-3 text-sm rounded-lg transition-colors duration-200 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800"
                                    >
                                        <KeyRound class="w-4 h-4" />
                                        <span>Ganti Password</span>
                                    </NavLink>

                                    <button
                                        @click="logout"
                                        class="flex items-center px-3 py-2 space-x-3 w-full text-sm text-red-600 rounded-lg transition-colors duration-200 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                                    >
                                        <LogOut class="w-4 h-4" />
                                        <span>Logout</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Main Content -->
            <main class="flex-1">
                <div class="px-4 py-6 mx-auto sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>

            <!-- Background dekoratif dengan grid pattern dan gradient blur -->
            <div class="absolute inset-0 -z-10 overflow-hidden">
                <div
                    class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:24px_24px]">
                </div>
                <div
                    class="absolute left-0 right-0 top-0 -z-10 m-auto h-[310px] w-[310px] rounded-full bg-cyan-400 dark:bg-cyan-600 opacity-20 blur-[100px]">
                </div>
                <div
                    class="absolute right-0 top-0 -z-10 h-[310px] w-[310px] rounded-full bg-blue-400 dark:bg-blue-600 opacity-20 blur-[100px]">
                </div>
            </div>

            <!-- Footer -->
            <footer
                class="w-screen border-t backdrop-blur-sm border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-900/50"
            >
                <div
                    class="px-4 py-4 mx-auto max-w-7xl text-center sm:px-6 lg:px-8"
                >
                    <p
                        class="text-sm text-slate-500 dark:text-slate-400"
                    >
                        © 2024 Automate Labeling System Pita Cukai - Peruri. All rights reserved.
                    </p>
                </div>
            </footer>
        </div>
    </AppTheme>
</template>
