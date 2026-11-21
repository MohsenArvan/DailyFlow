<script setup lang="ts">
import type { DashboardSummary } from '@/types/dashboard';

defineProps<{ counts: DashboardSummary['priority_distribution'] }>();
const priorities = [
    { key: 'urgent', label: 'فوری', color: 'bg-rose-500' },
    { key: 'high', label: 'بالا', color: 'bg-orange-500' },
    { key: 'medium', label: 'متوسط', color: 'bg-amber-400' },
    { key: 'low', label: 'پایین', color: 'bg-sky-500' },
    { key: 'none', label: 'بدون اولویت', color: 'bg-zinc-500' },
] as const;
</script>

<template>
    <section>
        <h2 class="mb-3 font-semibold">اولویت‌ها</h2>
        <div class="flex h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700" aria-hidden="true">
            <span v-for="item in priorities" :key="item.key" :class="item.color" :style="{ flexGrow: counts[item.key], flexBasis: 0 }" />
        </div>
        <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm">
            <li v-for="item in priorities" :key="item.key">{{ item.label }} <strong>{{ counts[item.key] }}</strong></li>
        </ul>
    </section>
</template>
