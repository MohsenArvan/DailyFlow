<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Columns3 } from 'lucide-vue-next';
import AppLayout from '@/components/layout/AppLayout.vue';
import TaskBoard from '@/components/task/TaskBoard.vue';
import TaskQuickAdd from '@/components/task/TaskQuickAdd.vue';
import { useProjectStore } from '@/stores/projects';
import { useTaskStore } from '@/stores/tasks';

const tasks = useTaskStore();
const projects = useProjectStore();
const projectNames = computed(() => new Map(projects.projects.map(project => [project.id, project.name])));
const ready = ref(false);
const error = ref(false);
const controller = new AbortController();
let request = 0;

async function load() {
    const current = ++request;
    error.value = false;
    const loaded = await tasks.fetchTasks('all', undefined, controller.signal);
    if (current !== request || controller.signal.aborted) return;
    if (loaded) ready.value = true;
    else error.value = true;
}

onMounted(load);
watch(() => tasks.revision, load);
onBeforeUnmount(() => controller.abort());
</script>

<template>
    <AppLayout>
        <div class="page-heading">
            <h2 class="flex items-center gap-2 text-2xl font-bold">
                <Columns3 aria-hidden="true" class="size-6 text-brand-700 dark:text-brand-300" />
                همهٔ تسک‌ها
            </h2>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">تسک‌های پروژه‌ها و کارهای بدون پروژه در یک تابلو</p>
        </div>

        <p v-if="error" role="alert" class="workspace-panel mb-5 p-4">
            دریافت تسک‌ها ممکن نشد.
            <button type="button" class="ms-2 font-semibold underline" @click="load">تلاش دوباره</button>
        </p>
        <p v-if="!ready && !error" role="status" class="workspace-panel p-5">در حال دریافت تسک‌ها…</p>
        <div v-if="ready" class="space-y-5">
            <TaskQuickAdd />
            <TaskBoard :tasks="tasks.tasks" :project-names="projectNames" />
        </div>
    </AppLayout>
</template>
