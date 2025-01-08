<script setup>
import { CheckCircle2, Clock, AlertCircle } from "lucide-vue-next";
</script>

<template>
    <div class="flex flex-row lg:flex-col justify-center gap-6 bg-slate-50/50 dark:bg-slate-700/50 p-6 rounded-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 flex items-center justify-center shadow-sm">
                <CheckCircle2 class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
            </div>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Selesai Verifikasi</span>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 flex items-center justify-center shadow-sm">
                <Clock class="w-6 h-6 text-amber-600 dark:text-amber-400" />
            </div>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Proses Verifikasi</span>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 flex items-center justify-center shadow-sm">
                <AlertCircle class="w-6 h-6 text-slate-400 dark:text-slate-500" />
            </div>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Belum Verifikasi</span>
        </div>
    </div>
</template>
