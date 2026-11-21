<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useTaskStore } from '@/stores/tasks';
import { useProjectStore } from '@/stores/projects';
import type { TaskPriority } from '@/types/task';
import BaseCheckbox from '@/components/ui/BaseCheckbox.vue';
import {
    X,
    Calendar,
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
const isEditingTitle = ref(false);

const handleTitleBlur = (e: Event) => {
    isEditingTitle.value = false;
    const target = e.target as HTMLInputElement;
    if (taskStore.currentTask && target.value.trim() && target.value.trim() !== taskStore.currentTask.title) {
        taskStore.updateTask(taskStore.currentTask.uuid, { title: target.value.trim() });
    }
};

const handleDescriptionBlur = (e: Event) => {
    const target = e.target as HTMLTextAreaElement;
    if (taskStore.currentTask && target.value !== taskStore.currentTask.description) {
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
    await taskStore.addSubtask(taskStore.currentTask.uuid, newSubtaskTitle.value.trim());
    newSubtaskTitle.value = '';
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

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && taskStore.isDetailsOpen) {
        taskStore.closeDetails();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    if (!projectStore.projects.length) {
        projectStore.fetchProjects();
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <div v-if="taskStore.isDetailsOpen && taskStore.currentTask" class="fixed inset-0 z-50 font-vazir text-right">
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-zinc-950/40 backdrop-blur-xs transition-opacity"
                @click="taskStore.closeDetails"
            ></div>

            <!-- Slide-over Drawer -->
            <div class="fixed inset-y-0 left-0 max-w-full w-full sm:w-[480px] bg-surface-cardLight dark:bg-surface-cardDark shadow-modal border-r border-surface-borderLight dark:border-surface-borderDark flex flex-col z-10 animate-in slide-in-from-left duration-200">
                <!-- Header Actions -->
                <div class="h-16 px-6 border-b border-surface-borderLight dark:border-surface-borderDark flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="handleToggleComplete"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors"
                            :class="[
                                taskStore.currentTask.status === 'completed'
                                    ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700'
                                    : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20'
                            ]"
                        >
                            <RotateCcw v-if="taskStore.currentTask.status === 'completed'" class="w-3.5 h-3.5" />
                            <CheckCircle2 v-else class="w-3.5 h-3.5" />
                            <span>{{ taskStore.currentTask.status === 'completed' ? 'بازگشایی تسک' : 'تکمیل تسک' }}</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            @click="handleDeleteTask"
                            class="p-2 rounded-xl text-zinc-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors"
                            title="حذف تسک"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                        <button
                            type="button"
                            @click="taskStore.closeDetails"
                            class="p-2 rounded-xl text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                            title="بستن (Esc)"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Main Scrollable Body -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <!-- Title Input -->
                    <div>
                        <input
                            :value="taskStore.currentTask.title"
                            @blur="handleTitleBlur"
                            @keyup.enter="($event.target as HTMLInputElement).blur()"
                            type="text"
                            class="w-full text-base sm:text-lg font-bold bg-transparent text-zinc-900 dark:text-zinc-50 border-b border-transparent focus:border-brand-500 focus:outline-none transition-colors py-1"
                        />
                    </div>

                    <!-- Meta Properties (Priority, Due Date, Project) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-2xl bg-surface-subtleLight dark:bg-surface-subtleDark border border-surface-borderLight dark:border-surface-borderDark text-xs">
                        <!-- Priority Selector -->
                        <div>
                            <label class="block text-[11px] text-zinc-400 mb-1 flex items-center gap-1">
                                <Flag class="w-3 h-3" />
                                اولویت
                            </label>
                            <select
                                :value="taskStore.currentTask.priority"
                                @change="handlePriorityChange"
                                class="w-full bg-surface-cardLight dark:bg-surface-cardDark border border-surface-borderLight dark:border-surface-borderDark rounded-xl px-2.5 py-1.5 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-brand-500 text-xs"
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
                            <label class="block text-[11px] text-zinc-400 mb-1 flex items-center gap-1">
                                <Calendar class="w-3 h-3" />
                                موعد انجام
                            </label>
                            <input
                                type="date"
                                :value="taskStore.currentTask.due_date || ''"
                                @change="handleDueDateChange"
                                class="w-full bg-surface-cardLight dark:bg-surface-cardDark border border-surface-borderLight dark:border-surface-borderDark rounded-xl px-2.5 py-1.5 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-brand-500 text-xs"
                            />
                        </div>

                        <!-- Project Selector -->
                        <div>
                            <label class="block text-[11px] text-zinc-400 mb-1 flex items-center gap-1">
                                <Folder class="w-3 h-3" />
                                پروژه
                            </label>
                            <select
                                :value="taskStore.currentTask.project_id || ''"
                                @change="handleProjectChange"
                                class="w-full bg-surface-cardLight dark:bg-surface-cardDark border border-surface-borderLight dark:border-surface-borderDark rounded-xl px-2.5 py-1.5 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-brand-500 text-xs truncate"
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

                    <!-- Description Textarea -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1.5">
                            توضیحات و یادداشت‌ها
                        </label>
                        <textarea
                            :value="taskStore.currentTask.description || ''"
                            @blur="handleDescriptionBlur"
                            rows="4"
                            placeholder="یادداشت‌های بیشتر درباره این تسک بنویسید..."
                            class="w-full p-3.5 rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-xs sm:text-sm text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors resize-y leading-relaxed"
                        ></textarea>
                    </div>

                    <!-- Subtasks Checklist -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                چک‌لیست زیرتسک‌ها
                            </h4>
                            <span v-if="taskStore.currentTask.subtasks?.length" class="text-[11px] font-medium text-zinc-400">
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
                                        :model-value="subtask.is_completed"
                                        @change="() => handleToggleSubtask(subtask.id)"
                                    />
                                    <span
                                        class="truncate font-medium transition-colors cursor-pointer"
                                        :class="subtask.is_completed ? 'text-zinc-400 line-through' : 'text-zinc-800 dark:text-zinc-200'"
                                        @click="handleToggleSubtask(subtask.id)"
                                    >
                                        {{ subtask.title }}
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    @click="handleDeleteSubtask(subtask.id)"
                                    class="opacity-0 group-hover:opacity-100 p-1 text-zinc-400 hover:text-rose-500 transition-opacity"
                                    title="حذف زیرتسک"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- Add Subtask Input -->
                        <form @submit.prevent="handleAddSubtask" class="flex items-center gap-2">
                            <input
                                v-model="newSubtaskTitle"
                                type="text"
                                placeholder="+ افزودن مورد جدید به چک‌لیست (Enter)..."
                                class="w-full px-3 py-2 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-xs text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-brand-500"
                            />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
