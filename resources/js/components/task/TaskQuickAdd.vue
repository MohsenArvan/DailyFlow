<script setup lang="ts">
import { ref } from 'vue';
import { useTaskStore } from '@/stores/tasks';
import type { TaskPriority } from '@/types/task';
import { Plus, Calendar, Flag, Loader2 } from 'lucide-vue-next';

interface Props {
    defaultDueDate?: string | null;
    projectId?: number | null;
}

const props = withDefaults(defineProps<Props>(), {
    defaultDueDate: null,
    projectId: null,
});

const taskStore = useTaskStore();

const isExpanded = ref(false);
const title = ref('');
const priority = ref<TaskPriority>('medium');
const dueDate = ref<string | null>(props.defaultDueDate);
const isSubmitting = ref(false);
const inputRef = ref<HTMLInputElement | null>(null);

const formatLocalDate = (d: Date = new Date()): string => {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const setPriority = (p: TaskPriority) => {
    priority.value = p;
};

const setDateShortcut = (shortcut: 'today' | 'tomorrow' | 'none') => {
    if (shortcut === 'none') {
        dueDate.value = null;
    } else if (shortcut === 'today') {
        dueDate.value = formatLocalDate(new Date());
    } else if (shortcut === 'tomorrow') {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dueDate.value = formatLocalDate(tomorrow);
    }
};

const handleSubmit = async () => {
    if (!title.value.trim() || isSubmitting.value) return;

    isSubmitting.value = true;
    try {
        await taskStore.createTask({
            title: title.value.trim(),
            priority: priority.value,
            due_date: dueDate.value,
            project_id: props.projectId,
        });

        title.value = '';
        priority.value = 'medium';
        dueDate.value = props.defaultDueDate;
        inputRef.value?.focus();
    } finally {
        isSubmitting.value = false;
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        isExpanded.value = false;
        title.value = '';
    }
};
</script>

<template>
    <div class="w-full font-vazir select-none">
        <form
            @submit.prevent="handleSubmit"
            class="rounded-2xl border transition-all duration-200"
            :class="[
                isExpanded
                    ? 'border-brand-500/50 bg-surface-cardLight dark:bg-surface-cardDark shadow-card ring-2 ring-brand-500/10'
                    : 'border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight/70 dark:bg-surface-cardDark/70 hover:border-zinc-300 dark:hover:border-zinc-700'
            ]"
        >
            <!-- Input Row -->
            <div class="flex items-center gap-3 px-4 py-3">
                <Plus class="w-4 h-4 text-brand-600 dark:text-brand-400 shrink-0 stroke-[2.5]" />
                <input
                    ref="inputRef"
                    v-model="title"
                    type="text"
                    placeholder="افزودن تسک جدید... (Enter برای ثبت)"
                    @focus="isExpanded = true"
                    @keydown="handleKeydown"
                    class="w-full bg-transparent text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none"
                />
                <button
                    v-if="title.trim()"
                    type="submit"
                    :disabled="isSubmitting"
                    class="shrink-0 px-3 py-1 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5"
                >
                    <Loader2 v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin" />
                    <span v-else>ثبت</span>
                </button>
            </div>

            <!-- Optional Controls Row (Shown when expanded) -->
            <div
                v-if="isExpanded"
                class="flex flex-wrap items-center justify-between gap-2 px-4 py-2.5 border-t border-surface-borderLight/70 dark:border-surface-borderDark/70 text-xs text-zinc-500 bg-surface-subtleLight/40 dark:bg-surface-subtleDark/40 rounded-b-2xl"
            >
                <!-- Date Shortcuts -->
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] text-zinc-400 flex items-center gap-1">
                        <Calendar class="w-3 h-3" />
                        موعد:
                    </span>
                    <button
                        type="button"
                        @click="setDateShortcut('today')"
                        class="px-2 py-0.5 rounded-lg text-[11px] transition-colors"
                        :class="dueDate === formatLocalDate() ? 'bg-brand-500 text-white' : 'hover:bg-zinc-200 dark:hover:bg-zinc-700'"
                    >
                        امروز
                    </button>
                    <button
                        type="button"
                        @click="setDateShortcut('tomorrow')"
                        class="px-2 py-0.5 rounded-lg text-[11px] hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors"
                    >
                        فردا
                    </button>
                    <button
                        type="button"
                        @click="setDateShortcut('none')"
                        class="px-2 py-0.5 rounded-lg text-[11px] transition-colors"
                        :class="!dueDate ? 'bg-zinc-300 dark:bg-zinc-600 text-zinc-900 dark:text-zinc-100' : 'hover:bg-zinc-200 dark:hover:bg-zinc-700'"
                    >
                        اینباکس
                    </button>
                </div>

                <!-- Priority Shortcuts -->
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] text-zinc-400 flex items-center gap-1">
                        <Flag class="w-3 h-3" />
                        اولویت:
                    </span>
                    <button
                        type="button"
                        @click="setPriority('urgent')"
                        class="w-4 h-4 rounded-full border-2 border-rose-500 transition-transform"
                        :class="priority === 'urgent' ? 'bg-rose-500 scale-110' : ''"
                        title="فوری"
                    ></button>
                    <button
                        type="button"
                        @click="setPriority('high')"
                        class="w-4 h-4 rounded-full border-2 border-amber-500 transition-transform"
                        :class="priority === 'high' ? 'bg-amber-500 scale-110' : ''"
                        title="بالا"
                    ></button>
                    <button
                        type="button"
                        @click="setPriority('medium')"
                        class="w-4 h-4 rounded-full border-2 border-blue-500 transition-transform"
                        :class="priority === 'medium' ? 'bg-blue-500 scale-110' : ''"
                        title="متوسط"
                    ></button>
                    <button
                        type="button"
                        @click="setPriority('low')"
                        class="w-4 h-4 rounded-full border-2 border-zinc-400 transition-transform"
                        :class="priority === 'low' ? 'bg-zinc-400 scale-110' : ''"
                        title="پایین"
                    ></button>
                </div>
            </div>
        </form>
    </div>
</template>
