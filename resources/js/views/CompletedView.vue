<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue';
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue';
import { CheckSquare } from 'lucide-vue-next';
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                <CheckSquare class="w-5 h-5 text-emerald-500" />
                <span>کارهای تکمیل‌شده</span>
            </h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                آرشیو کارهای انجام‌شده شما در طول روزها و هفته‌های گذشته
            </p>
        </div>

        <div class="rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark p-8 shadow-subtle min-h-[340px] flex items-center justify-center">
            <BaseEmptyState
                title="هنوز تسک انجام‌شده‌ای در سابقه ثبت نشده است"
                description="با تکمیل اولین کار در بخش امروز یا اینباکس، تاریخچه دستاوردهایتان اینجا ثبت خواهد شد."
            >
                <template #icon>
                    <CheckSquare class="w-6 h-6 text-emerald-400 stroke-[1.8]" />
                </template>
            </BaseEmptyState>
        </div>
    </AppLayout>
</template>
