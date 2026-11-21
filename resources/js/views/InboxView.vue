<script setup lang="ts">
import { onMounted } from 'vue';
import { useTaskStore } from '@/stores/tasks';
import AppLayout from '@/components/layout/AppLayout.vue';
import TaskQuickAdd from '@/components/task/TaskQuickAdd.vue';
import TaskList from '@/components/task/TaskList.vue';
import { Inbox, Loader2 } from 'lucide-vue-next';

const taskStore = useTaskStore();

onMounted(async () => {
    await taskStore.fetchTasks('inbox');
});
</script>

<template>
    <AppLayout>
        <div class="page-heading flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                    <Inbox class="w-5 h-5 text-zinc-500" />
                    <span>صندوق ورودی (Inbox)</span>
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    جایگاهی برای ثبت سریع ایده‌ها و کارهایی که هنوز زمان‌بندی نشده‌اند
                </p>
            </div>
        </div>

        <!-- Quick Add Form -->
        <div class="mb-6">
            <TaskQuickAdd :default-due-date="null" />
        </div>

        <!-- Tasks Loading Spinner or Task List -->
        <div v-if="taskStore.isLoading" class="flex items-center justify-center p-12 text-zinc-400">
            <Loader2 class="w-7 h-7 animate-spin text-brand-500" />
        </div>

        <TaskList
            v-else
            :tasks="taskStore.tasks"
            empty-title="صندوق ورودی شما خلوت و تمیز است"
            empty-description="هر زمان کاری به ذهنتان رسید، سریعاً آن را به اینباکس بسپارید تا بعداً زمان‌بندی و دسته‌بندی کنید."
        />
    </AppLayout>
</template>
