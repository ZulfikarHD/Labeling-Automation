<script setup>
import { usePage } from "@inertiajs/vue3"
import { router } from '@inertiajs/vue3'
import AppTheme from "@/Layouts/Navigation/AppTheme.vue"
import MainNavigation from "@/Layouts/Navigation/MainNavigation.vue"
import NavDropdown from "@/Components/Navigation/NavDropdown.vue"
import DropdownMenu from "@/Components/Navigation/DropdownMenu.vue"
import NavLink from "@/Components/Navigation/NavLink.vue"
import {
    Settings,
    KeyRound,
    LogOut,
    Sun,
    Moon,
    Menu,
    X,
    User,
    Bell,
} from "lucide-vue-next"
import { ref, computed } from 'vue'

// Get user role from auth props
const { props } = usePage()
const role = props.auth.user.role
const userName = props.auth.user.name
const isMobileMenuOpen = ref(false)
const isOptionsOpen = ref(false)

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
            class="flex flex-col min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-800 transition-colors duration-300"
        >
            <!-- Enhanced Navigation Bar -->
            <nav
                class="sticky top-0 z-[100] w-full border-b border-slate-200/60 dark:border-slate-700/60 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl shadow-sm"
            >
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <!-- Logo Section -->
                        <div class="flex items-center space-x-4">
                            <div
                                class="flex-shrink-0 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 p-2 shadow-lg"
                            >
                                <img
                                    src="/labeling/img/peruri.png"
                                    class="h-8 w-8 object-contain brightness-0 invert"
                                    alt="Logo Peruri"
                                />
                            </div>
                        </div>

                        <!-- Desktop Navigation -->
                        <div class="hidden lg:block">
                            <MainNavigation
                                :role="role"
                            />
                        </div>

                        <!-- Right Side Actions -->
                        <div class="flex items-center space-x-2">

                            <!-- Dark Mode Toggle -->
                            <button
                                @click="toggleDarkMode"
                                class="rounded-full p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100 transition-all duration-200"
                                :aria-label="
                                    isDark
                                        ? 'Switch to light mode'
                                        : 'Switch to dark mode'
                                "
                            >
                                <Moon
                                    v-if="isDark"
                                    class="h-5 w-5 text-amber-400"
                                />
                                <Sun v-else class="h-5 w-5" />
                            </button>

                            <!-- User Profile Dropdown -->
                            <div class="relative hidden md:block">
                                <button
                                    @click="toggleOptions"
                                    class="flex items-center space-x-3 rounded-full bg-slate-100 dark:bg-slate-800 p-1 pr-3 text-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-all duration-200"
                                    :class="
                                        isOptionsOpen
                                            ? 'ring-2 ring-blue-500 ring-offset-2 dark:ring-offset-slate-900'
                                            : ''
                                    "
                                >
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-xs font-semibold text-white shadow-md"
                                    >
                                        {{ userInitials }}
                                    </div>
                                    <span
                                        class="hidden sm:block font-medium text-slate-700 dark:text-slate-200"
                                    >
                                        {{ userName }}
                                    </span>
                                    <Settings class="h-4 w-4 text-slate-500" />
                                </button>

                                <!-- Enhanced Dropdown Menu -->
                                <div
                                    v-show="isOptionsOpen"
                                    class="absolute right-0 mt-2 w-64 origin-top-right rounded-xl bg-white dark:bg-slate-800 shadow-xl ring-1 ring-black ring-opacity-5 border border-slate-200 dark:border-slate-700 z-[110]"
                                >
                                    <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-semibold text-white"
                                            >
                                                {{ userInitials }}
                                            </div>
                                            <div>
                                                <p
                                                    class="text-sm font-semibold text-slate-900 dark:text-slate-100"
                                                >
                                                    {{ userName }}
                                                </p>
                                                <p
                                                    class="text-xs text-slate-500 dark:text-slate-400"
                                                >
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
                                            class="flex items-center space-x-3 px-4 py-3 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                                        >
                                            <KeyRound class="h-4 w-4" />
                                            <span>Ganti Password</span>
                                        </NavLink>

                                        <button
                                            @click="logout"
                                            class="flex w-full items-center space-x-3 px-4 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200"
                                        >
                                            <LogOut class="h-4 w-4" />
                                            <span>Logout</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Menu Button -->
                            <button
                                @click="toggleMobileMenu"
                                class="rounded-md p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100 lg:hidden transition-all duration-200"
                                aria-label="Toggle mobile menu"
                            >
                                <Menu
                                    v-if="!isMobileMenuOpen"
                                    class="h-6 w-6"
                                />
                                <X v-else class="h-6 w-6" />
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
                    class="fixed inset-y-0 right-0 w-full max-w-sm bg-white dark:bg-slate-900 shadow-xl border-l border-slate-200 dark:border-slate-700"
                    @click.stop
                >
                    <!-- Mobile Menu Header -->
                    <div
                        class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 p-4"
                    >
                        <div class="flex items-center space-x-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-semibold text-white"
                            >
                                {{ userInitials }}
                            </div>
                            <div>
                                <p
                                    class="text-sm font-semibold text-slate-900 dark:text-slate-100"
                                >
                                    {{ userName }}
                                </p>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        role === 1 ? "Administrator" : "User"
                                    }}
                                </p>
                            </div>
                        </div>
                        <button
                            @click="closeMobileMenu"
                            class="rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Mobile Navigation Content -->
                    <div class="flex-1 overflow-y-auto p-4">
                        <div class="space-y-6">
                            <!-- Navigation Links -->
                            <div>
                                <h3
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3"
                                >
                                    Navigation
                                </h3>
                                <MainNavigation :role="role" />
                            </div>

                            <!-- Account Options -->
                            <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                                <h3
                                    class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3"
                                >
                                    Account
                                </h3>
                                <div class="space-y-1">
                                    <NavLink
                                        :href="route('changePassword.index')"
                                        class="flex items-center space-x-3 rounded-lg px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors duration-200"
                                    >
                                        <KeyRound class="h-4 w-4" />
                                        <span>Ganti Password</span>
                                    </NavLink>

                                    <button
                                        @click="logout"
                                        class="flex w-full items-center space-x-3 rounded-lg px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-200"
                                    >
                                        <LogOut class="h-4 w-4" />
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
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>

            <!-- Footer -->
            <footer
                class="border-t border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm w-screen"
            >
                <div
                    class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8 text-center"
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
