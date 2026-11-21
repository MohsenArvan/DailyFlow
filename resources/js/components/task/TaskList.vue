<script setup lang="ts">
import type { Task } from '@/types/task';
import { useTaskStore } from '@/stores/tasks';
import TaskItem from '@/components/task/TaskItem.vue';
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue';

interface Props {
    tasks: Task[];
    emptyTitle?: string;
    emptyDescription?: string;
}

withDefaults(defineProps<Props>(), {
    emptyTitle: 'هیچ تسکی وجود ندارد',
    emptyDescription: 'با افزودن اولین کار، برنامه‌ریزی روزانه‌تان را آغاز کنید.',
});

const taskStore = useTaskStore();

const handleToggleComplete = (task: Task) => {
    if (task.status === 'completed') {
        taskStore.reopenTask(task.uuid);
    } else {
        taskStore.completeTask(task.uuid);
    }
};

const handleSelectTask = (task: Task) => {
    taskStore.openDetails(task);
};
</script>

<template>
    <div class="w-full font-vazir">
        <div v-if="tasks.length > 0" class="space-y-2">
            <TaskItem
                v-for="task in tasks"
                :key="task.uuid"
                :task="task"
                @toggle-complete="handleToggleComplete"
                @select="handleSelectTask"
            />
        </div>

        <div
            v-else
            class="workspace-panel flex min-h-[220px] items-center justify-center border-dashed p-4"
        >
            <BaseEmptyState
                :title="emptyTitle"
                :description="emptyDescription"
            />
        </div>
    </div>
</template>
