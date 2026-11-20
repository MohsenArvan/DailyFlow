<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';
import {
    CheckCircle2,
    ShieldCheck,
    Database,
    Layers,
    Sparkles,
    Calendar,
    Target,
    Settings,
    ArrowLeft,
    LogIn,
    UserPlus,
    Clock,
} from 'lucide-vue-next';

interface ApiHealth {
    status: string;
    app: string;
    version: string;
    timestamp: string;
}

const router = useRouter();
const authStore = useAuthStore();
const apiStatus = ref<ApiHealth | null>(null);
const loadingHealth = ref(true);

const todayDateFormatted = computed(() => {
    const isJalali = authStore.userPreferences?.date_format !== 'gregorian';
    const locale = isJalali ? 'fa-IR' : 'en-US';
    return new Intl.DateTimeFormat(locale, {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(new Date());
});

onMounted(async () => {
    try {
        const response = await api.get<ApiHealth>('/health');
        apiStatus.value = response.data;
    } catch (err) {
        console.error('API healthcheck error:', err);
    } finally {
        loadingHealth.value = false;
    }
});
</script>

<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 font-vazir">
        <!-- Authenticated Dashboard View -->
        <template v-if="authStore.isAuthenticated">
            <!-- Welcome Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                        <span>سلام، {{ authStore.user?.name }}</span>
                        <span class="inline-block animate-bounce">👋</span>
                    </h1>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1.5 flex items-center gap-2">
                        <Calendar class="w-3.5 h-3.5" />
                        <span>امروز: {{ todayDateFormatted }}</span>
                        <span class="text-zinc-300 dark:text-zinc-600">•</span>
                        <Clock class="w-3.5 h-3.5" />
                        <span>منطقه زمانی: {{ authStore.user?.timezone }}</span>
                    </p>
                </div>

                <router-link
                    to="/settings"
                    class="self-start sm:self-auto px-3.5 py-2 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark text-xs font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors flex items-center gap-2"
                >
                    <Settings class="w-4 h-4 text-zinc-500" />
                    <span>تنظیمات و ترجیحات</span>
                </router-link>
            </div>

            <!-- Daily Progress Preview Card -->
            <div class="mb-8 p-6 rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                            <Target class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">هدف روزانه شما</h2>
                            <p class="text-xs text-zinc-400">تکمیل {{ authStore.userPreferences?.daily_goal || 5 }} تسک در روز</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-brand-500/10 text-brand-600 dark:text-brand-400">
                        ۰ / {{ authStore.userPreferences?.daily_goal || 5 }} تسک
                    </span>
                </div>

                <!-- Progress Bar -->
                <div class="w-full h-2 rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden mb-3">
                    <div class="h-full bg-brand-500 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>

                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    سیستم احراز هویت و پروفایل در فاز ۳ پیاده‌سازی شد. قابلیت ایجاد و پیگیری تسک‌ها در فاز بعدی فعال می‌شود.
                </p>
            </div>
        </template>

        <!-- Guest Hero View -->
        <template v-else>
            <div class="text-center py-12 max-w-2xl mx-auto">
                <div class="w-14 h-14 rounded-2xl bg-brand-600 flex items-center justify-center text-white shadow-lg shadow-brand-600/30 mx-auto mb-5">
                    <CheckCircle2 class="w-8 h-8 stroke-[2.2]" />
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-50 mb-3">
                    DailyFlow
                </h1>
                <p class="text-base text-zinc-600 dark:text-zinc-300 leading-relaxed mb-8">
                    جریان روزانه کارهای شما، آرام، دقیق و متمرکز. با کمترین اصطکاک کارهایتان را ثبت و به سرانجام برسانید.
                </p>

                <div class="flex items-center justify-center gap-3">
                    <router-link
                        to="/register"
                        class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-medium text-xs shadow-md shadow-brand-600/25 flex items-center gap-2 transition-all"
                    >
                        <UserPlus class="w-4 h-4" />
                        <span>شروع رایگان</span>
                    </router-link>

                    <router-link
                        to="/login"
                        class="px-5 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 font-medium text-xs flex items-center gap-2 transition-all"
                    >
                        <LogIn class="w-4 h-4" />
                        <span>ورود به حساب</span>
                    </router-link>
                </div>
            </div>
        </template>

        <!-- Infrastructure & System Status Cards -->
        <div class="mt-8">
            <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-3">وضعیت زیرساخت سیستم</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="p-3.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <ShieldCheck class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] text-zinc-400">Laravel API</div>
                        <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5" v-if="apiStatus">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online
                        </div>
                        <div class="text-xs font-semibold text-zinc-400" v-else-if="loadingHealth">درحال بررسی...</div>
                        <div class="text-xs font-semibold text-rose-500" v-else>آفلاین</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center">
                        <Sparkles class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] text-zinc-400">Frontend</div>
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Vue 3 + TS</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <Database class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] text-zinc-400">Database</div>
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">PostgreSQL 16</div>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <Layers class="w-4 h-4" />
                    </div>
                    <div>
                        <div class="text-[11px] text-zinc-400">Queues & Cache</div>
                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Redis 7</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
