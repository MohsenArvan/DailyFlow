<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue';
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import { useToast } from '@/composables/useToast';
import { CalendarDays, Plus } from 'lucide-vue-next';

const toast = useToast();

const handleAddTask = () => {
    toast.info('برنامه‌ریزی تسک‌های آینده در فاز ۵ فعال خواهد شد.');
};
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                    <CalendarDays class="w-5 h-5 text-violet-500" />
                    <span>برنامه‌ریزی آینده (به‌زودی)</span>
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    مشاهده کارهای زمان‌بندی‌شده برای روزها و هفته‌های آتی
                </p>
            </div>

            <BaseButton variant="primary" size="sm" @click="handleAddTask">
                <template #prefix>
                    <Plus class="w-4 h-4 stroke-[2.5]" />
                </template>
                زمان‌بندی تسک
            </BaseButton>
        </div>

        <div class="rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark p-8 shadow-subtle min-h-[340px] flex items-center justify-center">
            <BaseEmptyState
                title="تسک زمان‌بندی‌شده‌ای برای روزهای آینده وجود ندارد"
                description="برای هفته پیش‌رو برنامه‌ریزی کنید تا در روزهای آینده ذهنی آسوده داشته باشید."
            >
                <template #icon>
                    <CalendarDays class="w-6 h-6 text-violet-400 stroke-[1.8]" />
                </template>
                <template #action>
                    <BaseButton variant="outline" size="sm" @click="handleAddTask">
                        <template #prefix>
                            <Plus class="w-3.5 h-3.5" />
                        </template>
                        افزودن تسک آینده
                    </BaseButton>
                </template>
            </BaseEmptyState>
        </div>
    </AppLayout>
</template>
