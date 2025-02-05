<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

// Define dropdown state interface
const dropdownState = {
    orderBesar: false,
    orderKecil: false,
    options: false,
}

const dropdowns = ref({ ...dropdownState })

// Track if component is mounted
const isMounted = ref(false)

const resetDropdowns = () => {
    Object.keys(dropdowns.value).forEach((key) => {
        dropdowns.value[key] = false
    })
}

const closeDropdowns = (e) => {
    try {
        if (isMounted.value && !e.target.closest('.dropdown-trigger')) {
            resetDropdowns()
        }
    } catch (error) {
        console.error('Error closing dropdowns:', error)
        resetDropdowns()
    }
}

const toggleDropdown = (dropdown) => {
    try {
        if (!Object.keys(dropdownState).includes(dropdown)) {
            console.warn(`Invalid dropdown key: ${dropdown}`)
            return
        }

        // Close other dropdowns
        Object.keys(dropdowns.value).forEach((key) => {
            if (key !== dropdown) dropdowns.value[key] = false
        })

        // Toggle target dropdown
        dropdowns.value[dropdown] = !dropdowns.value[dropdown]
    } catch (error) {
        console.error('Error toggling dropdown:', error)
        resetDropdowns()
    }
}

const logout = async () => {
    try {
        await router.post(route('logout'))
    } catch (error) {
        console.error('Error during logout:', error)
        // You might want to show a notification to the user here
    }
}

// Handle escape key to close dropdowns
const handleEscKey = (e) => {
    if (e.key === 'Escape') {
        resetDropdowns()
    }
}

onMounted(() => {
    isMounted.value = true

    if (typeof window !== 'undefined') {
        document.addEventListener('click', closeDropdowns)
        document.addEventListener('keydown', handleEscKey)
    }
})

onBeforeUnmount(() => {
    isMounted.value = false

    if (typeof window !== 'undefined') {
        document.removeEventListener('click', closeDropdowns)
        document.removeEventListener('keydown', handleEscKey)
    }
})

// Expose necessary methods and state
defineExpose({
    dropdowns,
    toggleDropdown,
    logout
})
</script>

<template>
    <slot
        :dropdowns="dropdowns"
        :toggle-dropdown="toggleDropdown"
        :logout="logout"
    />
</template>
