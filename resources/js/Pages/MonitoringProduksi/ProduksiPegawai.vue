<script setup lang="ts">
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import LoadingOverlay from '@/Components/LoadingOverlay.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, watch, onMounted, computed } from 'vue'
import { Calendar, Users, Target, TrendingUp, BarChart3, Eye, Trophy, Medal, Award } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
    teams: Object
})

const getDate = new Date()
const form = useForm({
    date: getDate.toISOString().substr(0, 10)
})
const activeTeams = ref([])
const isLoading = ref(true)
const activeTab = ref('overview')
const teamData = ref({})

const fetchActiveTeams = async () => {
    try {
        isLoading.value = true
        const response = await axios.get(`/api/active-teams?date=${form.date}`)
        activeTeams.value = response.data

        // Fetch data for all active teams
        await fetchAllTeamData()
    } catch (error) {
        console.error('Error fetching active teams:', error)
    } finally {
        isLoading.value = false
    }
}

const fetchAllTeamData = async () => {
    const promises = activeTeams.value.map(async (teamId) => {
        try {
            const [teamResponse, produksiResponse] = await Promise.all([
                axios.get(`/api/team-name/${teamId}`),
                axios.get(`/api/pendapatan-harian?date=${form.date}&team=${teamId}`)
            ])

            return {
                id: teamId,
                name: teamResponse.data.workstation,
                data: produksiResponse.data
            }
        } catch (error) {
            console.error(`Error fetching data for team ${teamId}:`, error)
            return null
        }
    })

    const results = await Promise.all(promises)
    teamData.value = results.filter(Boolean).reduce((acc, team) => {
        acc[team.id] = team
        return acc
    }, {})
}

watch(() => form.date, () => {
    fetchActiveTeams()
})

onMounted(() => {
    fetchActiveTeams()
})

const filteredTeams = computed(() => {
    return props.teams.filter(team => activeTeams.value.includes(team.id))
})

const overviewStats = computed(() => {
    const stats = {
        totalVerifikasi: 0,
        totalRim: 0,
        totalPO: 0,
        totalEmployees: 0,
        activeTeams: activeTeams.value.length
    }

    Object.values(teamData.value).forEach(team => {
        if (team.data) {
            stats.totalVerifikasi += team.data.reduce((sum, item) => sum + Number(item.verifikasi), 0)
            stats.totalPO += team.data.reduce((sum, item) => sum + Number(item.jumlah_po), 0)
            stats.totalEmployees += team.data.length
        }
    })

    stats.totalRim = Math.ceil(stats.totalVerifikasi / 500)
    return stats
})

const getTeamStats = (teamId) => {
    const team = teamData.value[teamId]
    if (!team || !team.data) return { verifikasi: 0, rim: 0, po: 0, employees: 0 }

    const verifikasi = team.data.reduce((sum, item) => sum + Number(item.verifikasi), 0)
    return {
        verifikasi,
        rim: Math.ceil(verifikasi / 500),
        po: team.data.reduce((sum, item) => sum + Number(item.jumlah_po), 0),
        employees: team.data.length
    }
}

const getPerformanceColor = (actual, target = 17500) => {
    const percentage = (actual / target) * 100
    if (percentage >= 100) return 'text-green-600 dark:text-green-400'
    if (percentage >= 80) return 'text-yellow-600 dark:text-yellow-400'
    return 'text-red-600 dark:text-red-400'
}

const getProgressPercentage = (actual, target = 17500) => {
    return Math.min((actual / target) * 100, 100)
}

const getTopPerformers = computed(() => {
    const allEmployees = []

    Object.values(teamData.value).forEach(team => {
        if (team.data) {
            team.data.forEach(employee => {
                allEmployees.push({
                    ...employee,
                    teamName: team.name,
                    teamId: team.id,
                    percentage: getProgressPercentage(employee.verifikasi)
                })
            })
        }
    })

    return allEmployees.sort((a, b) => Number(b.verifikasi) - Number(a.verifikasi)).slice(0, 10)
})

const getTeamRanking = computed(() => {
    return filteredTeams.value.map(team => {
        const stats = getTeamStats(team.id)
        return {
            ...team,
            ...stats,
            percentage: getProgressPercentage(stats.verifikasi)
        }
    }).sort((a, b) => b.verifikasi - a.verifikasi)
})

