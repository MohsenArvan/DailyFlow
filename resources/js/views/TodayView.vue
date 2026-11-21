<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useTaskStore } from '@/stores/tasks';
import AppLayout from '@/components/layout/AppLayout.vue';
import BaseProgressBar from '@/components/ui/BaseProgressBar.vue';
import TaskQuickAdd from '@/components/task/TaskQuickAdd.vue';
import TaskList from '@/components/task/TaskList.vue';
import { Calendar, CheckCircle2, Sparkles, Loader2 } from 'lucide-vue-next';

const authStore = useAuthStore();
const taskStore = useTaskStore();

const formatLocalDate = (d: Date = new Date()): string => {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const todayDateString = formatLocalDate();

const todayFormatted = computed(() => {
    const isJalali = authStore.userPreferences?.date_format !== 'gregorian';
    const locale = isJalali ? 'fa-IR' : 'en-US';
    return new Intl.DateTimeFormat(locale, {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(new Date());
});

const dailyGoal = computed(() => authStore.userPreferences?.daily_goal || 5);
const completedToday = computed(() => taskStore.counts.today_completed ?? 0);
const remainingToday = computed(() => taskStore.counts.today);

onMounted(async () => {
    await taskStore.fetchTasks('today');
});
</script>

<template>
    <AppLayout>
        <!-- Top Date & Goal Summary -->
        <div class="mb-6 p-6 rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark shadow-card">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                        <Calendar class="w-5 h-5 text-brand-600 dark:text-brand-400" />
                        <span>کارهای امروز</span>
                        <span class="text-xs font-normal text-zinc-400">({{ todayFormatted }})</span>
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                        {{ remainingToday > 0 ? `${remainingToday} تسک برای انجام در انتظار شماست.` : 'تمام تسک‌های امروز تکمیل شده یا هنوز تسکی ثبت نکرده‌اید.' }}
                    </p>
                </div>

                <div v-if="completedToday >= dailyGoal" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                    <Sparkles class="w-4 h-4" />
                    <span>هدف روزانه محقق شد!</span>
                </div>
            </div>

            <!-- Daily Goal Progress -->
            <div class="pt-3 border-t border-surface-borderLight dark:border-surface-borderDark">
                <div class="flex justify-between items-center text-xs mb-1.5">
                    <span class="font-medium text-zinc-600 dark:text-zinc-400">هدف تکمیل روزانه</span>
                    <span class="font-bold text-brand-600 dark:text-brand-400">
                        {{ completedToday }} از {{ dailyGoal }} تسک
                    </span>
                </div>
                <BaseProgressBar :value="completedToday" :max="dailyGoal" color="brand" />
            </div>
        </div>

        <!-- Quick Add Form -->
        <div class="mb-6">
            <TaskQuickAdd :default-due-date="todayDateString" />
        </div>

        <!-- Tasks Loading Spinner or Task List -->
        <div v-if="taskStore.isLoading" class="flex items-center justify-center p-12 text-zinc-400">
            <Loader2 class="w-7 h-7 animate-spin text-brand-500" />
        </div>

        <TaskList
            v-else
            :tasks="taskStore.tasks"
            empty-title="همه کارهای امروز انجام شده‌اند یا تسکی ثبت نشده"
            empty-description="برای برنامه‌ریزی روز پرانرژی، اولین کار امروزتان را با فرم بالا یا کلید Q اضافه کنید."
        />
    </AppLayout>
</template>
