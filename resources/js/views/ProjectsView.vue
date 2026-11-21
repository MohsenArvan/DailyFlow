<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue';
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import { useToast } from '@/composables/useToast';
import { FolderKanban, Plus } from 'lucide-vue-next';

const toast = useToast();

const handleAddProject = () => {
    toast.info('مدیریت کامل پروژه‌ها و دسته‌بندی تسک‌ها در فاز ۶ فعال خواهد شد.');
};
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                    <FolderKanban class="w-5 h-5 text-amber-500" />
                    <span>پروژه‌های شخصی</span>
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    سازمان‌دهی کارها بر اساس موضوعات، اهداف بلندمدت و حوزه‌های زندگی
                </p>
            </div>

            <BaseButton variant="primary" size="sm" @click="handleAddProject">
                <template #prefix>
                    <Plus class="w-4 h-4 stroke-[2.5]" />
                </template>
                پروژه جدید
            </BaseButton>
        </div>

        <div class="rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark p-8 shadow-subtle min-h-[340px] flex items-center justify-center">
            <BaseEmptyState
                title="هنوز پروژه‌ای تعریف نکرده‌اید"
                description="برای تفکیک کارهای کاری، تحصیلی و شخصی، می‌توانید پروژه‌های اختصاصی ایجاد کنید."
            >
                <template #icon>
                    <FolderKanban class="w-6 h-6 text-amber-500 stroke-[1.8]" />
                </template>
                <template #action>
                    <BaseButton variant="outline" size="sm" @click="handleAddProject">
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
