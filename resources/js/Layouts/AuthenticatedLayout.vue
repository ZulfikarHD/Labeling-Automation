<script setup>
import { usePage } from "@inertiajs/vue3"
import NavigationManager from "@/Layouts/Navigation/NavigationManager.vue"
import AppTheme from "@/Layouts/Navigation/AppTheme.vue"
import MainNavigation from "@/Layouts/Navigation/MainNavigation.vue"
import NavDropdown from "@/Components/Navigation/NavDropdown.vue"
import DropdownMenu from "@/Components/Navigation/DropdownMenu.vue"
import NavLink from "@/Components/Navigation/NavLink.vue"
import { Settings, KeyRound, LogOut, Sun, Moon } from "lucide-vue-next"

// Get user role from auth props
const { props } = usePage()
const role = props.auth.user.role
</script>

<template>
    <AppTheme v-slot="{ isDark, toggleDarkMode }">
        <NavigationManager v-slot="{ dropdowns, toggleDropdown, logout }">
            <div
                class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-800"
            >
                <nav
                    class="w-full bg-white/80 dark:bg-slate-800/80 backdrop-blur-sm shadow-lg px-8 py-4 sticky top-0 z-50"
                >
                    <div class="max-w-7xl mx-auto flex justify-between items-center">
                        <!-- Logo -->
                        <img
                            src="/img/peruri.png"
                            class="w-24 hover:opacity-80 transition-opacity"
                            alt="Logo Peruri"
                        />

                        <!-- Main Navigation -->
                        <MainNavigation
                            :dropdowns="dropdowns"
                            :role="role"
                            @toggle-dropdown="toggleDropdown"
                        />

                        <!-- Options -->
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

                            <!-- Options Dropdown -->
                            <div class="relative">
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
                        </div>
                    </div>
                </nav>

                <!-- Main Content -->
                <main class="max-w-full mx-auto px-4 py-4">
                    <slot />
                </main>
            </div>
        </NavigationManager>
    </AppTheme>
</template>