const getRankBadgeColor = (rank) => {
    if (rank === 1) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'
    if (rank === 2) return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    if (rank === 3) return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'
    return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
}

const getRankIcon = (rank) => {
    if (rank === 1) return '🥇'
    if (rank === 2) return '🥈'
    if (rank === 3) return '🥉'
    return `#${rank}`
}

const getTeamEmployeesWithRanking = (teamId) => {
    const team = teamData.value[teamId]
    if (!team || !team.data) return []

    return team.data
        .map(employee => ({
            ...employee,
            verifikasiNumber: Number(employee.verifikasi)
        }))
        .sort((a, b) => b.verifikasiNumber - a.verifikasiNumber)
        .map((employee, index) => ({
            ...employee,
            rank: index + 1,
            totalEmployees: team.data.length
        }))
}
</script>

<template>
    <Head title="Monitoring Produksi Harian" />
    <AuthenticatedLayout>
        <div class="relative">
            <div class="mx-auto max-w-7xl">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-4xl">
                        Monitoring Produksi Harian
                    </h1>
                    <p class="mt-2 text-slate-600 dark:text-slate-400">
                        Dashboard monitoring aktivitas dan performa tim produksi
                    </p>
                </div>

                <!-- Date Filter -->
                <div class="mx-auto mb-8 max-w-md">
                    <div class="relative">
                        <InputLabel
                            for="dateFilter"
                            class="block mb-2 text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Tanggal Produksi
                        </InputLabel>
                        <div class="relative">
                            <Calendar class="absolute left-3 top-1/2 w-5 h-5 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                            <TextInput
                                id="dateFilter"
                                type="date"
                                v-model="form.date"
                                class="pl-12 w-full rounded-xl shadow-sm border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div class="relative">
                    <LoadingOverlay :is-loading="isLoading" />

                    <!-- Empty State -->
                    <div v-if="!isLoading && activeTeams.length === 0" class="py-16 text-center">
                        <div class="flex justify-center items-center mx-auto mb-4 w-24 h-24 bg-gray-100 rounded-full dark:bg-gray-800">
                            <BarChart3 class="w-12 h-12 text-gray-400" />
                        </div>
                        <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">
                            Tidak ada data produksi
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Tidak ada aktivitas produksi untuk tanggal ini
                        </p>
                    </div>

                    <!-- Main Content -->
                    <div v-else-if="!isLoading" class="space-y-8">
                        <!-- Tab Navigation -->
                        <div class="border-b border-slate-200 dark:border-slate-700">
                            <nav class="flex overflow-x-auto flex-wrap gap-y-2 gap-x-4 -mb-px sm:flex-nowrap sm:gap-x-8 sm:gap-y-0">
                                <!-- Overview Tab -->
                                <button
                                    @click="activeTab = 'overview'"
                                    :class="[
                                        activeTab === 'overview'
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300',
                                        'whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm flex items-center gap-2 min-w-0 sm:py-4 sm:px-1'
                                    ]"
                                >
                                    <BarChart3 class="flex-shrink-0 w-4 h-4" />
                                    <span class="hidden sm:inline">Ringkasan</span>
                                    <span class="sm:hidden">Ring.</span>
                                </button>

                                <!-- Ranking Tab -->
                                <button
                                    @click="activeTab = 'ranking'"
                                    :class="[
                                        activeTab === 'ranking'
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300',
                                        'whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm flex items-center gap-2 min-w-0 sm:py-4 sm:px-1'
                                    ]"
                                >
                                    <Trophy class="flex-shrink-0 w-4 h-4" />
                                    <span class="hidden sm:inline">Ranking</span>
                                    <span class="sm:hidden">Rank</span>
                                </button>

                                <!-- Team Tabs -->
                                <button
                                    v-for="team in filteredTeams"
                                    :key="team.id"
                                    @click="activeTab = `team-${team.id}`"
                                    :class="[
                                        activeTab === `team-${team.id}`
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-300',
                                        'whitespace-nowrap py-3 px-2 border-b-2 font-medium text-sm flex items-center gap-2 min-w-0 sm:py-4 sm:px-1'
                                    ]"
                                >
                                    <Users class="flex-shrink-0 w-4 h-4" />
                                    <span class="truncate max-w-24 sm:max-w-none">{{ team.workstation }}</span>
                                </button>
                            </nav>
                        </div>

                        <!-- Overview Tab Content -->
                        <div v-if="activeTab === 'overview'" class="space-y-6">
                            <!-- Summary Cards -->
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                                <!-- Total Verifikasi -->
                                <div class="p-6 bg-white rounded-xl border shadow-sm border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="flex justify-center items-center w-12 h-12 bg-indigo-100 rounded-lg dark:bg-indigo-900/30">
                                                <Target class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Verifikasi</p>
                                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                                {{ overviewStats.totalVerifikasi.toLocaleString() }}
                                            </p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Lembar</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total RIM -->
                                <div class="p-6 bg-white rounded-xl border shadow-sm border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="flex justify-center items-center w-12 h-12 bg-green-100 rounded-lg dark:bg-green-900/30">
                                                <TrendingUp class="w-6 h-6 text-green-600 dark:text-green-400" />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total RIM</p>
                                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                                {{ overviewStats.totalRim.toLocaleString() }}
                                            </p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">RIM</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Active Teams -->
                                <div class="p-6 bg-white rounded-xl border shadow-sm border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="flex justify-center items-center w-12 h-12 bg-purple-100 rounded-lg dark:bg-purple-900/30">
                                                <Users class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Tim Aktif</p>
                                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                                {{ overviewStats.activeTeams }}
                                            </p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Tim</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Employees -->
                                <div class="p-6 bg-white rounded-xl border shadow-sm border-slate-200 dark:bg-slate-800 dark:border-slate-700">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="flex justify-center items-center w-12 h-12 bg-orange-100 rounded-lg dark:bg-orange-900/30">
                                                <Users class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Pegawai</p>
                                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                                                {{ overviewStats.totalEmployees }}
                                            </p>
                                            <p class="text-sm text-slate-500 dark:text-slate-400">Orang</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Team Performance Cards -->
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <div
                                    v-for="team in filteredTeams"
                                    :key="team.id"
                                    class="p-6 bg-white rounded-xl border shadow-sm border-slate-200 dark:bg-slate-800 dark:border-slate-700"
                                >
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                            {{ team.workstation }}
                                        </h3>
                                        <button
                                            @click="activeTab = `team-${team.id}`"
                                            class="inline-flex gap-1 items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                        >
                                            <Eye class="w-4 h-4" />
                                            Detail
                                        </button>
                                    </div>

                                    <div class="space-y-4">
                                        <!-- Progress Bar -->
                                        <div>
                                            <div class="flex justify-between mb-1 text-sm">
                                                <span class="text-slate-600 dark:text-slate-400">Progress Harian</span>
                                                <span :class="getPerformanceColor(getTeamStats(team.id).verifikasi)">
                                                    {{ getProgressPercentage(getTeamStats(team.id).verifikasi).toFixed(1) }}%
                                                </span>
                                            </div>
                                            <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-700">
                                                <div
                                                    class="h-2 bg-gradient-to-r from-indigo-500 to-blue-500 rounded-full transition-all duration-300"
                                                    :style="`width: ${getProgressPercentage(getTeamStats(team.id).verifikasi)}%`"
                                                ></div>
                                            </div>
                                        </div>

                                        <!-- Stats Grid -->
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="text-center">
                                                <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                                    {{ getTeamStats(team.id).verifikasi.toLocaleString() }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Lembar</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                                    {{ getTeamStats(team.id).rim }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">RIM</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                                    {{ getTeamStats(team.id).employees }}
                                                </p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Pegawai</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ranking Tab Content -->
                        <div v-if="activeTab === 'ranking'" class="space-y-6">
                            <!-- Page Header -->
                            <div class="text-center">
                                <h2 class="mb-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    🏆 Leaderboard Produksi Harian
                                </h2>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Ranking performa terbaik berdasarkan jumlah verifikasi
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                                <!-- Top Performers (Individual) -->
                                <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                                    <div class="flex gap-3 items-center mb-6">
                                        <div class="flex justify-center items-center w-10 h-10 bg-yellow-100 rounded-lg dark:bg-yellow-900/30">
                                            <Medal class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                Top Performers
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Pegawai dengan performa terbaik
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <div
                                            v-for="(employee, index) in getTopPerformers"
                                            :key="index"
                                            class="flex justify-between items-center p-4 rounded-lg border border-gray-100 transition-colors dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        >
                                            <div class="flex gap-4 items-center">
                                                <!-- Rank Badge -->
                                                <div :class="[
                                                    'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                                                    getRankBadgeColor(index + 1)
                                                ]">
                                                    {{ getRankIcon(index + 1) }}
                                                </div>

                                                <!-- Employee Info -->
                                                <div>
                                                    <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ employee.pegawai }}
                                                    </p>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ employee.teamName }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Performance Stats -->
                                            <div class="text-right">
                                                <p class="font-bold text-blue-600 dark:text-blue-400">
                                                    {{ Number(employee.verifikasi).toLocaleString() }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ employee.percentage.toFixed(1) }}% dari target
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Empty State for Top Performers -->
                                        <div v-if="getTopPerformers.length === 0" class="py-8 text-center">
                                            <Trophy class="mx-auto mb-3 w-12 h-12 text-gray-400" />
                                            <p class="text-gray-500 dark:text-gray-400">
                                                Belum ada data performa
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Team Rankings -->
                                <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                                    <div class="flex gap-3 items-center mb-6">
                                        <div class="flex justify-center items-center w-10 h-10 bg-purple-100 rounded-lg dark:bg-purple-900/30">
                                            <Award class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                Ranking Tim
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Performa tim berdasarkan total verifikasi
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div
                                            v-for="(team, index) in getTeamRanking"
                                            :key="team.id"
                                            class="p-4 rounded-lg border border-gray-100 transition-colors dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        >
                                            <div class="flex justify-between items-center mb-3">
                                                <div class="flex gap-3 items-center">
                                                    <!-- Rank Badge -->
                                                    <div :class="[
                                                        'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                                                        getRankBadgeColor(index + 1)
                                                    ]">
                                                        {{ getRankIcon(index + 1) }}
                                                    </div>

                                                    <!-- Team Info -->
                                                    <div>
                                                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                                                            {{ team.workstation }}
                                                        </p>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                                            {{ team.employees }} pegawai aktif
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Team Stats -->
                                                <div class="text-right">
                                                    <p class="font-bold text-blue-600 dark:text-blue-400">
                                                        {{ team.verifikasi.toLocaleString() }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ team.rim }} RIM
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Team Progress Bar -->
                                            <div>
                                                <div class="flex justify-between mb-1 text-xs">
                                                    <span class="text-gray-600 dark:text-gray-400">Progress Tim</span>
                                                    <span :class="getPerformanceColor(team.verifikasi)">
                                                        {{ team.percentage.toFixed(1) }}%
                                                    </span>
                                                </div>
                                                <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                                                    <div
                                                        class="h-2 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-300"
                                                        :style="`width: ${team.percentage}%`"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Empty State for Team Rankings -->
                                        <div v-if="getTeamRanking.length === 0" class="py-8 text-center">
                                            <Award class="mx-auto mb-3 w-12 h-12 text-gray-400" />
                                            <p class="text-gray-500 dark:text-gray-400">
                                                Belum ada data tim
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Achievement Badges Section -->
                            <div class="p-6 bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl border border-blue-200 dark:from-blue-900/20 dark:to-purple-900/20 dark:border-blue-800">
                                <div class="mb-6 text-center">
                                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        🎯 Target Achievement
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Status pencapaian target harian (17.500 lembar per pegawai)
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                                    <!-- Target Achieved -->
                                    <div class="p-4 text-center bg-green-100 rounded-lg dark:bg-green-900/30">
                                        <div class="mb-2 text-2xl">🎉</div>
                                        <p class="text-sm font-medium text-green-800 dark:text-green-300">Target Tercapai</p>
                                        <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                            {{ getTopPerformers.filter(emp => emp.percentage >= 100).length }}
                                        </p>
                                        <p class="text-xs text-green-600 dark:text-green-400">pegawai</p>
                                    </div>

                                    <!-- Close to Target -->
                                    <div class="p-4 text-center bg-yellow-100 rounded-lg dark:bg-yellow-900/30">
                                        <div class="mb-2 text-2xl">⚡</div>
                                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Mendekati Target</p>
                                        <p class="text-lg font-bold text-yellow-600 dark:text-yellow-400">
                                            {{ getTopPerformers.filter(emp => emp.percentage >= 80 && emp.percentage < 100).length }}
                                        </p>
                                        <p class="text-xs text-yellow-600 dark:text-yellow-400">pegawai</p>
                                    </div>

                                    <!-- Below Target -->
                                    <div class="p-4 text-center bg-red-100 rounded-lg dark:bg-red-900/30">
                                        <div class="mb-2 text-2xl">📈</div>
                                        <p class="text-sm font-medium text-red-800 dark:text-red-300">Perlu Peningkatan</p>
                                        <p class="text-lg font-bold text-red-600 dark:text-red-400">
                                            {{ getTopPerformers.filter(emp => emp.percentage < 80).length }}
                                        </p>
                                        <p class="text-xs text-red-600 dark:text-red-400">pegawai</p>
                                    </div>

                                    <!-- Best Team -->
                                    <div class="p-4 text-center bg-purple-100 rounded-lg dark:bg-purple-900/30">
                                        <div class="mb-2 text-2xl">👑</div>
                                        <p class="text-sm font-medium text-purple-800 dark:text-purple-300">Tim Terbaik</p>
                                        <p class="text-sm font-bold text-purple-600 dark:text-purple-400">
                                            {{ getTeamRanking[0]?.workstation || 'N/A' }}
                                        </p>
                                        <p class="text-xs text-purple-600 dark:text-purple-400">
                                            {{ getTeamRanking[0]?.verifikasi.toLocaleString() || '0' }} lembar
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Individual Team Tab Content -->
                        <div
                            v-for="team in filteredTeams"
                            :key="`content-${team.id}`"
                            v-show="activeTab === `team-${team.id}`"
                            class="space-y-6"
                        >
                            <!-- Team Header -->
                            <div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                            {{ team.workstation }}
                                        </h2>
                                        <p class="text-gray-600 dark:text-gray-400">
                                            Data verifikasi untuk tanggal {{ new Date(form.date).toLocaleDateString('id-ID') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Verifikasi</p>
                                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                                            {{ getTeamStats(team.id).verifikasi.toLocaleString() }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Employee Cards -->
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                <div
                                    v-for="(employee, index) in getTeamEmployeesWithRanking(team.id)"
                                    :key="index"
                                    class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm dark:bg-gray-800 dark:border-gray-700"
                                >
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                            {{ employee.pegawai }}
                                        </h3>
                                        <div class="text-right">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Urutan</div>
                                            <div class="font-bold text-indigo-600 dark:text-indigo-400">
                                                {{ employee.rank }}/{{ employee.totalEmployees }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mb-4">
                                        <div class="flex justify-between mb-1 text-xs">
                                            <span class="text-gray-600 dark:text-gray-400">Progress Target</span>
                                            <span :class="getPerformanceColor(employee.verifikasi)" class="font-medium">
                                                {{ getProgressPercentage(employee.verifikasi).toFixed(1) }}%
                                            </span>
                                        </div>
                                        <div class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700">
                                            <div
                                                class="h-2 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full transition-all duration-300"
                                                :style="`width: ${getProgressPercentage(employee.verifikasi)}%`"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Employee Stats -->
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Verifikasi</span>
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">
                                                {{ Number(employee.verifikasi).toLocaleString() }} Lbr
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">RIM</span>
                                            <span class="font-semibold text-green-600 dark:text-green-400">
                                                {{ Math.ceil(Number(employee.verifikasi) / 500) }} RIM
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">PO</span>
                                            <span class="font-semibold text-purple-600 dark:text-purple-400">
                                                {{ employee.jumlah_po }} PO
                                            </span>
                                        </div>
                                        <div class="flex justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Target</span>
                                            <span class="font-semibold text-orange-600 dark:text-orange-400">
                                                17.500 Lbr
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Background Effects -->
            <div class="overflow-hidden absolute inset-0 -z-10">
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:24px_24px]"></div>
                <div class="absolute left-0 right-0 top-0 -z-10 m-auto h-[310px] w-[310px] rounded-full bg-cyan-400 dark:bg-cyan-600 opacity-20 blur-[100px]"></div>
                <div class="absolute right-0 top-0 -z-10 h-[310px] w-[310px] rounded-full bg-blue-400 dark:bg-blue-600 opacity-20 blur-[100px]"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
