<script setup lang="ts">
import { ref, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import type { UserPreferences } from '@/types/auth';
import {
    Palette,
    Calendar,
    Target,
    Bell,
    CheckCircle,
    AlertCircle,
    Loader2,
    Sun,
    Moon,
    Laptop,
} from 'lucide-vue-next';

const authStore = useAuthStore();

const theme = ref<'dark' | 'light' | 'system'>(authStore.userPreferences?.theme || 'dark');
const dateFormat = ref<'jalali' | 'gregorian'>(authStore.userPreferences?.date_format || 'jalali');
const startOfWeek = ref<'saturday' | 'monday'>(authStore.userPreferences?.start_of_week || 'saturday');
const dailyGoal = ref<number>(authStore.userPreferences?.daily_goal || 5);
const emailNotifications = ref<boolean>(authStore.userPreferences?.email_notifications ?? true);

const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

watch(
    () => authStore.userPreferences,
    (prefs) => {
        if (prefs) {
            theme.value = prefs.theme || 'dark';
            dateFormat.value = prefs.date_format || 'jalali';
            startOfWeek.value = prefs.start_of_week || 'saturday';
            dailyGoal.value = prefs.daily_goal ?? 5;
            emailNotifications.value = prefs.email_notifications ?? true;
        }
    },
    { immediate: true }
);

const handleSavePreferences = async () => {
    successMessage.value = '';
    errorMessage.value = '';

    const payload: UserPreferences = {
        theme: theme.value,
        date_format: dateFormat.value,
        start_of_week: startOfWeek.value,
        daily_goal: Number(dailyGoal.value),
        email_notifications: emailNotifications.value,
    };

    isLoading.value = true;
    try {
        await authStore.updatePreferences(payload);
        successMessage.value = 'تنظیمات ظاهری و ترجیحات ذخیره شدند.';
    } catch {
        errorMessage.value = 'خطایی در ذخیره تنظیمات رخ داد.';
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="space-y-6 font-vazir">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">تنظیمات ظاهری و ترجیحات</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">شخصی‌سازی ظاهر برنامه، تقویم و اهداف روزانه شما</p>
        </div>

        <!-- Success Alert -->
        <div
            v-if="successMessage"
            class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 flex items-center gap-2.5 text-xs text-emerald-700 dark:text-emerald-300"
            role="status"
        >
            <CheckCircle class="w-4 h-4 shrink-0 text-emerald-500" />
            <span>{{ successMessage }}</span>
        </div>

        <!-- Error Alert -->
        <div
            v-if="errorMessage"
            class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 flex items-center gap-2.5 text-xs text-rose-700 dark:text-rose-300"
            role="alert"
        >
            <AlertCircle class="w-4 h-4 shrink-0 text-rose-500" />
            <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleSavePreferences" class="space-y-6 max-w-lg">
            <!-- Theme Selection -->
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-2">
                    پوسته و تم برنامه
                </label>
                <div class="grid grid-cols-3 gap-2.5">
                    <button
                        type="button"
                        @click="theme = 'dark'"
                        class="p-3 rounded-xl border text-xs font-medium flex flex-col items-center gap-2 transition-all"
                        :class="theme === 'dark' ? 'border-brand-500 bg-brand-500/10 text-brand-600 dark:text-brand-400 ring-2 ring-brand-500/30' : 'border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-zinc-600 dark:text-zinc-400 hover:border-zinc-400'"
                    >
                        <Moon class="w-5 h-5" />
                        <span>تاریک (پیش‌فرض)</span>
                    </button>

                    <button
                        type="button"
                        @click="theme = 'light'"
                        class="p-3 rounded-xl border text-xs font-medium flex flex-col items-center gap-2 transition-all"
                        :class="theme === 'light' ? 'border-brand-500 bg-brand-500/10 text-brand-600 dark:text-brand-400 ring-2 ring-brand-500/30' : 'border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-zinc-600 dark:text-zinc-400 hover:border-zinc-400'"
                    >
                        <Sun class="w-5 h-5" />
                        <span>روشن</span>
                    </button>

                    <button
                        type="button"
                        @click="theme = 'system'"
                        class="p-3 rounded-xl border text-xs font-medium flex flex-col items-center gap-2 transition-all"
                        :class="theme === 'system' ? 'border-brand-500 bg-brand-500/10 text-brand-600 dark:text-brand-400 ring-2 ring-brand-500/30' : 'border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-zinc-600 dark:text-zinc-400 hover:border-zinc-400'"
                    >
                        <Laptop class="w-5 h-5" />
                        <span>سیستم</span>
                    </button>
                </div>
            </div>

            <!-- Date Format -->
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-2">
                    نوع تقویم و نمایش تاریخ
                </label>
                <div class="grid grid-cols-2 gap-2.5">
                    <button
                        type="button"
                        @click="dateFormat = 'jalali'"
                        class="p-3 rounded-xl border text-xs font-medium flex items-center justify-center gap-2 transition-all"
                        :class="dateFormat === 'jalali' ? 'border-brand-500 bg-brand-500/10 text-brand-600 dark:text-brand-400 ring-2 ring-brand-500/30' : 'border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-zinc-600 dark:text-zinc-400'"
                    >
                        <Calendar class="w-4 h-4" />
                        <span>تقویم شمسی (جلالی)</span>
                    </button>

                    <button
                        type="button"
                        @click="dateFormat = 'gregorian'"
                        class="p-3 rounded-xl border text-xs font-medium flex items-center justify-center gap-2 transition-all"
                        :class="dateFormat === 'gregorian' ? 'border-brand-500 bg-brand-500/10 text-brand-600 dark:text-brand-400 ring-2 ring-brand-500/30' : 'border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-zinc-600 dark:text-zinc-400'"
                    >
                        <Calendar class="w-4 h-4" />
                        <span>تقویم میلادی (Gregorian)</span>
                    </button>
                </div>
            </div>

            <!-- Start of Week -->
            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-2">
                    روز آغاز هفته
                </label>
                <div class="grid grid-cols-2 gap-2.5">
                    <button
                        type="button"
                        @click="startOfWeek = 'saturday'"
                        class="p-2.5 rounded-xl border text-xs font-medium transition-all"
                        :class="startOfWeek === 'saturday' ? 'border-brand-500 bg-brand-500/10 text-brand-600 dark:text-brand-400 ring-2 ring-brand-500/30' : 'border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-zinc-600 dark:text-zinc-400'"
                    >
                        شنبه (تقویم رسمی ایران)
                    </button>

                    <button
                        type="button"
                        @click="startOfWeek = 'monday'"
                        class="p-2.5 rounded-xl border text-xs font-medium transition-all"
                        :class="startOfWeek === 'monday' ? 'border-brand-500 bg-brand-500/10 text-brand-600 dark:text-brand-400 ring-2 ring-brand-500/30' : 'border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-zinc-600 dark:text-zinc-400'"
                    >
                        دوشنبه (استاندارد بین‌المللی)
                    </button>
                </div>
            </div>

            <!-- Daily Goal -->
            <div>
                <label for="daily-goal" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    هدف روزانه تعداد تسک‌ها
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                        <Target class="w-4 h-4" />
                    </div>
                    <input
                        id="daily-goal"
                        v-model.number="dailyGoal"
                        type="number"
                        min="1"
                        max="50"
                        class="w-full pr-10 pl-3.5 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-colors"
                    />
                </div>
                <p class="mt-1 text-[11px] text-zinc-400">
                    نوار پیشرفت کارهای روزانه بر اساس این هدف اندازه‌گیری خواهد شد.
                </p>
            </div>

            <!-- Submit -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="isLoading"
                    class="py-2.5 px-5 rounded-xl bg-brand-600 hover:bg-brand-500 disabled:opacity-60 text-white font-medium text-xs shadow-md shadow-brand-600/20 flex items-center justify-center gap-2 transition-all focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                    <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
                    <span v-else>ذخیره تنظیمات ظاهری</span>
                </button>
            </div>
        </form>
    </div>
</template>
