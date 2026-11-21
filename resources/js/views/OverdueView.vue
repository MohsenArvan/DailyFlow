<script setup lang="ts">
import AppLayout from '@/components/layout/AppLayout.vue';
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue';
import { AlertCircle, CheckCircle2 } from 'lucide-vue-next';
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h2 class="text-xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                <AlertCircle class="w-5 h-5 text-rose-500" />
                <span>کارهای عقب‌افتاده (Overdue)</span>
            </h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                تسک‌هایی که موعد انجام آن‌ها سپری شده و نیاز به بازبینی یا انتقال به امروز دارند
            </p>
        </div>

        <div class="rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark p-8 shadow-subtle min-h-[340px] flex items-center justify-center">
            <BaseEmptyState
                title="عالی است! هیچ کار عقب‌افتاده‌ای ندارید"
                description="تمام کارهایتان به‌موقع انجام شده‌اند و هیچ تسک معوقه‌ای باقی نمانده است."
            >
                <template #icon>
                    <CheckCircle2 class="w-6 h-6 text-emerald-500 stroke-[2]" />
                </template>
            </BaseEmptyState>
        </div>
    </AppLayout>
</template>
