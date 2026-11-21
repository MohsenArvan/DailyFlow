<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import AppLayout from '@/components/layout/AppLayout.vue';
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseProgressBar from '@/components/ui/BaseProgressBar.vue';
import { Calendar, Plus, Sparkles, CheckCircle2 } from 'lucide-vue-next';

const authStore = useAuthStore();
const toast = useToast();

const todayFormatted = computed(() => {
    const isJalali = authStore.userPreferences?.date_format !== 'gregorian';
    const locale = isJalali ? 'fa-IR' : 'en-US';
    return new Intl.DateTimeFormat(locale, {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(new Date());
});

const dailyGoal = computed(() => authStore.userPreferences?.daily_goal || 5);

const handleAddTask = () => {
    toast.info('قابلیت ایجاد و مدیریت کامل تسک‌ها در فاز ۵ فعال می‌شود.');
};
</script>

<template>
    <AppLayout>
        <!-- Top Date & Goal Summary -->
        <div class="mb-8 p-6 rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark shadow-card">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                        <span>کارهای امروز</span>
                        <span class="text-xs font-normal text-zinc-400">({{ todayFormatted }})</span>
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                        تسک‌های برنامه‌ریزی‌شده برای تمرکز امروز شما
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <BaseButton variant="primary" size="sm" @click="handleAddTask">
                        <template #prefix>
                            <Plus class="w-4 h-4 stroke-[2.5]" />
                        </template>
                        افزودن تسک
                    </BaseButton>
                </div>
            </div>

            <!-- Daily Goal Progress -->
            <div class="pt-3 border-t border-surface-borderLight dark:border-surface-borderDark">
                <div class="flex justify-between items-center text-xs mb-1.5">
                    <span class="font-medium text-zinc-600 dark:text-zinc-400">هدف تکمیل روزانه</span>
                    <span class="font-bold text-brand-600 dark:text-brand-400">۰ از {{ dailyGoal }} تسک</span>
                </div>
                <BaseProgressBar :value="0" :max="dailyGoal" color="brand" />
            </div>
        </div>

        <!-- Task List Area / Empty State -->
        <div class="rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark p-8 shadow-subtle min-h-[340px] flex items-center justify-center">
            <BaseEmptyState
                title="همه کارهای امروزتان به انجام رسیده یا هنوز تسکی ثبت نکرده‌اید"
                description="برای شروع روز پرانرژی، اولین کار امروزتان را با دکمه زیر یا کلید Q اضافه کنید."
            >
                <template #icon>
                    <CheckCircle2 class="w-6 h-6 text-brand-500 stroke-[2]" />
                </template>
                <template #action>
                    <BaseButton variant="outline" size="sm" @click="handleAddTask">
                        <template #prefix>
                            <Plus class="w-3.5 h-3.5" />
                        </template>
                        ثبت کار جدید برای امروز
                    </BaseButton>
                </template>
            </BaseEmptyState>
        </div>
    </AppLayout>
</template>
