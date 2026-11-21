<script setup lang="ts">
import { useDialogFocus } from '@/composables/useDialogFocus';
import { ref, watch, onMounted } from 'vue';
import { useTaskStore } from '@/stores/tasks';
import { useProjectStore } from '@/stores/projects';
import type { TaskPriority, TaskStatus } from '@/types/task';
import TaskScheduleFields from './TaskScheduleFields.vue';
import BaseCheckbox from '@/components/ui/BaseCheckbox.vue';
import {
    X,
    Calendar,
    CircleDot,
    ListChecks,
    Flag,
    Folder,
    Trash2,
    Plus,
    CheckCircle2,
    RotateCcw,
} from 'lucide-vue-next';

const taskStore = useTaskStore();
const projectStore = useProjectStore();

const newSubtaskTitle = ref('');
const statusSaving = ref(false);
watch(() => taskStore.currentTask?.uuid, () => { newSubtaskTitle.value = ''; });

async function handleStatusChange(event: Event) {
    const field = event.target as HTMLSelectElement;
    const task = taskStore.currentTask;
    if (!task) return;
    const status = field.value as TaskStatus;
    field.value = task.status;
    if (statusSaving.value || status === task.status) return;
    statusSaving.value = true;
    try {
        await taskStore.updateTask(task.uuid, { status });
    } finally {
        statusSaving.value = false;
    }
}

const handleTitleBlur = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (taskStore.currentTask && target.value.trim() && target.value.trim() !== taskStore.currentTask.title) {
        taskStore.updateTask(taskStore.currentTask.uuid, { title: target.value.trim() });
    }
};

const handleDescriptionBlur = (e: Event) => {
    const target = e.target as HTMLTextAreaElement;
    if (taskStore.currentTask && target.value !== (taskStore.currentTask.description ?? '')) {
        taskStore.updateTask(taskStore.currentTask.uuid, { description: target.value });
    }
};

const handlePriorityChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    if (taskStore.currentTask) {
        taskStore.updateTask(taskStore.currentTask.uuid, { priority: target.value as TaskPriority });
    }
};

const handleDueDateChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (taskStore.currentTask) {
        taskStore.updateTask(taskStore.currentTask.uuid, { due_date: target.value || null });
    }
};

const handleProjectChange = (e: Event) => {
    const target = e.target as HTMLSelectElement;
    if (taskStore.currentTask) {
        const val = target.value ? parseInt(target.value, 10) : null;
        taskStore.updateTask(taskStore.currentTask.uuid, { project_id: val });
    }
};

const handleAddSubtask = async () => {
    if (!newSubtaskTitle.value.trim() || !taskStore.currentTask) return;
    if (await taskStore.addSubtask(taskStore.currentTask.uuid, newSubtaskTitle.value.trim())) newSubtaskTitle.value = '';
};

const handleToggleSubtask = async (subtaskId: number) => {
    await taskStore.toggleSubtask(subtaskId);
};

const handleDeleteSubtask = async (subtaskId: number) => {
    await taskStore.deleteSubtask(subtaskId);
};

const handleToggleComplete = () => {
    if (!taskStore.currentTask) return;
    if (taskStore.currentTask.status === 'completed') {
        taskStore.reopenTask(taskStore.currentTask.uuid);
    } else {
        taskStore.completeTask(taskStore.currentTask.uuid);
    }
};

const handleDeleteTask = async () => {
    if (!taskStore.currentTask) return;
    if (confirm('آیا از حذف این تسک اطمینان دارید؟')) {
        await taskStore.deleteTask(taskStore.currentTask.uuid);
    }
};

const dialogRoot = ref<HTMLElement>();
const trapFocus = useDialogFocus(dialogRoot, () => taskStore.isDetailsOpen);

const handleKeydown = (e: KeyboardEvent) => {
    trapFocus(e);
    if (e.key === 'Escape' && taskStore.isDetailsOpen) {
        e.preventDefault();
        taskStore.closeDetails();
    }
};

