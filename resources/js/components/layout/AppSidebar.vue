<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import {
    CheckCircle2,
    Inbox,
    Calendar,
    CalendarDays,
    AlertCircle,
    CheckSquare,
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

const navItems = [
    { name: 'صندوق ورودی', path: '/inbox', icon: Inbox, badge: null, color: 'text-zinc-600 dark:text-zinc-400' },
    { name: 'امروز', path: '/today', icon: Calendar, badge: null, color: 'text-brand-600 dark:text-brand-400' },
    { name: 'به‌زودی', path: '/upcoming', icon: CalendarDays, badge: null, color: 'text-violet-600 dark:text-violet-400' },
    { name: 'عقب‌افتاده', path: '/overdue', icon: AlertCircle, badge: null, badgeColor: 'bg-rose-500 text-white', color: 'text-rose-500' },
    { name: 'انجام‌شده', path: '/completed', icon: CheckSquare, badge: null, color: 'text-emerald-600 dark:text-emerald-400' },
];

const isActive = (path: string) => route.path === path;

const handleItemClick = () => {
    emit('navigate');
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
                        <span class="text-[10px] text-zinc-400 font-medium">جریان روزانه کارهای من</span>
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
            <div class="p-3 space-y-1">
                <router-link
                    v-for="item in navItems"
                    :key="item.path"
                    :to="item.path"
                    @click="handleItemClick"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                    :class="[
                        isActive(item.path)
                            ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-semibold shadow-xs'
                            : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50',
                        collapsed && !mobile ? 'justify-center px-0' : ''
                    ]"
                    :title="collapsed && !mobile ? item.name : undefined"
                >
                    <component :is="item.icon" class="w-4 h-4 shrink-0" :class="item.color" />
                    <span v-if="!collapsed || mobile" class="truncate flex-1 text-right">{{ item.name }}</span>
                    <span
                        v-if="item.badge && (!collapsed || mobile)"
                        class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                        :class="item.badgeColor || 'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300'"
                    >
                        {{ item.badge }}
                    </span>
                </router-link>
            </div>

            <!-- Projects Section -->
            <div class="px-3 pt-4 border-t border-surface-borderLight/70 dark:border-surface-borderDark/70 mt-2">
                <div v-if="!collapsed || mobile" class="flex items-center justify-between px-2 mb-2">
                    <span class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">پروژه‌ها</span>
                    <router-link
                        to="/projects"
                        @click="handleItemClick"
                        class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-200/50 dark:hover:bg-zinc-800 transition-colors"
                        title="مدیریت پروژه‌ها"
                    >
                        <Plus class="w-3.5 h-3.5" />
                    </router-link>
                </div>

                <router-link
                    to="/projects"
                    @click="handleItemClick"
                    class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50 transition-all"
                    :class="[
                        isActive('/projects') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-semibold' : '',
                        collapsed && !mobile ? 'justify-center px-0' : ''
                    ]"
                    :title="collapsed && !mobile ? 'پروژه‌ها' : undefined"
                >
                    <FolderKanban class="w-4 h-4 shrink-0 text-amber-500" />
                    <span v-if="!collapsed || mobile" class="truncate flex-1 text-right">لیست پروژه‌ها</span>
                </router-link>
            </div>
        </div>

        <!-- Bottom User / Settings Section -->
        <div class="p-3 border-t border-surface-borderLight dark:border-surface-borderDark space-y-1">
            <router-link
                to="/settings"
                @click="handleItemClick"
                class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-200/50 dark:hover:bg-zinc-800/50 transition-all"
                :class="[
                    isActive('/settings') ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400 font-semibold' : '',
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
                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all"
                :class="collapsed && !mobile ? 'justify-center px-0' : ''"
                :title="collapsed && !mobile ? 'خروج از حساب' : undefined"
            >
                <LogOut class="w-4 h-4 shrink-0" />
                <span v-if="!collapsed || mobile" class="truncate flex-1 text-right">خروج از حساب</span>
            </button>
        </div>
    </aside>
</template>
