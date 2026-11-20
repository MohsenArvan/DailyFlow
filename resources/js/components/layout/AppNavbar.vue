<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import {
    CheckCircle2,
    Sun,
    Moon,
    Settings,
    LogOut,
    LogIn,
    UserPlus,
} from 'lucide-vue-next';

const router = useRouter();
const authStore = useAuthStore();
const isMenuOpen = ref(false);
const menuRef = ref<HTMLElement | null>(null);

const toggleTheme = () => {
    const isDark = document.documentElement.classList.contains('dark');
    authStore.updatePreferences({ theme: isDark ? 'light' : 'dark' });
};

const handleLogout = async () => {
    isMenuOpen.value = false;
    await authStore.logout();
    router.push('/login');
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isMenuOpen.value) {
        isMenuOpen.value = false;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <header class="w-full border-b border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight/80 dark:bg-surface-cardDark/80 backdrop-blur-md sticky top-0 z-50 transition-colors duration-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <router-link to="/" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white shadow-md shadow-brand-600/30 group-hover:scale-105 transition-transform">
                    <CheckCircle2 class="w-5 h-5 stroke-[2.2]" />
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-bold tracking-tight text-zinc-900 dark:text-zinc-50 font-vazir leading-none">DailyFlow</span>
                    <span class="text-[10px] text-zinc-400 font-medium font-vazir">جریان روزانه کارهای من</span>
                </div>
            </router-link>

            <!-- Actions -->
            <div class="flex items-center gap-2.5">
                <!-- Theme Toggle -->
                <button
                    @click="toggleTheme"
                    class="p-2 rounded-xl text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500"
                    title="تغییر تم"
                    aria-label="تغییر تم"
                >
                    <Sun class="w-4 h-4 hidden dark:block" />
                    <Moon class="w-4 h-4 block dark:hidden" />
                </button>

                <!-- Authenticated User Menu -->
                <template v-if="authStore.isAuthenticated">
                    <div class="relative" ref="menuRef">
                        <button
                            @click="isMenuOpen = !isMenuOpen"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors text-sm font-medium text-zinc-700 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-brand-500"
                            aria-haspopup="true"
                            :aria-expanded="isMenuOpen"
                        >
                            <div class="w-6 h-6 rounded-lg bg-brand-500/10 text-brand-600 dark:text-brand-400 flex items-center justify-center text-xs font-bold font-vazir">
                                {{ authStore.user?.name ? authStore.user.name.charAt(0) : 'U' }}
                            </div>
                            <span class="hidden sm:inline font-vazir text-xs">{{ authStore.user?.name }}</span>
                        </button>

                        <!-- Invisible Backdrop to dismiss dropdown -->
                        <div
                            v-if="isMenuOpen"
                            @click="isMenuOpen = false"
                            class="fixed inset-0 z-40 bg-transparent"
                        ></div>

                        <!-- Dropdown Menu -->
                        <div
                            v-if="isMenuOpen"
                            class="absolute left-0 mt-2 w-48 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark shadow-xl py-1 z-50 text-xs font-vazir"
                        >
                            <div class="px-3 py-2 border-b border-surface-borderLight dark:border-surface-borderDark">
                                <p class="font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ authStore.user?.name }}</p>
                                <p class="text-[11px] text-zinc-400 truncate">{{ authStore.user?.email }}</p>
                            </div>

                            <router-link
                                to="/settings"
                                @click="isMenuOpen = false"
                                class="flex items-center gap-2 px-3 py-2 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors"
                            >
                                <Settings class="w-4 h-4" />
                                <span>تنظیمات و پروفایل</span>
                            </router-link>

                            <button
                                @click="handleLogout"
                                class="w-full flex items-center gap-2 px-3 py-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors text-right"
                            >
                                <LogOut class="w-4 h-4" />
                                <span>خروج از حساب</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Guest Links -->
                <template v-else>
                    <router-link
                        to="/login"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-medium font-vazir text-zinc-600 dark:text-zinc-300 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/60 transition-colors flex items-center gap-1.5"
                    >
                        <LogIn class="w-3.5 h-3.5" />
                        <span>ورود</span>
                    </router-link>

                    <router-link
                        to="/register"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-medium font-vazir bg-brand-600 hover:bg-brand-500 text-white shadow-sm shadow-brand-600/30 transition-colors flex items-center gap-1.5"
                    >
                        <UserPlus class="w-3.5 h-3.5" />
                        <span>ثبت‌نام رایگان</span>
                    </router-link>
                </template>
            </div>
        </div>
    </header>
</template>
