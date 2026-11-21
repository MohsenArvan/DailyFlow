<script setup lang="ts">
import { computed, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useTaskStore } from '@/stores/tasks';
import { useProjectStore } from '@/stores/projects';
import {
    CheckCircle2,
    ChartNoAxesColumnIncreasing,
    Inbox,
    Calendar,
    CalendarDays,
    AlertCircle,
    CheckSquare,
    Columns3,
    FolderKanban,
    Settings,
    ChevronLeft,
    ChevronRight,
    LogOut,
    Plus,
} from 'lucide-vue-next';

interface Props {
    collapsed?: boolean;
    mobile?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    collapsed: false,
    mobile: false,
});

const emit = defineEmits<{
    (e: 'toggleCollapse'): void;
    (e: 'navigate'): void;
}>();

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const taskStore = useTaskStore();
const projectStore = useProjectStore();

const navItems = computed(() => [
    {
        name: 'داشبورد',
        path: '/dashboard',
        icon: ChartNoAxesColumnIncreasing,
        badge: null,
        color: 'text-brand-600 dark:text-brand-400',
    },
    {
        name: 'کانبان همهٔ تسک‌ها',
        path: '/kanban',
        icon: Columns3,
        badge: null,
        color: 'text-indigo-700 dark:text-indigo-300',
    },
    {
        name: 'صندوق ورودی',
        path: '/inbox',
        icon: Inbox,
        badge: taskStore.counts.inbox > 0 ? taskStore.counts.inbox : null,
        color: 'text-zinc-600 dark:text-zinc-400',
    },
    {
        name: 'امروز',
        path: '/today',
        icon: Calendar,
        badge: taskStore.counts.today > 0 ? taskStore.counts.today : null,
        badgeColor: 'bg-brand-500/10 text-brand-700 dark:text-brand-400',
        color: 'text-brand-600 dark:text-brand-400',
    },
    {
        name: 'به‌زودی',
        path: '/upcoming',
        icon: CalendarDays,
        badge: taskStore.counts.upcoming > 0 ? taskStore.counts.upcoming : null,
        color: 'text-violet-600 dark:text-violet-400',
    },
    {
        name: 'عقب‌افتاده',
        path: '/overdue',
        icon: AlertCircle,
        badge: taskStore.counts.overdue > 0 ? taskStore.counts.overdue : null,
        badgeColor: 'bg-rose-500 text-white',
        color: 'text-rose-500',
    },
    {
        name: 'انجام‌شده',
        path: '/completed',
        icon: CheckSquare,
        badge: null,
        color: 'text-emerald-600 dark:text-emerald-400',
    },
]);

const isActive = (path: string) => route.path === path;

const handleItemClick = () => {
    emit('navigate');
};

const handleCreateProject = async () => {
    emit('navigate');
    await nextTick();
    projectStore.openCreateModal();
};

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>

