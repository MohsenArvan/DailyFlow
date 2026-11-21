<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import WeeklyVelocityChart from '@/components/dashboard/WeeklyVelocityChart.vue';
import StreakBadge from '@/components/dashboard/StreakBadge.vue';
import PriorityBreakdown from '@/components/dashboard/PriorityBreakdown.vue';
import ProjectDistributionCard from '@/components/dashboard/ProjectDistributionCard.vue';
import { useDashboardStore } from '@/stores/dashboard';
import { useTaskStore } from '@/stores/tasks';

const dashboard = useDashboardStore();
const tasks = useTaskStore();
const range = ref<'week' | 'month'>('week');

onMounted(dashboard.load);
watch(() => tasks.revision, () => {
    void dashboard.load();
});
</script>

<template>
    <AppLayout>
        <h1 class="mb-5 text-2xl font-bold">داشبورد</h1>
        <p v-if="dashboard.loading" role="status" class="mb-3 text-sm">در حال دریافت آمار…</p>
        <p v-if="dashboard.error" role="alert" class="mb-4">
            دریافت آمار ممکن نشد.
            <button class="underline" @click="dashboard.load">تلاش دوباره</button>
        </p>
        <div v-if="dashboard.summary && dashboard.velocity" class="space-y-6">
            <section class="rounded-2xl border border-surface-borderLight bg-surface-cardLight p-5 dark:border-surface-borderDark dark:bg-surface-cardDark">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-semibold">کارهای تکمیل‌شده</h2>
                    <label class="text-sm">
                        <span class="sr-only">بازهٔ نمودار</span>
                        <select v-model="range" class="rounded-lg border border-zinc-400 bg-transparent px-2 py-1 dark:bg-surface-cardDark">
                            <option value="week">۷ روز اخیر</option>
                            <option value="month">۳۰ روز اخیر</option>
                        </select>
                    </label>
                </div>
                <WeeklyVelocityChart :days="dashboard.velocity[range]" />
            </section>
            <div class="grid grid-cols-1 gap-6 border-b border-zinc-200 pb-6 sm:grid-cols-2 dark:border-zinc-700">
                <StreakBadge :current="dashboard.summary.current_streak_days" :best="dashboard.summary.best_streak_days" />
                <section>
                    <h2 class="font-semibold">نرخ تکمیل کل</h2>
                    <p class="mt-3 text-3xl font-bold tabular-nums">{{ dashboard.summary.completion_rate }}٪</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ dashboard.summary.completed_today }} از {{ dashboard.summary.daily_goal }} تسک هدف روزانه</p>
                </section>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <PriorityBreakdown :counts="dashboard.summary.priority_distribution" />
                <ProjectDistributionCard :projects="dashboard.summary.project_distribution" />
            </div>
        </div>
    </AppLayout>
</template>
