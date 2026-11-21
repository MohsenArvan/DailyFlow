<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useProjectStore } from '@/stores/projects';
import AppLayout from '@/components/layout/AppLayout.vue';
import ProjectCard from '@/components/project/ProjectCard.vue';
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import { FolderKanban, Plus, Search, Star, Loader2 } from 'lucide-vue-next';

const projectStore = useProjectStore();
const searchQuery = ref('');

onMounted(async () => {
    await projectStore.fetchProjects();
});

const filteredProjects = computed(() => {
    if (!searchQuery.value.trim()) {
        return projectStore.projects;
    }
    const q = searchQuery.value.toLowerCase().trim();
    return projectStore.projects.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.description && p.description.toLowerCase().includes(q))
    );
});

const favoriteProjects = computed(() => {
    return filteredProjects.value.filter(p => p.is_favorite);
});

const standardProjects = computed(() => {
    return filteredProjects.value.filter(p => !p.is_favorite);
});

const handleOpenCreateModal = () => {
    projectStore.openCreateModal();
};
</script>

<template>
    <AppLayout>
        <!-- Header Row -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                    <FolderKanban class="w-5 h-5 text-amber-500" />
                    <span>پروژه‌های من</span>
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    سازمان‌دهی کارها بر اساس موضوعات، اهداف بلندمدت و حوزه‌های زندگی
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Search input -->
                <div v-if="projectStore.projects.length > 0" class="relative">
                    <Search class="w-4 h-4 text-zinc-400 absolute right-3 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="جست‌وجوی پروژه..."
                        class="pr-9 pl-3 py-1.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark text-xs text-zinc-800 dark:text-zinc-200 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-brand-500 transition-colors w-40 sm:w-52"
                    />
                </div>

                <BaseButton variant="primary" size="sm" @click="handleOpenCreateModal">
                    <template #prefix>
                        <Plus class="w-4 h-4 stroke-[2.5]" />
                    </template>
                    پروژه جدید
                </BaseButton>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="projectStore.isLoading" class="flex items-center justify-center p-16 text-zinc-400">
            <Loader2 class="w-8 h-8 animate-spin text-brand-500" />
        </div>

        <!-- Content -->
        <div v-else-if="projectStore.projects.length > 0" class="space-y-8">
            <!-- Favorites Section (if any) -->
            <div v-if="favoriteProjects.length > 0">
                <h3 class="text-xs font-bold text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5 mb-3">
                    <Star class="w-3.5 h-3.5 text-amber-500 fill-amber-500" />
                    <span>علاقه‌مندی‌ها</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <ProjectCard
                        v-for="project in favoriteProjects"
                        :key="project.uuid"
                        :project="project"
                    />
                </div>
            </div>

            <!-- All / Other Projects Section -->
            <div>
                <h3 v-if="favoriteProjects.length > 0" class="text-xs font-bold text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5 mb-3">
                    <FolderKanban class="w-3.5 h-3.5 text-zinc-400" />
                    <span>سایر پروژه‌ها</span>
                </h3>
                <div v-if="standardProjects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <ProjectCard
                        v-for="project in standardProjects"
                        :key="project.uuid"
                        :project="project"
                    />
                </div>
                <div v-else-if="favoriteProjects.length === 0" class="py-8 text-center text-xs text-zinc-400">
                    پروژه‌ای مطابق با عبارت جست‌وجو یافت نشد.
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark p-8 shadow-subtle min-h-[340px] flex items-center justify-center"
        >
            <BaseEmptyState
                title="هنوز پروژه‌ای تعریف نکرده‌اید"
                description="برای تفکیک کارهای کاری، تحصیلی و شخصی، می‌توانید پروژه‌های اختصاصی ایجاد کنید."
            >
                <template #icon>
                    <FolderKanban class="w-6 h-6 text-amber-500 stroke-[1.8]" />
                </template>
                <template #action>
                    <BaseButton variant="outline" size="sm" @click="handleOpenCreateModal">
                        <template #prefix>
                            <Plus class="w-3.5 h-3.5" />
                        </template>
                        ایجاد اولین پروژه
                    </BaseButton>
                </template>
            </BaseEmptyState>
        </div>
    </AppLayout>
</template>
