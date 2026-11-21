<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { Activity, AlertCircle, LoaderCircle } from 'lucide-vue-next';
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
const period = computed(() => dashboard.velocity?.[range.value] ?? []);
const periodTotal = computed(() => period.value.reduce((sum, day) => sum + day.count, 0));

onMounted(dashboard.load);
watch(() => tasks.revision, () => {
    void dashboard.load();
});
</script>

<template>
    <AppLayout>
        <div class="page-heading">
            <h2 class="text-2xl font-extrabold sm:text-3xl">مرور بهره‌وری</h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">هدف روزانه، روند تکمیل کارها و وضعیت پروژه‌ها</p>
        </div>
        <div v-if="dashboard.error" role="alert" class="workspace-panel mb-5 flex flex-wrap items-center gap-3 p-4">
            <AlertCircle aria-hidden="true" class="size-5 text-rose-700 dark:text-rose-300" />
            <p class="flex-1 text-sm">دریافت آمار ممکن نشد.</p>
            <button type="button" class="rounded-lg bg-brand-50 px-3 py-2 text-sm font-semibold text-brand-700 dark:bg-brand-950 dark:text-brand-300" @click="dashboard.load">تلاش دوباره</button>
        </div>
        <div v-if="dashboard.loading && !dashboard.summary" role="status" class="workspace-panel flex items-center justify-center gap-2 p-10 text-sm">
            <LoaderCircle aria-hidden="true" class="size-5 animate-spin text-brand-600 dark:text-brand-300" />
            در حال دریافت آمار…
        </div>
        <div v-if="dashboard.summary && dashboard.velocity" class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <section class="workspace-hero p-5 sm:p-6">
                    <h3 class="font-semibold">نرخ تکمیل کل</h3>
                    <p class="mt-2 text-4xl font-extrabold tabular-nums text-brand-800 dark:text-brand-200">{{ dashboard.summary.completion_rate }}٪</p>
                    <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">{{ dashboard.summary.completed_today }} از {{ dashboard.summary.daily_goal }} تسک هدف روزانه</p>
                </section>
                <section class="workspace-panel p-5 sm:p-6">
                    <StreakBadge :current="dashboard.summary.current_streak_days" :best="dashboard.summary.best_streak_days" />
                </section>
            </div>
            <section class="workspace-panel p-5 sm:p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="flex items-center gap-2 font-bold"><Activity aria-hidden="true" class="size-4 text-brand-700 dark:text-brand-300" />کارهای تکمیل‌شده</h3>
                        <p class="mt-1 text-sm tabular-nums text-zinc-600 dark:text-zinc-300">{{ periodTotal }} تسک در این بازه</p>
                    </div>
                    <div role="group" aria-label="بازهٔ نمودار" class="segmented-control">
                        <button type="button" :aria-pressed="range === 'week'" @click="range = 'week'">۷ روز</button>
                        <button type="button" :aria-pressed="range === 'month'" @click="range = 'month'">۳۰ روز</button>
                    </div>
                </div>
                <WeeklyVelocityChart v-if="periodTotal" :days="period" />
                <p v-else class="rounded-xl border border-dashed px-4 py-8 text-center text-sm text-zinc-600 dark:text-zinc-300">در این بازه هنوز کاری تکمیل نشده است.</p>
            </section>
            <div class="grid gap-4 sm:grid-cols-2">
                <PriorityBreakdown class="workspace-panel p-5" :counts="dashboard.summary.priority_distribution" />
                <ProjectDistributionCard class="workspace-panel p-5" :projects="dashboard.summary.project_distribution" />
            </div>
        </div>
    </AppLayout>
</template>
