<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useAgendaDates } from '@/composables/useAgendaDates';
import { useTaskStore } from '@/stores/tasks';
import AppLayout from '@/components/layout/AppLayout.vue';
import TaskList from '@/components/task/TaskList.vue';
import { CheckSquare, Loader2 } from 'lucide-vue-next';

const taskStore = useTaskStore();
const { localDate, offsetDate, formatDay } = useAgendaDates();
const groups = computed(() => {
    const dates = new Map<string, typeof taskStore.tasks>();
    for (const task of taskStore.completedTasks) {
        const date = task.completed_at ? localDate(new Date(task.completed_at)) : '';
        const items = dates.get(date) ?? [];
        items.push(task);
        dates.set(date, items);
    }

    return [...dates].sort(([a], [b]) => b.localeCompare(a)).map(([date, tasks]) => ({
        date,
        tasks,
        title: date === offsetDate(0) ? 'امروز' : date === offsetDate(-1) ? 'دیروز' : date ? formatDay(date) : 'بدون تاریخ تکمیل',
    }));
});

onMounted(async () => {
    await taskStore.fetchTasks('completed');
});
</script>

<template>
    <AppLayout>
        <div class="page-heading">
            <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                <CheckSquare class="w-5 h-5 text-emerald-500" />
                <span>کارهای تکمیل‌شده</span>
            </h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                آرشیو کارهای انجام‌شده شما در طول روزها و هفته‌های گذشته
            </p>
        </div>

        <!-- Tasks Loading Spinner or Task List -->
        <div v-if="taskStore.isLoading" class="flex items-center justify-center p-12 text-zinc-400">
            <Loader2 class="w-7 h-7 animate-spin text-brand-500" />
        </div>

        <div v-else-if="groups.length" class="space-y-5">
            <section v-for="group in groups" :key="group.date">
                <h3 class="mb-2 font-semibold">{{ group.title }}</h3>
                <TaskList :tasks="group.tasks" />
            </section>
        </div>

        <TaskList
            v-else
            :tasks="[]"
            empty-title="هنوز تسک انجام‌شده‌ای در سابقه ثبت نشده است"
            empty-description="با تکمیل اولین کار در بخش امروز یا اینباکس، تاریخچه دستاوردهایتان اینجا ثبت خواهد شد."
        />
    </AppLayout>
</template>
