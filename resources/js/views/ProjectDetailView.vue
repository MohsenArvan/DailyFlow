<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProjectStore } from '@/stores/projects';
import { useTaskStore } from '@/stores/tasks';
import api from '@/services/api';
import type { Task } from '@/types/task';
import TaskBoard from '@/components/task/TaskBoard.vue';
import ProjectNotesPanel from '@/components/project/ProjectNotesPanel.vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import TaskQuickAdd from '@/components/task/TaskQuickAdd.vue';
import TaskList from '@/components/task/TaskList.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import {
    ArrowRight,
    Columns3,
    List,
    Star,
    Edit2,
    Trash2,
    Loader2,
    Folder,
    Briefcase,
    BookOpen,
    Code,
    Target,
    Heart,
    Zap,
    CheckCircle2,
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const projectStore = useProjectStore();
const taskStore = useTaskStore();

const modeSaving = ref(false);
const loadError = ref(false);
const taskLoadError = ref(false);
let projectRequest = 0;
let taskRequest: AbortController | undefined;
let loadedProject: string | undefined;
const isLoadingTasks = ref(true);

const project = computed(() => projectStore.currentProject?.uuid === route.params.uuid ? projectStore.currentProject : null);

async function setView(mode: 'list' | 'board') {
    if (!project.value || modeSaving.value || mode === project.value.view_mode) return;
    modeSaving.value = true;
    try {
        await projectStore.updateProject(project.value.uuid, { view_mode: mode });
    } finally {
        modeSaving.value = false;
    }
}

const iconComponent = computed(() => {
    switch (project.value?.icon) {
        case 'briefcase': return Briefcase;
        case 'book': return BookOpen;
        case 'star': return Star;
        case 'code': return Code;
        case 'target': return Target;
        case 'heart': return Heart;
        case 'zap': return Zap;
        case 'check-circle': return CheckCircle2;
        default: return Folder;
    }
});

const tabs = [
    { id: 'tasks', label: 'تسک‌ها' },
    { id: 'notes', label: 'یادداشت‌ها' },
] as const;
type ProjectTab = (typeof tabs)[number]['id'];
const activeTab = computed<ProjectTab>(() => route.query.tab === 'notes' ? 'notes' : 'tasks');

async function selectTab(tab: ProjectTab) {
    if (tab === activeTab.value) return;
    await router.push({ query: { ...route.query, tab: tab === 'notes' ? 'notes' : undefined }, hash: route.hash });
}

function handleTabKey(event: KeyboardEvent, current: ProjectTab) {
    const other = current === 'tasks' ? 'notes' : 'tasks';
    const target = {
        ArrowLeft: other, ArrowRight: other, Home: 'tasks', End: 'notes',
    }[event.key] as ProjectTab | undefined;
    if (!target) return;
    event.preventDefault();
    document.getElementById(`project-tab-${target}`)?.focus();
}

async function loadProject() {
    const uuid = route.params.uuid as string;
    const request = ++projectRequest;
    loadError.value = false;
    const result = await projectStore.fetchProject(uuid);
    if (request === projectRequest) loadError.value = !result;
}

async function loadTasks() {
    taskRequest?.abort();
    if (!project.value || activeTab.value !== 'tasks') return;
    const { id, uuid } = project.value;
    const controller = new AbortController();
    taskRequest = controller;
    const isCurrent = () => !controller.signal.aborted && route.params.uuid === uuid && activeTab.value === 'tasks';

    isLoadingTasks.value = loadedProject !== uuid;
    taskLoadError.value = false;
    try {
        const response = await api.get<{ data: Task[] }>('/tasks', {
            params: { project_id: id },
            signal: controller.signal,
        });
        if (!isCurrent()) return;
        taskStore.tasks = response.data.data;
        loadedProject = uuid;
    } catch {
        if (isCurrent()) taskLoadError.value = true;
    } finally {
        if (isCurrent()) isLoadingTasks.value = false;
    }
}

watch([() => route.params.uuid, () => taskStore.revision], loadProject, { immediate: true });
watch([() => project.value?.id, activeTab, () => taskStore.revision], (_, __, onCleanup) => {
    if (!project.value || activeTab.value !== 'tasks') loadedProject = undefined;
    void loadTasks();
    onCleanup(() => taskRequest?.abort());
}, { immediate: true });

const handleToggleFavorite = () => {
    if (project.value) {
        projectStore.toggleFavorite(project.value.uuid);
    }
};

const handleEdit = () => {
    if (project.value) {
        projectStore.openEditModal(project.value);
    }
};

const handleDelete = async () => {
    if (!project.value) return;
    const shouldDeleteTasks = confirm('آیا مایلید تمام تسک‌های این پروژه هم حذف شوند؟\nدر صورت انصراف، تسک‌ها به صندوق ورودی (Inbox) منتقل می‌شوند.');
    const ok = await projectStore.deleteProject(project.value.uuid, shouldDeleteTasks);
    if (ok) {
        router.push('/projects');
    }
};
</script>

