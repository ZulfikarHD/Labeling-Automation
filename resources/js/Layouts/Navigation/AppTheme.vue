<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const isDark = ref(false)
const mediaQuery = ref(null)

const initializeDarkMode = () => {
    try {
        isDark.value = localStorage.getItem('darkMode') === 'true' ||
            (window.matchMedia &&
             window.matchMedia('(prefers-color-scheme: dark)').matches)

        applyDarkMode()
    } catch (error) {
        console.error('Error initializing dark mode:', error)
        isDark.value = false
    }
}

const applyDarkMode = () => {
    try {
        document.documentElement.classList.toggle('dark', isDark.value)
    } catch (error) {
        console.error('Error applying dark mode:', error)
    }
}

const toggleDarkMode = () => {
    try {
        isDark.value = !isDark.value
        localStorage.setItem('darkMode', isDark.value)
        applyDarkMode()
    } catch (error) {
        console.error('Error toggling dark mode:', error)
    }
}

const handleSystemThemeChange = (e) => {
    try {
        if (!localStorage.getItem('darkMode')) {
            isDark.value = e.matches
            applyDarkMode()
        }
    } catch (error) {
        console.error('Error handling system theme change:', error)
    }
}

onMounted(() => {
    initializeDarkMode()

    // Initialize media query
    if (typeof window !== 'undefined') {
        mediaQuery.value = window.matchMedia('(prefers-color-scheme: dark)')
        mediaQuery.value.addEventListener('change', handleSystemThemeChange)
    }
})

onBeforeUnmount(() => {
    // Cleanup
    if (mediaQuery.value) {
        mediaQuery.value.removeEventListener('change', handleSystemThemeChange)
    }
})

defineExpose({
    isDark,
    toggleDarkMode
})
</script>

<template>
    <slot
        :is-dark="isDark"
        :toggle-dark-mode="toggleDarkMode"
    />
</template>
