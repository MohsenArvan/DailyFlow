<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { CheckCircle2, ShieldCheck, Database, Layers, Sparkles, Moon, Sun } from 'lucide-vue-next';

interface ApiHealth {
    status: string;
    app: string;
    version: string;
    timestamp: string;
}

const apiStatus = ref<ApiHealth | null>(null);
const loading = ref(true);
const isDark = ref(true);

const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

onMounted(async () => {
    try {
        const response = await axios.get<ApiHealth>('/api/v1/health');
        apiStatus.value = response.data;
    } catch (err) {
        console.error('API healthcheck error:', err);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-surface-light dark:bg-surface-dark transition-colors duration-200">
        <!-- Top Controls -->
        <header class="absolute top-6 right-6 flex items-center gap-3">
            <button
                @click="toggleTheme"
                class="p-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark text-zinc-600 dark:text-zinc-300 hover:text-brand-500 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                :title="isDark ? 'حالت روشن' : 'حالت تاریک'"
                aria-label="Toggle Theme"
            >
                <Sun v-if="isDark" class="w-4 h-4" />
                <Moon v-else class="w-4 h-4" />
            </button>
        </header>

        <!-- Main Card -->
        <main class="w-full max-w-xl p-8 rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark shadow-xl shadow-zinc-950/5">
            <!-- Header -->
            <div class="flex items-center gap-3.5 mb-6">
                <div class="w-11 h-11 rounded-xl bg-brand-600 flex items-center justify-center text-white shadow-md shadow-brand-600/30">
                    <CheckCircle2 class="w-6 h-6 stroke-[2.2]" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 font-vazir">DailyFlow</h1>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Personal Task Manager • Dockerized Infrastructure</p>
                </div>
            </div>

            <p class="text-sm leading-relaxed text-zinc-600 dark:text-zinc-300 mb-6 font-vazir">
                محیط زیرساخت Docker، لاراول ۱۱، Vue 3، TypeScript، PostgreSQL و Redis با موفقیت راه‌اندازی و متصل شدند.
            </p>

            <!-- Status Grid -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="p-3.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <ShieldCheck class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] text-zinc-400 dark:text-zinc-500">Backend API</div>
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200" v-if="loading">در حال بررسی...</div>
                        <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5" v-else-if="apiStatus">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online (v{{ apiStatus.version }})
                        </div>
                        <div class="text-xs font-semibold text-rose-500" v-else>ارتباط ناموفق</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                        <Sparkles class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] text-zinc-400 dark:text-zinc-500">Frontend Stack</div>
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Vue 3.5 + TS + Vite</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <Database class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] text-zinc-400 dark:text-zinc-500">Database & Cache</div>
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">PostgreSQL 16 + Redis 7</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <Layers class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] text-zinc-400 dark:text-zinc-500">Background Jobs</div>
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Queue Worker + Scheduler</div>
                    </div>
                </div>
            </div>

            <!-- Footer Badge -->
            <footer class="pt-4 border-t border-surface-borderLight dark:border-surface-borderDark flex items-center justify-between text-[11px] text-zinc-400 dark:text-zinc-500">
                <span>فاز ۲ تکمیل: زیرساخت آماده است</span>
                <span class="font-mono">Ready for Phase 3 (Auth)</span>
            </footer>
        </main>
    </div>
</template>
