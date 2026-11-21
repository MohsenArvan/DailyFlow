<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useProjectStore } from '@/stores/projects';
import { useTaskStore } from '@/stores/tasks';
import api from '@/services/api';
import type { Task } from '@/types/task';
import AppLayout from '@/components/layout/AppLayout.vue';
import TaskQuickAdd from '@/components/task/TaskQuickAdd.vue';
import TaskList from '@/components/task/TaskList.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import {
    ArrowRight,
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

const projectTasks = ref<Task[]>([]);
const isLoadingTasks = ref(false);

const project = computed(() => projectStore.currentProject);

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

const loadProjectData = async () => {
    const uuid = route.params.uuid as string;
    if (!uuid) return;

    const p = await projectStore.fetchProject(uuid);
    if (!p) {
        router.push('/projects');
        return;
    }

    isLoadingTasks.value = true;
    try {
        const response = await api.get<{ data: Task[] }>('/tasks', {
            params: { project_id: p.id },
        });
        projectTasks.value = response.data.data;
        // Also keep taskStore tasks aligned for detail panel and actions
        taskStore.tasks = response.data.data;
    } catch (err) {
        console.error('Failed to load project tasks:', err);
    } finally {
        isLoadingTasks.value = false;
    }
};

watch(
    () => [route.params.uuid, taskStore.revision],
    () => {
        loadProjectData();
    }
);

onMounted(() => {
    loadProjectData();
});

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
        <div v-if="projectStore.isLoading && !project" class="flex items-center justify-center p-20 text-zinc-400">
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
            <div class="p-6 rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark shadow-card">
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
                        <span class="font-bold" :style="{ color: project.color }">
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

            <!-- Project Scoped Quick Add -->
            <div>
                <TaskQuickAdd :project-id="project.id" />
            </div>

            <!-- Tasks Loading or List -->
            <div v-if="isLoadingTasks" class="flex items-center justify-center p-12 text-zinc-400">
                <Loader2 class="w-7 h-7 animate-spin text-brand-500" />
            </div>

            <TaskList
                v-else
                :tasks="taskStore.tasks"
                empty-title="هنوز تسکی در این پروژه ثبت نشده است"
                empty-description="با استفاده از فرم ثبت سریع بالا، اولین کار این پروژه را اضافه کنید."
            />
        </div>
    </AppLayout>
</template>
