<script setup lang="ts">
import { ref } from 'vue';
import api from '@/services/api';
import { Download, User, Sliders, Shield } from 'lucide-vue-next';
import AppLayout from '@/components/layout/AppLayout.vue';
import ProfileSettingsTab from '@/components/settings/ProfileSettingsTab.vue';
import PreferencesSettingsTab from '@/components/settings/PreferencesSettingsTab.vue';
import SecuritySettingsTab from '@/components/settings/SecuritySettingsTab.vue';

type Tab = 'profile' | 'preferences' | 'security' | 'data';
const activeTab = ref<Tab>('profile');

const tabs = [
    { id: 'profile', name: 'پروفایل کاربری', icon: User },
    { id: 'preferences', name: 'ظاهر و ترجیحات', icon: Sliders },
    { id: 'data', name: 'داده‌ها', icon: Download },
    { id: 'security', name: 'امنیت و کلمه عبور', icon: Shield },
];
const downloadStatus = ref('');
const downloading = ref(false);
const browserNotifications = typeof Notification !== 'undefined';

async function exportData(format: 'json' | 'csv') {
    downloading.value = true;
    downloadStatus.value = '';
    try {
        const response = await api.get<Blob>('/user/export', { params: { format }, responseType: 'blob' });
        const url = URL.createObjectURL(response.data);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'dailyflow.' + format;
        link.click();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    } catch {
        downloadStatus.value = 'دریافت خروجی ممکن نشد.';
    } finally {
        downloading.value = false;
    }
}

async function enableNotifications() {
    const permission = await Notification.requestPermission();
    downloadStatus.value = permission === 'granted' ? 'اعلان مرورگر فعال شد.' : 'مجوز اعلان مرورگر صادر نشد.';
}
</script>

<template>
    <AppLayout>
        <div class="page-heading">
            <h2 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">تنظیمات حساب کاربری</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">مدیریت مشخصات فردی، ظاهر برنامه و امنیت حساب شما</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Sidebar / Tabs Navigation -->
            <div class="md:col-span-1">
                <nav class="flex flex-wrap md:flex-col gap-1.5 p-1 rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id as Tab"
                        class="flex-1 md:flex-initial flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all text-right"
                        :class="activeTab === tab.id
                            ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30'
                            : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:bg-zinc-100 dark:hover:bg-zinc-800/60'"
                    >
                        <component :is="tab.icon" class="w-4 h-4 shrink-0" />
                        <span class="truncate">{{ tab.name }}</span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content Pane -->
            <div class="md:col-span-3">
                <div class="workspace-panel p-5 sm:p-8">
                    <ProfileSettingsTab v-if="activeTab === 'profile'" />
                    <PreferencesSettingsTab v-else-if="activeTab === 'preferences'" />
                    <SecuritySettingsTab v-else-if="activeTab === 'security'" />
                    <section v-else class="space-y-3">
                        <h3 class="font-semibold">خروجی داده‌ها و اعلان‌ها</h3>
                        <div class="flex flex-wrap gap-2">
                            <button v-for="format in (['json', 'csv'] as const)" :key="format" :disabled="downloading" type="button"
                                class="rounded-lg border border-zinc-400 px-3 py-2 text-sm" @click="exportData(format)">
                                دریافت {{ format.toUpperCase() }}
                            </button>
                            <button v-if="browserNotifications" type="button" class="rounded-lg border border-zinc-400 px-3 py-2 text-sm" @click="enableNotifications">
                                فعال‌کردن اعلان مرورگر
                            </button>
                        </div>
                        <p role="status">{{ downloadStatus }}</p>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
