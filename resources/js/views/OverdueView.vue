<script setup lang="ts">
import { onMounted } from 'vue';
import { useTaskStore } from '@/stores/tasks';
import AppLayout from '@/components/layout/AppLayout.vue';
import TaskList from '@/components/task/TaskList.vue';
import { AlertCircle, CheckCircle2, Loader2 } from 'lucide-vue-next';

const taskStore = useTaskStore();

onMounted(async () => {
    await taskStore.fetchTasks('overdue');
});
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                <AlertCircle class="w-5 h-5 text-rose-500" />
                <span>کارهای عقب‌افتاده (Overdue)</span>
            </h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                تسک‌هایی که موعد انجام آن‌ها سپری شده و نیاز به بازبینی یا انتقال به امروز دارند
            </p>
        </div>

        <!-- Tasks Loading Spinner or Task List -->
        <div v-if="taskStore.isLoading" class="flex items-center justify-center p-12 text-zinc-400">
            <Loader2 class="w-7 h-7 animate-spin text-brand-500" />
        </div>

        <TaskList
            v-else
            :tasks="taskStore.tasks"
            empty-title="عالی است! هیچ کار عقب‌افتاده‌ای ندارید"
            empty-description="تمام کارهایتان به‌موقع انجام شده‌اند و هیچ تسک معوقه‌ای باقی نمانده است."
        />
    </AppLayout>
</template>
