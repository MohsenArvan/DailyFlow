<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import AppSidebar from '@/components/layout/AppSidebar.vue';
import AppHeader from '@/components/layout/AppHeader.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import TaskDetailPanel from '@/components/task/TaskDetailPanel.vue';
import { useToast } from '@/composables/useToast';
import { useTaskStore } from '@/stores/tasks';

const route = useRoute();
const toast = useToast();
const taskStore = useTaskStore();

const isSidebarCollapsed = ref(localStorage.getItem('dailyflow_sidebar_collapsed') === 'true');
const isMobileDrawerOpen = ref(false);

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

const handleQuickAdd = () => {
    const input = document.querySelector('input[placeholder*="افزودن تسک"]') as HTMLInputElement;
    if (input) {
        input.focus();
    } else {
        toast.info('از فرم سریع افزودن تسک در بالای صفحه استفاده کنید.');
    }
};

const handleOpenSearch = () => {
    toast.info('بخش جست‌وجو و فیلترها در فاز بعدی فعال خواهد شد.');
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isMobileDrawerOpen.value) {
        closeMobileDrawer();
    }
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        handleOpenSearch();
    }
    // 'q' key focuses quick add if not already typing in an input
    if (e.key === 'q' && !['INPUT', 'TEXTAREA'].includes((e.target as HTMLElement).tagName)) {
        e.preventDefault();
        handleQuickAdd();
    }
};

onMounted(async () => {
    window.addEventListener('keydown', handleKeydown);
    await taskStore.fetchCounts();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="min-h-screen flex bg-surface-light dark:bg-surface-dark text-zinc-900 dark:text-zinc-100 transition-colors font-vazir antialiased">
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
                <div class="fixed inset-y-0 right-0 max-w-full flex z-10 shadow-2xl animate-in slide-in-from-right duration-200">
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
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-5xl w-full mx-auto">
                <slot />
            </main>
        </div>

        <!-- Task Details Slide-over Panel -->
        <TaskDetailPanel />

        <!-- Global Toast Container -->
        <ToastContainer />
    </div>
</template>
