<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import {
    Menu,
    Plus,
    Search,
    Sun,
    Moon,
    Settings,
    LogOut,
} from 'lucide-vue-next';
import BaseDropdown from '@/components/ui/BaseDropdown.vue';

interface Props {
    title?: string;
}

defineProps<Props>();

const emit = defineEmits<{
    (e: 'toggleMobileMenu'): void;
    (e: 'quickAdd'): void;
    (e: 'openSearch'): void;
}>();

const router = useRouter();
const authStore = useAuthStore();

const toggleTheme = () => {
    const isDark = document.documentElement.classList.contains('dark');
    authStore.updatePreferences({ theme: isDark ? 'light' : 'dark' });
};

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};

const userInitial = computed(() => {
    return authStore.user?.name ? authStore.user.name.charAt(0) : 'U';
});
</script>

<template>
    <header class="h-16 px-4 sm:px-6 flex items-center justify-between border-b border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight/80 dark:bg-surface-cardDark/80 backdrop-blur-md sticky top-0 z-30 transition-colors font-vazir select-none">
        <!-- Left: Mobile Menu Trigger & Title -->
        <div class="flex items-center gap-3">
            <!-- Mobile Menu Toggle Button -->
            <button
                type="button"
                @click="emit('toggleMobileMenu')"
                class="md:hidden p-2 rounded-xl text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500"
                aria-label="منو"
            >
                <Menu class="w-5 h-5" />
            </button>

            <!-- Page Title -->
            <h1 class="text-base sm:text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-50 truncate">
                {{ title || 'DailyFlow' }}
            </h1>
        </div>

        <!-- Right: Actions & Profile -->
        <div class="flex items-center gap-2 sm:gap-2.5">
            <!-- Quick Add Task Button -->
            <button
                type="button"
                @click="emit('quickAdd')"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold shadow-xs shadow-brand-600/30 transition-all active:scale-95 focus:outline-none focus:ring-2 focus:ring-brand-500"
                title="افزودن تسک جدید (Q)"
            >
                <Plus class="w-4 h-4 stroke-[2.5]" />
                <span class="hidden sm:inline">تسک جدید</span>
            </button>

            <!-- Search Button -->
            <button
                type="button"
                @click="emit('openSearch')"
                class="flex items-center gap-2 px-2.5 sm:px-3 py-1.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark hover:bg-zinc-200/60 dark:hover:bg-zinc-800/80 text-zinc-500 dark:text-zinc-400 text-xs transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500"
                title="جست‌وجو (Ctrl+K)"
            >
                <Search class="w-3.5 h-3.5" />
                <span class="hidden lg:inline text-zinc-600 dark:text-zinc-300">جست‌وجو...</span>
                <kbd class="hidden lg:inline text-[10px] font-mono px-1.5 py-0.5 rounded bg-zinc-200/70 dark:bg-zinc-700/60 text-zinc-700 dark:text-zinc-300">⌘K</kbd>
            </button>

            <!-- Theme Toggle -->
            <button
                type="button"
                @click="toggleTheme"
                class="p-2 rounded-xl text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500"
                title="تغییر تم"
                aria-label="تغییر تم"
            >
                <Sun class="w-4 h-4 hidden dark:block" />
                <Moon class="w-4 h-4 block dark:hidden" />
            </button>

            <!-- User Dropdown Menu -->
            <BaseDropdown align="left" width-class="w-52">
                <template #trigger="{ isOpen }">
                    <button
                        type="button"
                        class="flex items-center gap-2 p-1 rounded-xl hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500"
                        :aria-expanded="isOpen"
                    >
                        <div class="w-7 h-7 rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center text-xs font-bold font-vazir">
                            {{ userInitial }}
                        </div>
                    </button>
                </template>

                <template #default="{ close }">
                    <div class="px-3.5 py-2.5 border-b border-surface-borderLight dark:border-surface-borderDark">
                        <p class="font-semibold text-zinc-800 dark:text-zinc-100 truncate text-xs">{{ authStore.user?.name }}</p>
                        <p class="text-[11px] text-zinc-400 truncate">{{ authStore.user?.email }}</p>
                    </div>

                    <router-link
                        to="/settings"
                        @click="close"
                        class="flex items-center gap-2 px-3.5 py-2 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors text-xs"
                    >
                        <Settings class="w-3.5 h-3.5 text-zinc-400" />
                        <span>تنظیمات و پروفایل</span>
                    </router-link>

                    <button
                        type="button"
                        @click="() => { close(); handleLogout(); }"
                        class="w-full flex items-center gap-2 px-3.5 py-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors text-right text-xs"
                    >
                        <LogOut class="w-3.5 h-3.5" />
                        <span>خروج از حساب</span>
                    </button>
                </template>
            </BaseDropdown>
        </div>
    </header>
</template>
