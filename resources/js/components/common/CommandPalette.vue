<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import { useTaskStore } from '@/stores/tasks';
import type { Task } from '@/types/task';

interface Results {
    tasks: { title: string; uuid: string }[];
    projects: { name: string; uuid: string }[];
}

const router = useRouter();
const tasks = useTaskStore();
const toast = useToast();
const auth = useAuthStore();
const dialog = ref<HTMLDialogElement>();
const input = ref<HTMLInputElement>();
const opened = ref(false);
const query = ref('');
const active = ref(0);
const results = ref<Results>({ tasks: [], projects: [] });
const status = ref('');
const routes = router.getRoutes().filter(route => route.meta.requiresAuth && !route.path.includes(':'));

const entries = computed(() => [
    ...routes.filter(route => String(route.meta.title).includes(query.value.trim())).map(route => ({
        id: route.path, group: 'صفحه', label: String(route.meta.title), run: () => router.push(route.path),
    })),
    ...results.value.projects.map(project => ({
        id: project.uuid, group: 'پروژه', label: project.name, run: () => router.push('/projects/' + project.uuid),
    })),
    ...results.value.tasks.map(task => ({
        id: task.uuid, group: 'تسک', label: task.title,
        run: async () => tasks.openDetails((await api.get<{ data: Task }>('/tasks/' + task.uuid)).data.data),
    })),
    ...(!query.value.trim() ? [
        { id: 'new', group: 'عملیات', label: 'افزودن تسک', run: async () => {
            await router.push('/today');
            await nextTick();
            document.querySelector<HTMLInputElement>('input[placeholder*="افزودن تسک"]')?.focus();
        } },
        { id: 'theme', group: 'عملیات', label: 'تغییر پوسته', run: () => auth.updatePreferences({
            theme: document.documentElement.classList.contains('dark') ? 'light' : 'dark',
        }) },
    ] : []),
]);

watch([query, opened], ([value, visible], _, onCleanup) => {
    results.value = { tasks: [], projects: [] };
    active.value = 0;
    status.value = '';
    if (!visible || !value.trim()) return;

    const controller = new AbortController();
    const timer = setTimeout(async () => {
        try {
            const response = await api.get<{ data: Results }>('/search', {
                params: { q: value.trim() }, signal: controller.signal,
            });
            if (!controller.signal.aborted) results.value = response.data.data;
        } catch {
            if (!controller.signal.aborted) status.value = 'جست‌وجو انجام نشد.';
        }
    }, 180);
    onCleanup(() => {
        clearTimeout(timer);
        controller.abort();
    });
});

async function open() {
    if (!dialog.value || dialog.value.open) return;
    query.value = '';
    opened.value = true;
    dialog.value.showModal();
    await nextTick();
    input.value?.focus();
}

function move(offset: number) {
    const size = entries.value.length;
    if (size) {
        active.value = (active.value + offset + size) % size;
        document.getElementById('command-option-' + active.value)?.scrollIntoView({ block: 'nearest' });
    }
}

async function select(index: number) {
    const entry = entries.value[index];
    if (!entry) return;
    try {
        dialog.value?.close();
        await entry.run();
    } catch {
        toast.error('اجرای فرمان ممکن نشد.');
    }
}

defineExpose({ open });
</script>

<template>
    <Teleport to="body">
        <dialog ref="dialog" aria-label="جست‌وجو و فرمان‌ها"
            class="m-auto w-[min(92vw,36rem)] rounded-2xl bg-surface-cardLight p-4 text-zinc-900 shadow-modal backdrop:bg-black/55 dark:bg-surface-cardDark dark:text-zinc-100"
            @close="opened = false">
            <input ref="input" v-model="query" maxlength="120" type="search" role="combobox" aria-label="جست‌وجوی تسک و پروژه"
                aria-controls="command-results" :aria-expanded="opened" aria-autocomplete="list"
                :aria-activedescendant="entries.length ? 'command-option-' + active : undefined"
                class="w-full rounded-lg border border-zinc-400 bg-transparent p-3"
                placeholder="جست‌وجوی تسک و پروژه"
                @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="select(active)" />
            <p v-if="status" role="status" class="mt-2 text-sm">{{ status }}</p>
            <ul id="command-results" role="listbox" aria-label="نتایج" class="mt-2 max-h-[55vh] overflow-y-auto">
                <li v-for="(entry, index) in entries" :id="'command-option-' + index" :key="entry.id" role="option"
                    :aria-selected="index === active" class="flex cursor-pointer justify-between gap-2 rounded-lg p-3 text-sm"
                    :class="index === active ? 'bg-brand-500/15' : ''" @mousedown.prevent @click="select(index)">
                    <span>{{ entry.label }}</span><span>{{ entry.group }}</span>
                </li>
            </ul>
            <button type="button" class="mt-2 rounded px-3 py-2 text-sm underline" @click="dialog?.close()">بستن</button>
        </dialog>
    </Teleport>
</template>
