<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useAgendaDates } from '@/composables/useAgendaDates';
import { useTaskStore } from '@/stores/tasks';
import AppLayout from '@/components/layout/AppLayout.vue';
import BaseProgressBar from '@/components/ui/BaseProgressBar.vue';
import TaskQuickAdd from '@/components/task/TaskQuickAdd.vue';
import TaskList from '@/components/task/TaskList.vue';
import { Calendar, Sparkles, Loader2 } from 'lucide-vue-next';

const authStore = useAuthStore();
const taskStore = useTaskStore();

const { localDate, formatDay } = useAgendaDates();
const todayDateString = computed(() => localDate());
const todayFormatted = computed(() => formatDay(todayDateString.value));
const focus = ref(false);
const scheduled = computed(() => taskStore.pendingTasks.filter(task => task.is_today && task.due_time)
    .sort((a, b) => (a.due_time ?? '').localeCompare(b.due_time ?? '')));
const flexible = computed(() => taskStore.pendingTasks.filter(task => task.is_today && !task.due_time));
const focusedTask = computed(() => [...scheduled.value, ...flexible.value].slice(0, 1));

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
        <div class="workspace-hero mb-6 p-5 sm:p-7">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                        <Calendar class="w-5 h-5 text-brand-600 dark:text-brand-400" />
                        <span>کارهای امروز</span>
                        <span class="text-xs font-normal text-zinc-400">({{ todayFormatted }})</span>
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                        {{ remainingToday > 0 ? `${remainingToday} تسک برای انجام در انتظار شماست.` : 'تمام تسک‌های امروز تکمیل شده یا هنوز تسکی ثبت نکرده‌اید.' }}
                    </p>
                </div>

                <div v-if="completedToday >= dailyGoal" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-800 dark:text-emerald-400 text-xs font-semibold">
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

        <button type="button" class="mb-4 rounded-lg border border-zinc-400 px-3 py-2 text-sm" :aria-pressed="focus" @click="focus = !focus">
            حالت تمرکز
        </button>

        <!-- Quick Add Form -->
        <div v-if="!focus" class="mb-6">
            <TaskQuickAdd :default-due-date="todayDateString" />
        </div>

        <!-- Tasks Loading Spinner or Task List -->
        <div v-if="taskStore.isLoading" class="flex items-center justify-center p-12 text-zinc-400">
            <Loader2 class="w-7 h-7 animate-spin text-brand-500" />
        </div>

        <div v-else-if="focus || scheduled.length || flexible.length" class="space-y-5">
            <TaskList v-if="focus" :tasks="focusedTask" empty-title="کاری برای تمرکز باقی نمانده است" />
            <template v-else>
                <section v-if="scheduled.length">
                    <h3 class="mb-2 font-semibold">زمان‌بندی‌شده</h3>
                    <TaskList :tasks="scheduled" />
                </section>
                <section v-if="flexible.length">
                    <h3 class="mb-2 font-semibold">بدون ساعت مشخص</h3>
                    <TaskList :tasks="flexible" />
                </section>
            </template>
        </div>

        <TaskList
            v-else
            :tasks="[]"
            empty-title="همه کارهای امروز انجام شده‌اند یا تسکی ثبت نشده"
            empty-description="برای برنامه‌ریزی روز پرانرژی، اولین کار امروزتان را با فرم بالا یا کلید Q اضافه کنید."
        />
    </AppLayout>
</template>
