<script setup lang="ts">
// Jumlah item skeleton yang akan ditampilkan
const itemCount = 5;
</script>

<template>
    <div class="animate-pulse transition-opacity duration-300 ease-in-out">
        <!-- Header Skeleton -->
        <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="h-4 w-36 bg-gray-200 dark:bg-gray-700 rounded"></div>
        </div>

        <div class="relative overflow-x-auto rounded -mx-6">
            <table class="w-full">
                <!-- Table Header Skeleton -->
                <thead>
                    <tr>
                        <th scope="col" rowspan="2" class="sticky top-0 px-6 py-3 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm border-b-2 border-blue-300 dark:border-blue-700">
                            <div class="h-3 w-6 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </th>
                        <th scope="col" rowspan="2" class="sticky top-0 px-6 py-3 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm border-b-2 border-blue-300 dark:border-blue-700">
                            <div class="h-3 w-14 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </th>
                        <th scope="col" colspan="3" class="sticky top-0 px-6 py-3 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm border-b-2 border-blue-300 dark:border-blue-700">
                            <div class="h-3 w-24 mx-auto bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </th>
                        <th scope="col" rowspan="2" class="sticky top-0 px-6 py-3 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm border-b-2 border-blue-300 dark:border-blue-700">
                            <div class="h-3 w-20 mx-auto bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </th>
                    </tr>
                    <tr>
                        <th v-for="i in 3" :key="i" scope="col" class="sticky top-0 px-6 py-3 bg-blue-100/90 dark:bg-blue-900/50 backdrop-blur-sm border-b-2 border-blue-300 dark:border-blue-700">
                            <div class="h-3 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </th>
                    </tr>
                </thead>

                <!-- Table Body Skeleton -->
                <tbody class="bg-white/50 dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="i in itemCount" :key="i"
                        class="even:bg-gray-50/50 dark:even:bg-gray-800/30">
                        <!-- No -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="h-3 w-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </td>
                        <!-- NP -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </td>
                        <!-- Lembar -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="h-3 w-10 ml-auto bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </td>
                        <!-- RIM -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="h-3 w-8 ml-auto bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </td>
                        <!-- PO -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="h-3 w-6 ml-auto bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </td>
                        <!-- Target -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col items-end gap-1.5">
                                <div class="h-3 w-20 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-3 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Skeleton -->
        <div class="flex justify-center items-center gap-2 p-3 border-t border-gray-200 dark:border-gray-700">
            <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
            <div class="h-8 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
            <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
        </div>
    </div>
</template>

<style scoped>
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}
</style>
