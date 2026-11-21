<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue';
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import { useToast } from '@/composables/useToast';
import { Inbox, Plus } from 'lucide-vue-next';

const toast = useToast();

const handleAddTask = () => {
    toast.info('قابلیت ثبت سریع در صندوق ورودی در فاز ۵ فعال می‌شود.');
};
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                    <Inbox class="w-5 h-5 text-zinc-500" />
                    <span>صندوق ورودی (Inbox)</span>
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    جایگاهی برای ثبت سریع ایده‌ها و کارهایی که هنوز زمان‌بندی نشده‌اند
                </p>
            </div>

            <BaseButton variant="primary" size="sm" @click="handleAddTask">
                <template #prefix>
                    <Plus class="w-4 h-4 stroke-[2.5]" />
                </template>
                افزودن به اینباکس
            </BaseButton>
        </div>

        <div class="rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark p-8 shadow-subtle min-h-[340px] flex items-center justify-center">
            <BaseEmptyState
                title="صندوق ورودی شما خلوت و تمیز است"
                description="هر زمان کاری به ذهنتان رسید، سریعاً آن را به اینباکس بسپارید تا بعداً دسته‌بندی و زمان‌بندی کنید."
            >
                <template #icon>
                    <Inbox class="w-6 h-6 text-zinc-400 stroke-[1.8]" />
                </template>
                <template #action>
                    <BaseButton variant="outline" size="sm" @click="handleAddTask">
                        <template #prefix>
                            <Plus class="w-3.5 h-3.5" />
                        </template>
                        ثبت کار در اینباکس
                    </BaseButton>
                </template>
            </BaseEmptyState>
        </div>
    </AppLayout>
</template>
