<script setup lang="ts">
import { ref, nextTick, onMounted, onUnmounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppSidebar from '@/components/layout/AppSidebar.vue';
import AppHeader from '@/components/layout/AppHeader.vue';
import TaskDetailPanel from '@/components/task/TaskDetailPanel.vue';
import CommandPalette from '@/components/common/CommandPalette.vue';
import ProjectModal from '@/components/project/ProjectModal.vue';
import { useTaskReminders } from '@/composables/useTaskReminders';
import { useDialogFocus } from '@/composables/useDialogFocus';
import { X } from 'lucide-vue-next';
import { useTaskStore } from '@/stores/tasks';
import { useProjectStore } from '@/stores/projects';

const route = useRoute();
const palette = ref<InstanceType<typeof CommandPalette>>();
const router = useRouter();
useTaskReminders();
const taskStore = useTaskStore();
const projectStore = useProjectStore();

const isSidebarCollapsed = ref(localStorage.getItem('dailyflow_sidebar_collapsed') === 'true');
const isMobileDrawerOpen = ref(false);
const mobileDrawer = ref<HTMLElement>();
const trapDrawerFocus = useDialogFocus(mobileDrawer, () => isMobileDrawerOpen.value);

const currentViewTitle = computed(() => {
    return (route.meta?.title as string) || 'DailyFlow';
});

const toggleSidebarCollapse = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
    localStorage.setItem('dailyflow_sidebar_collapsed', isSidebarCollapsed.value ? 'true' : 'false');
};

const openMobileDrawer = () => {
    isMobileDrawerOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const closeMobileDrawer = () => {
    isMobileDrawerOpen.value = false;
    document.body.style.overflow = '';
};

const handleQuickAdd = async () => {
    const selector = 'input[aria-label="عنوان تسک جدید"]';
    if (!document.querySelector(selector) && route.name !== 'today') {
        await router.push({ name: 'today' });
        await nextTick();
    }
    document.querySelector<HTMLInputElement>(selector)?.focus();
};

const handleOpenSearch = () => {
    void palette.value?.open();
};

function handleDrawerKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape') {
        event.preventDefault();
        closeMobileDrawer();
        return;
    }
    trapDrawerFocus(event);
}

const handleKeydown = (event: KeyboardEvent) => {
    if (event.defaultPrevented || isMobileDrawerOpen.value || projectStore.isModalOpen || taskStore.isDetailsOpen
        || document.querySelector('dialog[open]')) return;

    if ((event.metaKey || event.ctrlKey) && event.code === 'KeyK') {
        event.preventDefault();
        handleOpenSearch();
    }
    const target = event.target as HTMLElement | null;
    if (!event.ctrlKey && !event.metaKey && !event.altKey && event.key === 'q'
        && !target?.closest('input, textarea, select, [contenteditable="true"]')) {
        event.preventDefault();
        void handleQuickAdd();
    }
};

onMounted(async () => {
    window.addEventListener('keydown', handleKeydown);
    await Promise.all([
        taskStore.fetchCounts(),
        projectStore.fetchProjects(),
    ]);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="min-h-screen flex bg-surface-light dark:bg-surface-dark text-zinc-900 dark:text-zinc-100 transition-colors font-vazir antialiased">
        <a href="#workspace-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-surface-cardLight focus:p-3 focus:text-zinc-900">
            رفتن به محتوای اصلی
        </a>
        <!-- Desktop Sidebar -->
        <div class="hidden md:block shrink-0 sticky top-0 h-screen z-40">
            <AppSidebar
                :collapsed="isSidebarCollapsed"
                @toggle-collapse="toggleSidebarCollapse"
            />
        </div>

        <!-- Mobile Drawer Backdrop & Slide-over -->
        <Teleport to="body">
            <div v-if="isMobileDrawerOpen" class="fixed inset-0 z-50 md:hidden font-vazir">
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm transition-opacity"
                    @click="closeMobileDrawer"
                ></div>

                <!-- Slide-over drawer -->
                <div ref="mobileDrawer" role="dialog" aria-modal="true" aria-label="منوی برنامه" tabindex="-1"
                    class="fixed inset-y-0 right-0 z-10 flex max-w-full shadow-2xl" @keydown="handleDrawerKeydown">
                    <button type="button" aria-label="بستن منو" class="absolute end-2 top-4 z-10 rounded-lg bg-surface-sidebarLight p-2 dark:bg-surface-sidebarDark" @click="closeMobileDrawer">
                        <X aria-hidden="true" class="size-5" />
                    </button>
                    <AppSidebar
                        :mobile="true"
                        @navigate="closeMobileDrawer"
                    />
                </div>
            </div>
        </Teleport>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Header -->
            <AppHeader
                :title="currentViewTitle"
                @toggle-mobile-menu="openMobileDrawer"
                @quick-add="handleQuickAdd"
                @open-search="handleOpenSearch"
            />

            <!-- Page Content -->
            <main id="workspace-content" tabindex="-1" class="mx-auto w-full max-w-[90rem] flex-1 px-4 py-6 sm:px-7 lg:px-10 lg:py-8">
                <slot />
            </main>
        </div>

        <!-- Task Details Dialog -->
        <TaskDetailPanel />

        <!-- Project Create/Edit Modal -->
        <ProjectModal />

        <CommandPalette ref="palette" />

    </div>
</template>