onMounted(() => {
    if (!projectStore.projects.length) {
        projectStore.fetchProjects();
    }
});

</script>

<template>
    <Teleport to="body">
        <div v-if="taskStore.isDetailsOpen && taskStore.currentTask" class="fixed inset-0 z-50 grid place-items-center p-3 font-vazir text-right sm:p-6">
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-slate-950/65 backdrop-blur-sm"
                @click="taskStore.closeDetails"
            ></div>

            <!-- Task details dialog -->
            <div ref="dialogRoot" role="dialog" aria-modal="true" tabindex="-1" @keydown="handleKeydown" aria-label="جزئیات تسک" class="workspace-panel relative z-10 flex max-h-[calc(100dvh-1.5rem)] min-w-0 w-full max-w-[43rem] flex-col overflow-hidden shadow-modal sm:max-h-[calc(100dvh-3rem)]">
                <!-- Header Actions -->
                <div class="flex shrink-0 flex-wrap items-center justify-between gap-2 border-b bg-brand-50/70 px-4 py-3 dark:bg-brand-950/45 sm:px-6">
                    <h2 class="flex items-center gap-2 font-bold">
                        <ListChecks aria-hidden="true" class="size-5 text-brand-700 dark:text-brand-300" /> جزئیات تسک
                    </h2>
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            @click="handleToggleComplete"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors"
                            :class="[
                                taskStore.currentTask.status === 'completed'
                                    ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700'
                                    : 'bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-500/20'
                            ]"
                        >
                            <RotateCcw v-if="taskStore.currentTask.status === 'completed'" class="w-3.5 h-3.5" />
                            <CheckCircle2 v-else class="w-3.5 h-3.5" />
                            <span>{{ taskStore.currentTask.status === 'completed' ? 'بازگشایی تسک' : 'تکمیل تسک' }}</span>
                        </button>
                        <button
                            type="button"
                            @click="handleDeleteTask"
                            class="p-2 rounded-xl text-zinc-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors"
                            title="حذف تسک" aria-label="حذف تسک"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                        <button
                            type="button"
                            @click="taskStore.closeDetails"
                            class="p-2 rounded-xl text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                            title="بستن (Esc)" aria-label="بستن جزئیات تسک"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Main Scrollable Body -->
                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain px-4 py-5 sm:px-6">
                    <!-- Title Input -->
                    <div>
                        <label for="task-editor-title" class="task-field-label">عنوان تسک</label>
                        <input
                            id="task-editor-title"
                            aria-label="عنوان تسک"
                            :value="taskStore.currentTask.title"
                            @blur="handleTitleBlur"
                            @keyup.enter="($event.target as HTMLInputElement).blur()"
                            type="text"
                            class="task-field !text-base font-bold"
                        />
                    </div>

                    <!-- Meta Properties (Priority, Due Date, Project) -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="task-editor-status" class="task-field-label"><CircleDot class="size-4" aria-hidden="true" /> وضعیت تسک</label>
                            <select id="task-editor-status" class="task-field" :value="taskStore.currentTask.status"
                                :aria-busy="statusSaving" @change="handleStatusChange">
                                <option value="pending">در انتظار</option>
                                <option value="in_progress">در حال انجام</option>
                                <option value="completed">انجام‌شده</option>
                            </select>
                        </div>
                        <!-- Priority Selector -->
                        <div>
                            <label for="task-editor-priority" class="task-field-label">
                                <Flag class="w-3 h-3" />
                                اولویت
                            </label>
                            <select
                                id="task-editor-priority"
                                :value="taskStore.currentTask.priority"
                                @change="handlePriorityChange"
                                class="task-field"
                            >
                                <option value="none">بدون اولویت</option>
                                <option value="low">پایین</option>
                                <option value="medium">متوسط</option>
                                <option value="high">بالا</option>
                                <option value="urgent">فوری</option>
                            </select>
                        </div>

                        <!-- Due Date Selector -->
                        <div>
                            <label for="task-editor-date" class="task-field-label">
                                <Calendar class="w-3 h-3" />
                                موعد انجام
                            </label>
                            <input
                                type="date"
                                id="task-editor-date"
                                :value="taskStore.currentTask.due_date || ''"
                                @change="handleDueDateChange"
                                class="task-field"
                            />
                        </div>

                        <!-- Project Selector -->
                        <div>
                            <label for="task-editor-project" class="task-field-label">
                                <Folder class="w-3 h-3" />
                                پروژه
                            </label>
                            <select
                                id="task-editor-project"
                                :value="taskStore.currentTask.project_id || ''"
                                @change="handleProjectChange"
                                class="task-field truncate"
                            >
                                <option value="">اینباکس (بدون پروژه)</option>
                                <option
                                    v-for="p in projectStore.projects"
                                    :key="p.id"
                                    :value="p.id"
                                >
                                    {{ p.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <TaskScheduleFields :key="taskStore.currentTask.uuid" :task="taskStore.currentTask" />

                    <!-- Description Textarea -->
                    <div>
                        <label for="task-editor-description" class="task-field-label">
                            توضیحات و یادداشت‌ها
                        </label>
                        <textarea
                            id="task-editor-description"
                            :value="taskStore.currentTask.description || ''"
                            @blur="handleDescriptionBlur"
                            rows="4"
                            placeholder="یادداشت‌های بیشتر درباره این تسک بنویسید..."
                            class="task-field resize-y leading-relaxed"
                        ></textarea>
                    </div>

                    <!-- Subtasks Checklist -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                چک‌لیست زیرتسک‌ها
                            </h4>
                            <span v-if="taskStore.currentTask.subtasks?.length" class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400">
                                {{ taskStore.currentTask.subtasks_completed_count }} از {{ taskStore.currentTask.subtasks_count }}
                            </span>
                        </div>

                        <!-- Subtasks List -->
                        <div class="space-y-1.5 mb-3">
                            <div
                                v-for="subtask in taskStore.currentTask.subtasks"
                                :key="subtask.id"
                                class="flex items-center justify-between gap-3 p-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark text-xs group"
                            >
                                <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                    <BaseCheckbox
                                        :aria-label="subtask.title"
                                        :model-value="subtask.is_completed"
                                        @change="() => handleToggleSubtask(subtask.id)"
                                    />
                                    <span
                                        class="truncate font-medium transition-colors cursor-pointer"
                                        :class="subtask.is_completed ? 'text-zinc-600 dark:text-zinc-400 line-through' : 'text-zinc-800 dark:text-zinc-200'"
                                        @click="handleToggleSubtask(subtask.id)"
                                    >
                                        {{ subtask.title }}
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    @click="handleDeleteSubtask(subtask.id)"
                                    class="opacity-0 group-hover:opacity-100 focus-visible:opacity-100 p-1 text-zinc-400 hover:text-rose-500 transition-opacity"
                                    title="حذف زیرتسک" aria-label="حذف زیرتسک"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- Add Subtask Input -->
                        <form @submit.prevent="handleAddSubtask" class="flex items-center gap-2">
                            <input
                                aria-label="افزودن زیرتسک"
                                v-model="newSubtaskTitle"
                                type="text"
                                placeholder="+ افزودن مورد جدید به چک‌لیست (Enter)..."
                                class="task-field min-w-0"
                            />
                            <button type="submit" :disabled="!newSubtaskTitle.trim()"
                                class="inline-flex min-h-11 shrink-0 items-center gap-1.5 rounded-xl bg-brand-700 px-3 text-sm font-semibold text-white disabled:opacity-50">
                                <Plus aria-hidden="true" class="size-4" /> افزودن
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
