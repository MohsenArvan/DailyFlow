<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import type { Project } from '@/types/project';
import { useProjectStore } from '@/stores/projects';
import BaseProgressBar from '@/components/ui/BaseProgressBar.vue';
import {
    Star,
    MoreVertical,
    Edit2,
    Trash2,
    Folder,
    Briefcase,
    BookOpen,
    Code,
    Target,
    Heart,
    Zap,
    CheckCircle2,
} from 'lucide-vue-next';

interface Props {
    project: Project;
}

const props = defineProps<Props>();
const router = useRouter();
const projectStore = useProjectStore();

const isMenuOpen = ref(false);
const menuRef = ref<HTMLDivElement | null>(null);

const iconComponent = computed(() => {
    switch (props.project.icon) {
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

const handleCardClick = () => {
    router.push(`/projects/${props.project.uuid}`);
};

const handleToggleFavorite = () => {
    projectStore.toggleFavorite(props.project.uuid);
};

const handleEdit = () => {
    isMenuOpen.value = false;
    projectStore.openEditModal(props.project);
};

const handleDelete = async () => {
    isMenuOpen.value = false;
    const shouldDeleteTasks = confirm('آیا مایلید تمام تسک‌های این پروژه هم حذف شوند؟\nدر صورت انتخاب Cancel، تسک‌ها به صندوق ورودی (Inbox) منتقل می‌شوند.');
    await projectStore.deleteProject(props.project.uuid, shouldDeleteTasks);
};

const handleClickOutside = (e: MouseEvent) => {
    if (menuRef.value && !menuRef.value.contains(e.target as Node)) {
        isMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div
        @click="handleCardClick"
        class="group relative rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark p-5 shadow-subtle hover:border-zinc-300 dark:hover:border-zinc-700 hover:shadow-card transition-all duration-150 cursor-pointer flex flex-col justify-between font-vazir select-none"
    >
        <!-- Top Row: Icon + Name + Star + Menu -->
        <div>
            <div class="flex items-start justify-between gap-3 mb-3">
                <!-- Project Icon & Title -->
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-xs"
                        :style="{ backgroundColor: `${project.color}18`, color: project.color }"
                    >
                        <component :is="iconComponent" class="w-5 h-5 stroke-[2]" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-50 truncate group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                            {{ project.name }}
                        </h3>
                        <p v-if="project.description" class="text-xs text-zinc-400 dark:text-zinc-500 truncate mt-0.5">
                            {{ project.description }}
                        </p>
                    </div>
                </div>

                <!-- Star & Menu Actions -->
                <div class="flex items-center gap-1 shrink-0" @click.stop>
                    <button
                        type="button"
                        @click="handleToggleFavorite"
                        class="p-1.5 rounded-xl transition-colors"
                        :class="project.is_favorite
                            ? 'text-amber-500 hover:text-amber-600 bg-amber-500/10'
                            : 'text-zinc-400 hover:text-amber-500 hover:bg-zinc-100 dark:hover:bg-zinc-800'"
                        :title="project.is_favorite ? 'حذف از علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی‌ها'"
                    >
                        <Star class="w-4 h-4" :class="{ 'fill-amber-500': project.is_favorite }" />
                    </button>

                    <div ref="menuRef" class="relative">
                        <button
                            type="button"
                            @click="isMenuOpen = !isMenuOpen"
                            class="p-1.5 rounded-xl text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                            title="گزینه‌ها"
                        >
                            <MoreVertical class="w-4 h-4" />
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-if="isMenuOpen"
                            class="absolute left-0 mt-1 w-36 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark shadow-card py-1 z-20 text-xs font-medium text-right animate-in fade-in zoom-in-95 duration-100"
                        >
                            <button
                                type="button"
                                @click="handleEdit"
                                class="w-full flex items-center gap-2 px-3 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                            >
                                <Edit2 class="w-3.5 h-3.5 text-zinc-400" />
                                <span>ویرایش پروژه</span>
                            </button>
                            <button
                                type="button"
                                @click="handleDelete"
                                class="w-full flex items-center gap-2 px-3 py-2 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors"
                            >
                                <Trash2 class="w-3.5 h-3.5 text-rose-500" />
                                <span>حذف پروژه</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Task Progress -->
        <div class="pt-4 border-t border-surface-borderLight/70 dark:border-surface-borderDark/70 mt-3">
            <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">
                    {{ project.completed_tasks_count }} از {{ project.tasks_count }} تسک تکمیل‌شده
                </span>
                <span class="text-[11px] font-bold" :style="{ color: project.color }">
                    {{ project.progress_percentage }}٪
                </span>
            </div>
            <div class="h-1.5 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
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
</template>
