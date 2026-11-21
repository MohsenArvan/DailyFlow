<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import { GripVertical } from 'lucide-vue-next';
import { useTaskStore } from '@/stores/tasks';
import type { Task, TaskStatus } from '@/types/task';

const props = defineProps<{ tasks: Task[]; projectNames?: ReadonlyMap<number, string> }>();
const store = useTaskStore();
const columns = [
    { status: 'pending', label: 'در انتظار', dot: 'bg-zinc-500' },
    { status: 'in_progress', label: 'در حال انجام', dot: 'bg-amber-500' },
    { status: 'completed', label: 'انجام‌شده', dot: 'bg-emerald-500' },
] as const;
const grouped = computed(() => ({
    pending: props.tasks.filter(task => task.status === 'pending'),
    in_progress: props.tasks.filter(task => task.status === 'in_progress'),
    completed: props.tasks.filter(task => task.status === 'completed'),
}));
const dragged = ref<string>();
const saving = ref(new Set<string>());
const feedback = ref('');

async function move(task: Task, status: TaskStatus, restoreFocus = false) {
    if (task.status === status || saving.value.has(task.uuid)) return;
    saving.value.add(task.uuid);
    feedback.value = '';
    try {
        const updated = await store.updateTask(task.uuid, { status });
        if (updated) feedback.value = `«${task.title}» به ستون ${columns.find(column => column.status === status)?.label} منتقل شد.`;
    } finally {
        saving.value.delete(task.uuid);
        if (restoreFocus) {
            await nextTick();
            const active = document.activeElement;
            if (!active || active === document.body) document.getElementById('board-status-' + task.uuid)?.focus();
        }
    }
}

function startDrag(event: DragEvent, task: Task) {
    if (!event.dataTransfer || saving.value.has(task.uuid)) {
        event.preventDefault();
        return;
    }
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', task.uuid);
    dragged.value = task.uuid;
}

function drop(status: TaskStatus) {
    const task = props.tasks.find(item => item.uuid === dragged.value);
    dragged.value = undefined;
    if (task) void move(task, status);
}

function changeStatus(event: Event, task: Task) {
    const control = event.target as HTMLSelectElement;
    const status = control.value as TaskStatus;
    control.value = task.status;
    void move(task, status, true);
}
</script>

<template>
    <p class="sr-only" role="status">{{ feedback }}</p>
    <div class="grid min-w-0 gap-4 lg:grid-cols-3" aria-label="تابلوی کانبان">
        <section v-for="column in columns" :key="column.status"
            :aria-label="column.label"
            class="board-column min-w-0 rounded-2xl border p-3 sm:p-4"
            :class="{ 'ring-2 ring-inset ring-brand-500/50': dragged && grouped[column.status].every(task => task.uuid !== dragged) }"
            @dragover.prevent
            @drop.prevent="drop(column.status)">
            <header class="mb-4 flex items-center gap-2">
                <span class="size-2 shrink-0 rounded-full" :class="column.dot" aria-hidden="true"></span>
                <h4 class="flex-1 text-sm font-bold">{{ column.label }}</h4>
                <span class="rounded-full bg-surface-cardLight px-2 py-0.5 text-xs tabular-nums dark:bg-surface-cardDark">{{ grouped[column.status].length }}</span>
            </header>
            <p v-if="!grouped[column.status].length" class="rounded-xl border border-dashed p-4 text-center text-sm text-zinc-600 dark:text-zinc-400">تسکی در این ستون نیست</p>
            <ul v-else class="space-y-2">
                <li v-for="task in grouped[column.status]" :key="task.uuid" class="workspace-panel space-y-3 p-3">
                    <div class="flex items-start gap-2">
                        <button type="button" class="min-w-0 flex-1 break-words text-start text-sm font-semibold hover:underline" @click="store.openDetails(task)">{{ task.title }}</button>
                        <span draggable="true" title="کشیدن تسک" aria-hidden="true"
                            class="inline-flex size-7 shrink-0 cursor-grab items-center justify-center rounded-md text-zinc-600 hover:bg-surface-subtleLight active:cursor-grabbing dark:text-zinc-400 dark:hover:bg-surface-subtleDark"
                            @dragstart="startDrag($event, task)" @dragend="dragged = undefined">
                            <GripVertical class="size-4" />
                        </span>
                    </div>
                    <p v-if="projectNames && (task.project_id == null || projectNames.has(task.project_id))"
                        class="break-words text-xs text-zinc-600 dark:text-zinc-300">
                        {{ task.project_id == null ? 'بدون پروژه' : projectNames.get(task.project_id) }}
                    </p>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span v-if="task.priority !== 'none'" class="rounded-full bg-surface-subtleLight px-2 py-1 dark:bg-surface-subtleDark">{{ task.priority_label }}</span>
                        <select :id="'board-status-' + task.uuid" :aria-label="`وضعیت ${task.title}`" :value="task.status" :disabled="saving.has(task.uuid)"
                            class="min-h-8 max-w-full rounded-lg border bg-transparent ps-2 pe-1 dark:bg-surface-cardDark"
                            @change="changeStatus($event, task)">
                            <option v-for="option in columns" :key="option.status" :value="option.status">{{ option.label }}</option>
                        </select>
                    </div>
                </li>
            </ul>
        </section>
    </div>
</template>
