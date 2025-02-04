<script setup>
import { usePage } from "@inertiajs/vue3"
import NavigationManager from "@/Layouts/Navigation/NavigationManager.vue"
import AppTheme from "@/Layouts/Navigation/AppTheme.vue"
import MainNavigation from "@/Layouts/Navigation/MainNavigation.vue"
import NavDropdown from "@/Components/Navigation/NavDropdown.vue"
import DropdownMenu from "@/Components/Navigation/DropdownMenu.vue"
import NavLink from "@/Components/Navigation/NavLink.vue"
import { Settings, KeyRound, LogOut, Sun, Moon, Menu, X } from "lucide-vue-next"
import { ref } from 'vue'

// Get user role from auth props
const { props } = usePage()
const role = props.auth.user.role
const isMobileMenuOpen = ref(false)

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value
}
</script>

<template>
    <AppTheme v-slot="{ isDark, toggleDarkMode }">
        <NavigationManager v-slot="{ dropdowns, toggleDropdown, logout }">
            <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-800">
                <nav class="w-full bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm shadow-lg px-4 md:px-8 py-4 sticky top-0 z-[100]">
                    <div class="max-w-7xl mx-auto flex justify-between items-center">
                        <!-- Logo -->
                        <img
                            src="/img/peruri.png"
                            class="w-20 md:w-24 hover:opacity-80 transition-opacity"
                            alt="Logo Peruri"
                        />

                        <!-- Desktop Navigation -->
                        <div class="hidden md:block">
                            <MainNavigation
                                :dropdowns="dropdowns"
                                :role="role"
                                @toggle-dropdown="toggleDropdown"
                            />
                        </div>

                        <!-- Options and Hamburger -->
                        <div class="flex items-center gap-2">
                            <!-- Dark Mode Toggle -->
                            <button
                                @click="toggleDarkMode"
                                class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
                            >
                                <Moon
                                    v-if="isDark"
                                    class="w-5 h-5 text-amber-400"
                                />
                                <Sun
                                    v-else
                                    class="w-5 h-5 text-slate-600"
                                />
                            </button>

                            <!-- Desktop Options -->
                            <div class="hidden md:block relative">
                                <NavDropdown
                                    label="Opsi"
                                    :icon="Settings"
                                    :is-open="dropdowns.options"
                                    @toggle="toggleDropdown('options')"
                                />
                                <DropdownMenu
                                    :show="dropdowns.options"
                                    class="w-48"
                                >
                                    <NavLink
                                        :href="route('changePassword.index')"
                                        class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                    >
                                        <KeyRound
                                            class="w-4 h-4 text-slate-500 dark:text-slate-400"
                                        />
                                        <span class="text-sm text-slate-600 dark:text-slate-300">
                                            Ganti Password
                                        </span>
                                    </NavLink>

                                    <button
                                        @click="logout"
                                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                    >
                                        <LogOut
                                            class="w-4 h-4 text-slate-500 dark:text-slate-400"
                                        />
                                        <span class="text-sm text-slate-600 dark:text-slate-300">
                                            Logout
                                        </span>
                                    </button>
                                </DropdownMenu>
                            </div>

                            <!-- Mobile Menu Button -->
                            <button
                                @click="toggleMobileMenu"
                                class="md:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                aria-label="Toggle mobile menu"
                            >
                                <Menu
                                    v-if="!isMobileMenuOpen"
                                    class="w-6 h-6 text-slate-600 dark:text-slate-300"
                                />
                                <X
                                    v-else
                                    class="w-6 h-6 text-slate-600 dark:text-slate-300"
                                />
                            </button>
                        </div>
                    </div>
                </nav>

                <!-- Mobile Navigation Menu - Increased z-index -->
                <div
                    v-show="isMobileMenuOpen"
                    class="md:hidden fixed inset-x-0 top-[72px] bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm border-t border-slate-200 dark:border-slate-700 shadow-lg z-[90]"
                >
                    <div class="px-4 py-4 space-y-4">
                        <MainNavigation
                            :dropdowns="dropdowns"
                            :role="role"
                            @toggle-dropdown="toggleDropdown"
                        />

                        <!-- Mobile Options -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                            <NavLink
                                :href="route('changePassword.index')"
                                class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                            >
                                <KeyRound
                                    class="w-4 h-4 text-slate-500 dark:text-slate-400"
                                />
                                <span class="text-sm text-slate-600 dark:text-slate-300">
                                    Ganti Password
                                </span>
                            </NavLink>

                            <button
                                @click="logout"
                                class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                            >
                                <LogOut
                                    class="w-4 h-4 text-slate-500 dark:text-slate-400"
                                />
                                <span class="text-sm text-slate-600 dark:text-slate-300">
                                    Logout
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content - Lower z-index -->
                <main class="max-w-full mx-auto px-4 py-4 relative z-0">
                    <slot />
                </main>
            </div>
        </NavigationManager>
    </AppTheme>
</template>