<template>
    <AppLayout>
        <!-- Loading State -->
        <p v-if="loadError && !project" role="alert" class="workspace-panel p-5">
            دریافت پروژه ممکن نشد.
            <button type="button" class="font-semibold underline" @click="loadProject">تلاش دوباره</button>
        </p>
        <div v-else-if="projectStore.isLoading && !project" class="flex items-center justify-center p-20 text-zinc-400">
            <Loader2 class="w-8 h-8 animate-spin text-brand-500" />
        </div>

        <div v-else-if="project" class="space-y-6">
            <!-- Back Navigation & Top Bar -->
            <div class="flex items-center justify-between gap-4">
                <button
                    type="button"
                    @click="router.push('/projects')"
                    class="inline-flex items-center gap-1.5 text-xs text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition-colors"
                >
                    <ArrowRight class="w-4 h-4" />
                    <span>بازگشت به پروژه‌ها</span>
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="handleToggleFavorite"
                        class="p-2 rounded-xl transition-colors"
                        :class="project.is_favorite
                            ? 'text-amber-500 bg-amber-500/10'
                            : 'text-zinc-400 hover:text-amber-500 hover:bg-zinc-100 dark:hover:bg-zinc-800'"
                        :title="project.is_favorite ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها'"
                    >
                        <Star class="w-4 h-4" :class="{ 'fill-amber-500': project.is_favorite }" />
                    </button>

                    <button
                        type="button"
                        @click="handleEdit"
                        class="p-2 rounded-xl text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                        title="ویرایش پروژه"
                    >
                        <Edit2 class="w-4 h-4" />
                    </button>

                    <button
                        type="button"
                        @click="handleDelete"
                        class="p-2 rounded-xl text-zinc-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors"
                        title="حذف پروژه"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Project Banner & Progress -->
            <div class="workspace-panel p-5 sm:p-7">
                <div class="flex items-start gap-4 mb-4">
                    <div
                        class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-sm"
                        :style="{ backgroundColor: `${project.color}18`, color: project.color }"
                    >
                        <component :is="iconComponent" class="w-6 h-6 stroke-[2]" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                            {{ project.name }}
                        </h2>
                        <p v-if="project.description" class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 leading-relaxed">
                            {{ project.description }}
                        </p>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="pt-3 border-t border-surface-borderLight dark:border-surface-borderDark">
                    <div class="flex justify-between items-center text-xs mb-1.5">
                        <span class="font-medium text-zinc-600 dark:text-zinc-400">پیشرفت کل پروژه</span>
                        <span class="font-bold tabular-nums text-zinc-800 dark:text-zinc-100">
                            {{ project.completed_tasks_count }} از {{ project.tasks_count }} تسک ({{ project.progress_percentage }}٪)
                        </span>
                    </div>
                    <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-300"
                            :style="{
                                width: `${project.progress_percentage}%`,
                                backgroundColor: project.color
                            }"
                        ></div>
                    </div>
                </div>
            </div>

            <div role="tablist" aria-label="بخش‌های پروژه" class="flex gap-4 border-b border-surface-borderLight dark:border-surface-borderDark">
                <button
                    v-for="tab in tabs"
                    :id="'project-tab-' + tab.id"
                    :key="tab.id"
                    type="button"
                    role="tab"
                    :aria-controls="'project-panel-' + tab.id"
                    :aria-selected="activeTab === tab.id"
                    :tabindex="activeTab === tab.id ? 0 : -1"
                    class="min-h-11 border-b-2 px-3 text-sm font-bold focus-visible:outline focus-visible:outline-brand-500"
                    :class="activeTab === tab.id
                        ? 'border-brand-600 text-brand-700 dark:border-brand-400 dark:text-brand-300'
                        : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100'"
                    @click="selectTab(tab.id)"
                    @keydown="handleTabKey($event, tab.id)"
                >{{ tab.label }}</button>
            </div>

            <section
                v-show="activeTab === 'tasks'"
                id="project-panel-tasks"
                role="tabpanel"
                aria-labelledby="project-tab-tasks"
                class="space-y-4"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-lg font-bold">تسک‌های پروژه</h3>
                    <div aria-label="نمای پروژه" role="group" class="segmented-control">
                        <button type="button" :aria-pressed="project.view_mode === 'list'" :disabled="modeSaving" @click="setView('list')">
                            <List aria-hidden="true" class="size-4" /> لیست
                        </button>
                        <button type="button" :aria-pressed="project.view_mode === 'board'" :disabled="modeSaving" @click="setView('board')">
                            <Columns3 aria-hidden="true" class="size-4" /> کانبان
                        </button>
                    </div>
                </div>
                <!-- Project Scoped Quick Add -->
                <div>
                    <TaskQuickAdd :project-id="project.id" />
                </div>

                <!-- Tasks Loading or List -->
                <div v-if="isLoadingTasks" class="flex items-center justify-center p-12 text-zinc-400">
                    <Loader2 class="w-7 h-7 animate-spin text-brand-500" />
                </div>

                <p v-else-if="taskLoadError" role="alert" class="workspace-panel p-5">
                    دریافت تسک‌ها ممکن نشد.
                    <button type="button" class="font-semibold underline" @click="loadTasks">تلاش دوباره</button>
                </p>
                <TaskBoard v-else-if="project.view_mode === 'board'" :key="project.uuid" :tasks="taskStore.tasks" />
                <TaskList
                    v-else
                    :tasks="taskStore.tasks"
                    empty-title="هنوز تسکی در این پروژه ثبت نشده است"
                    empty-description="با استفاده از فرم ثبت سریع بالا، اولین کار این پروژه را اضافه کنید."
                />
            </section>
            <section
                v-show="activeTab === 'notes'"
                id="project-panel-notes"
                role="tabpanel"
                aria-labelledby="project-tab-notes"
            >
                <ProjectNotesPanel v-if="activeTab === 'notes'" :key="project.uuid" :project-uuid="project.uuid" />
            </section>
        </div>
    </AppLayout>
</template>
