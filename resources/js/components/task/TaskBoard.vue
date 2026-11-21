<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import { Circle, CircleCheck, Folder, GripVertical, Timer } from 'lucide-vue-next';
import { useTaskStore } from '@/stores/tasks';
import type { Task, TaskStatus } from '@/types/task';

const props = defineProps<{ tasks: Task[]; projectNames?: ReadonlyMap<number, string> }>();
const store = useTaskStore();
const columns = [
    { status: 'pending', label: 'در انتظار', icon: Circle },
    { status: 'in_progress', label: 'در حال انجام', icon: Timer },
    { status: 'completed', label: 'انجام‌شده', icon: CircleCheck },
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
            :data-status="column.status"
            class="kanban-lane min-w-0 overflow-hidden rounded-[1.35rem] border lg:min-h-64"
            :class="{ 'ring-2 ring-inset ring-brand-500/50': dragged && grouped[column.status].every(task => task.uuid !== dragged) }"
            @dragenter.prevent
            @dragover.prevent
            @drop.prevent="drop(column.status)">
            <header class="kanban-heading flex items-center gap-3 px-4 py-3.5">
                <span class="kanban-symbol grid size-9 shrink-0 place-items-center rounded-xl">
                    <component :is="column.icon" class="size-[18px]" aria-hidden="true" />
                </span>
                <h4 class="flex-1 text-sm font-extrabold">{{ column.label }}</h4>
                <span class="kanban-symbol min-w-7 rounded-full px-2 py-1 text-center text-xs font-bold tabular-nums">{{ grouped[column.status].length }}</span>
            </header>
            <p v-if="!grouped[column.status].length" class="kanban-empty m-3 rounded-xl border border-dashed px-3 py-5 text-center text-sm">تسکی در این ستون نیست</p>
            <ul v-else class="space-y-2.5 p-3">
                <li v-for="task in grouped[column.status]" :key="task.uuid" class="kanban-card workspace-panel cursor-grab select-none p-3.5 active:cursor-grabbing"
                    :draggable="!saving.has(task.uuid)" @dragstart="startDrag($event, task)" @dragend="dragged = undefined">
                    <div class="flex items-start gap-2">
                        <button type="button" class="min-w-0 flex-1 break-words text-start text-sm font-bold leading-6 hover:underline" @click="store.openDetails(task)">{{ task.title }}</button>
                        <span title="کشیدن تسک" aria-hidden="true"
                            class="inline-flex size-7 shrink-0 cursor-grab select-none items-center justify-center rounded-md text-zinc-600 hover:bg-surface-subtleLight active:cursor-grabbing dark:text-zinc-400 dark:hover:bg-surface-subtleDark">
                            <GripVertical class="pointer-events-none size-4" />
                        </span>
                    </div>
                    <p v-if="projectNames && (task.project_id == null || projectNames.has(task.project_id))"
                        class="mt-1 flex items-center gap-1.5 break-words text-xs text-zinc-600 dark:text-zinc-300">
                        <Folder v-if="task.project_id != null" aria-hidden="true" class="size-3.5 shrink-0" />
                        {{ task.project_id == null ? 'بدون پروژه' : projectNames.get(task.project_id) }}
                    </p>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t pt-2.5 text-xs">
                        <span v-if="task.priority !== 'none'" :data-priority="task.priority" class="kanban-priority rounded-full px-2.5 py-1 font-semibold">{{ task.priority_label }}</span>
                        <select :id="'board-status-' + task.uuid" :aria-label="`وضعیت ${task.title}`" :value="task.status" :disabled="saving.has(task.uuid)"
                            class="kanban-status min-h-8 max-w-full rounded-lg border px-2 font-semibold"
                            @change="changeStatus($event, task)">
                            <option v-for="option in columns" :key="option.status" :value="option.status">{{ option.label }}</option>
                        </select>
                    </div>
                </li>
            </ul>
        </section>
    </div>
</template>


<style scoped>
.kanban-lane {
    --lane: #6242b4;
    --ink: #49298e;
    --tint: #f3edff;
    --surface: #faf7ff;
    --edge: #ded0f4;
    background: var(--surface);
    border-color: var(--edge);
}

.kanban-lane[data-status="in_progress"] {
    --lane: #b76413;
    --ink: #804308;
    --tint: #fff0d7;
    --surface: #fffbf3;
    --edge: #eddbbc;
}

.kanban-lane[data-status="completed"] {
    --lane: #187754;
    --ink: #0d5d40;
    --tint: #dff8eb;
    --surface: #f3fcf7;
    --edge: #c8e9d7;
}

:global(.dark .kanban-lane) {
    --lane: #bda3ff;
    --ink: #dfd1ff;
    --tint: #372750;
    --surface: #231c34;
    --edge: #453459;
}

:global(.dark .kanban-lane[data-status="in_progress"]) {
    --lane: #ffc678;
    --ink: #ffdcab;
    --tint: #49341e;
    --surface: #2e251c;
    --edge: #56412b;
}

:global(.dark .kanban-lane[data-status="completed"]) {
    --lane: #78d4ab;
    --ink: #acf0cf;
    --tint: #214438;
    --surface: #1a2d29;
    --edge: #315448;
}

.kanban-heading {
    color: var(--ink);
    border-block-end: 1px solid var(--edge);
    box-shadow: inset 0 3px var(--lane);
    background: linear-gradient(120deg, var(--tint), var(--surface));
}

.kanban-symbol, .kanban-status {
    color: var(--ink);
    background: var(--tint);
}

.kanban-status, .kanban-empty, .kanban-card {
    border-color: var(--edge);
}

.kanban-empty {
    color: var(--ink);
}

.kanban-card {
    box-shadow: 0 2px 8px rgb(25 20 43 / 6%);
}

.kanban-priority {
    @apply bg-surface-subtleLight text-zinc-700 dark:bg-surface-subtleDark dark:text-zinc-200;
}

.kanban-priority[data-priority="urgent"] {
    @apply bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200;
}

.kanban-priority[data-priority="high"] {
    @apply bg-orange-100 text-orange-900 dark:bg-orange-950 dark:text-orange-200;
}
</style>
