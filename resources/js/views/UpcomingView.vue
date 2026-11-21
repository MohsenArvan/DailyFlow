<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { useAgendaDates } from '@/composables/useAgendaDates';
import { useTaskStore } from '@/stores/tasks';
import AppLayout from '@/components/layout/AppLayout.vue';
import TaskQuickAdd from '@/components/task/TaskQuickAdd.vue';
import TaskList from '@/components/task/TaskList.vue';
import { CalendarDays, Loader2 } from 'lucide-vue-next';

const taskStore = useTaskStore();

const { offsetDate, formatDay } = useAgendaDates();
const tomorrowDateString = computed(() => offsetDate(1));
const groups = computed(() => Array.from({ length: 7 }, (_, index) => {
    const date = offsetDate(index + 1);
    return {
        date,
        title: index === 0 ? 'فردا' : index === 1 ? 'پس‌فردا' : formatDay(date),
        tasks: taskStore.pendingTasks.filter(task => task.due_date === date),
    };
}).filter(group => group.tasks.length));

onMounted(async () => {
    await taskStore.fetchTasks('upcoming');
});
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                    <CalendarDays class="w-5 h-5 text-violet-500" />
                    <span>برنامه‌ریزی آینده (Upcoming)</span>
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    مشاهده کارهای زمان‌بندی‌شده برای روزها و هفته‌های آتی
                </p>
            </div>
        </div>

        <!-- Quick Add Form -->
        <div class="mb-6">
            <TaskQuickAdd :default-due-date="tomorrowDateString" />
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
            empty-title="تسک زمان‌بندی‌شده‌ای برای روزهای آینده وجود ندارد"
            empty-description="برای روزهای پیش‌رو برنامه‌ریزی کنید تا در ادامه هفته ذهنی آسوده داشته باشید."
        />
    </AppLayout>
</template>