<template>
    <aside
        class="h-full flex flex-col justify-between bg-surface-sidebarLight dark:bg-surface-sidebarDark border-l border-surface-borderLight dark:border-surface-borderDark transition-all duration-300 font-vazir select-none"
        :class="[
            mobile ? 'w-72' : (collapsed ? 'w-18' : 'w-64'),
        ]"
    >
        <!-- Top Section -->
        <div class="flex flex-col flex-1 overflow-y-auto no-scrollbar">
            <!-- App Brand Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-surface-borderLight dark:border-surface-borderDark">
                <router-link
                    to="/today"
                    class="flex items-center gap-2.5 overflow-hidden group"
                    @click="handleItemClick"
                >
                    <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white shadow-md shadow-brand-600/30 group-hover:scale-105 transition-transform shrink-0">
                        <CheckCircle2 class="w-5 h-5 stroke-[2.2]" />
                    </div>
                    <div v-if="!collapsed || mobile" class="flex flex-col truncate">
                        <span class="text-base font-bold tracking-tight text-zinc-900 dark:text-zinc-50 leading-tight">DailyFlow</span>
                        <span class="text-[10px] text-zinc-600 dark:text-zinc-400 font-medium">جریان روزانه کارهای من</span>
                    </div>
                </router-link>

                <!-- Collapse Toggle (Desktop only) -->
                <button
                    v-if="!mobile"
                    @click="emit('toggleCollapse')"
                    class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-800 transition-colors"
                    :title="collapsed ? 'باز کردن نوار کناری' : 'جمع کردن نوار کناری'"
                >
                    <ChevronRight v-if="collapsed" class="w-4 h-4" />
                    <ChevronLeft v-else class="w-4 h-4" />
                </button>
            </div>

            <!-- Main Navigation Links -->
            <nav aria-label="ناوبری اصلی" class="px-3 py-4 space-y-1">
                <router-link
                    v-for="item in navItems"
                    :key="item.path"
                    :to="item.path"
                    @click="handleItemClick"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors group relative"
                    :class="[
                        isActive(item.path)
                            ? 'bg-brand-500/10 text-brand-700 dark:text-brand-400 font-semibold shadow-xs'
                            : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50',
                        collapsed && !mobile ? 'justify-center px-0' : ''
                    ]"
                    :title="collapsed && !mobile ? item.name : undefined"
                >
                    <component :is="item.icon" class="w-4 h-4 shrink-0" :class="item.color" />
                    <span v-if="!collapsed || mobile" class="truncate flex-1 text-right">{{ item.name }}</span>
                    <span
                        v-if="item.badge && (!collapsed || mobile)"
                        class="text-[10px] px-2 py-0.5 rounded-full font-bold leading-none shrink-0"
                        :class="item.badgeColor || 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300'"
                    >
                        {{ item.badge }}
                    </span>
                </router-link>
            </nav>

            <!-- Projects Section -->
            <div class="px-3 pt-4 border-t border-surface-borderLight/70 dark:border-surface-borderDark/70 mt-2">
                <div v-if="!collapsed || mobile" class="flex items-center justify-between px-2 mb-2">
                    <span class="text-[11px] font-bold text-zinc-600 dark:text-zinc-400 uppercase tracking-wider">پروژه‌ها</span>
                    <button
                        type="button"
                        @click="handleCreateProject"
                        class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-800 transition-colors"
                        title="افزودن پروژه جدید" aria-label="افزودن پروژه جدید"
                    >
                        <Plus class="w-3.5 h-3.5" />
                    </button>
                </div>

                <!-- Projects list -->
                <div class="space-y-0.5">
                    <router-link
                        v-for="proj in projectStore.projects"
                        :key="proj.uuid"
                        :to="`/projects/${proj.uuid}`"
                        @click="handleItemClick"
                        class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-sm font-medium transition-colors group relative"
                        :class="[
                            isActive(`/projects/${proj.uuid}`)
                                ? 'bg-brand-500/10 text-brand-700 dark:text-brand-400 font-semibold shadow-xs'
                                : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50',
                            collapsed && !mobile ? 'justify-center px-0' : ''
                        ]"
                        :title="collapsed && !mobile ? proj.name : undefined"
                    >
                        <span
                            class="w-2.5 h-2.5 rounded-full shrink-0 shadow-xs transition-transform group-hover:scale-125"
                            :style="{ backgroundColor: proj.color }"
                        ></span>
                        <span v-if="!collapsed || mobile" class="truncate flex-1 text-right">{{ proj.name }}</span>
                        <span
                            v-if="proj.pending_tasks_count > 0 && (!collapsed || mobile)"
                            class="text-[10px] px-1.5 py-0.2 rounded-full font-bold bg-zinc-200/70 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 leading-none shrink-0"
                        >
                            {{ proj.pending_tasks_count }}
                        </span>
                    </router-link>

                    <!-- Link to All Projects -->
                    <router-link
                        to="/projects"
                        @click="handleItemClick"
                        class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-200/40 dark:hover:bg-zinc-800/40 transition-all mt-1"
                        :class="[
                            isActive('/projects') ? 'text-brand-600 dark:text-brand-400 font-semibold' : '',
                            collapsed && !mobile ? 'justify-center px-0' : ''
                        ]"
                        :title="collapsed && !mobile ? 'همه پروژه‌ها' : undefined"
                    >
                        <FolderKanban class="w-3.5 h-3.5 shrink-0" />
                        <span v-if="!collapsed || mobile" class="truncate flex-1 text-right">همه پروژه‌ها</span>
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Bottom User / Settings Section -->
        <div class="p-3 border-t border-surface-borderLight dark:border-surface-borderDark space-y-1">
            <router-link
                to="/settings"
                @click="handleItemClick"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50 transition-all"
                :class="[
                    isActive('/settings') ? 'bg-brand-500/10 text-brand-700 dark:text-brand-400 font-semibold' : '',
                    collapsed && !mobile ? 'justify-center px-0' : ''
                ]"
                :title="collapsed && !mobile ? 'تنظیمات' : undefined"
            >
                <Settings class="w-4 h-4 shrink-0 text-zinc-500" />
                <span v-if="!collapsed || mobile" class="truncate flex-1 text-right">تنظیمات و پروفایل</span>
            </router-link>

            <button
                type="button"
                @click="handleLogout"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all"
                :class="collapsed && !mobile ? 'justify-center px-0' : ''"
                :title="collapsed && !mobile ? 'خروج از حساب' : undefined"
            >
                <LogOut class="w-4 h-4 shrink-0" />
                <span v-if="!collapsed || mobile" class="truncate flex-1 text-right">خروج از حساب</span>
            </button>
        </div>
    </aside>
</template>
