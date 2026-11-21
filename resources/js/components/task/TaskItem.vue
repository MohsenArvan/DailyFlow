<script setup lang="ts">
import { computed } from 'vue';
import { useAgendaDates } from '@/composables/useAgendaDates';
import type { Task } from '@/types/task';
import BaseCheckbox from '@/components/ui/BaseCheckbox.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import { Bell, Calendar, CheckSquare, Repeat2 } from 'lucide-vue-next';

interface Props {
    task: Task;
}

const { formatDay } = useAgendaDates();
const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'toggleComplete', task: Task): void;
    (e: 'select', task: Task): void;
}>();

const isCompleted = computed(() => props.task.status === 'completed');

const handleCheckboxToggle = () => {
    emit('toggleComplete', props.task);
};

const handleItemClick = () => {
    emit('select', props.task);
};

const dateBadgeVariant = computed(() => {
    if (props.task.is_overdue) return 'danger';
    if (props.task.is_today) return 'brand';
    return 'default';
});
</script>

<template>
    <div
        class="group relative flex items-center flex-wrap justify-between gap-2.5 px-3.5 py-3 rounded-xl sm:flex-nowrap border transition-all duration-150 font-vazir select-none"
        :class="[
            isCompleted
                ? 'bg-zinc-50/70 dark:bg-zinc-900/40 border-surface-borderLight/60 dark:border-surface-borderDark/60 '
                : 'bg-surface-cardLight dark:bg-surface-cardDark border-surface-borderLight dark:border-surface-borderDark hover:border-zinc-300 dark:hover:border-zinc-700 hover:shadow-xs shadow-subtle',
        ]"
    >
        <!-- Left (in RTL: Right) Section: Checkbox + Title -->
        <div class="flex items-center gap-3 flex-1 min-w-[9rem]">
            <!-- Custom Checkbox -->
            <div @click.stop>
                <BaseCheckbox
                    :aria-label="task.title"
                    :model-value="isCompleted"
                    :priority="task.priority"
                    @change="handleCheckboxToggle"
                />
            </div>

            <!-- Task Title & Meta -->
            <button type="button" class="min-w-0 flex-1 text-right" @click="handleItemClick">
                <p
                    class="text-xs sm:text-sm font-medium leading-tight truncate transition-colors"
                    :class="[
                        isCompleted
                            ? 'text-zinc-600 dark:text-zinc-400 line-through'
                            : 'text-zinc-900 dark:text-zinc-100 group-hover:text-brand-600 dark:group-hover:text-brand-400',
                    ]"
                >
                    {{ task.title }}
                </p>

                <span v-if="task.tags?.length" class="mt-0.5 block truncate text-xs text-zinc-600 dark:text-zinc-300">
                    {{ task.tags.join('، ') }}
                </span>
                <p v-if="task.description && !isCompleted" class="text-[11px] text-zinc-600 dark:text-zinc-400 truncate mt-0.5">
                    {{ task.description }}
                </p>
            </button>
        </div>

        <!-- Right (in RTL: Left) Section: Badges & Actions -->
        <div class="flex flex-wrap items-center gap-1.5 cursor-pointer" @click="handleItemClick">
            <span v-if="task.status === 'in_progress'" class="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-800 dark:text-amber-300">در حال انجام</span>
            <Repeat2 v-if="task.recurrence_rule" role="img" aria-label="تکرارشونده" class="size-4" />
            <Bell v-if="task.reminder_at" role="img" aria-label="یادآور دارد" class="size-4" />
            <time v-if="task.due_time" dir="ltr" class="text-xs">{{ task.due_time.slice(0, 5) }}</time>
            <!-- Subtasks Progress Pill -->
            <span
                v-if="task.subtasks_count > 0"
                class="flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark font-medium text-zinc-500 dark:text-zinc-400"
                :title="`${task.subtasks_completed_count} از ${task.subtasks_count} زیرتسک تکمیل شده`"
            >
                <CheckSquare class="w-3 h-3 text-zinc-400" />
                <span>{{ task.subtasks_completed_count }}/{{ task.subtasks_count }}</span>
            </span>

            <!-- Priority Badge (if not none) -->
            <BaseBadge
                v-if="task.priority !== 'none' && !isCompleted"
                :variant="task.priority as any"
                size="sm"
                :dot="true"
            >
                {{ task.priority_label }}
            </BaseBadge>

            <!-- Due Date Badge -->
            <span
                v-if="task.due_date && !isCompleted"
                class="flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full border font-medium"
                :class="[
                    task.is_overdue
                        ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20'
                        : (task.is_today
                            ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 border-brand-500/20'
                            : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border-surface-borderLight dark:border-surface-borderDark')
                ]"
            >
                <Calendar class="w-3 h-3" />
                <span>{{ task.is_today ? 'امروز' : (task.is_overdue ? 'عقب‌افتاده' : formatDay(task.due_date)) }}</span>
            </span>
        </div>
    </div>
</template>
