<script setup lang="ts">
import { onMounted } from 'vue';
import { useTaskStore } from '@/stores/tasks';
import AppLayout from '@/components/layout/AppLayout.vue';
import TaskList from '@/components/task/TaskList.vue';
import { CheckSquare, Loader2 } from 'lucide-vue-next';

const taskStore = useTaskStore();

onMounted(async () => {
    await taskStore.fetchTasks('completed');
});
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
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

        <TaskList
            v-else
            :tasks="taskStore.tasks"
            empty-title="هنوز تسک انجام‌شده‌ای در سابقه ثبت نشده است"
            empty-description="با تکمیل اولین کار در بخش امروز یا اینباکس، تاریخچه دستاوردهایتان اینجا ثبت خواهد شد."
        />
    </AppLayout>
</template>
