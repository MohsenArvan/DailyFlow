<script setup lang="ts">
import { Flame } from 'lucide-vue-next';

defineProps<{ current: number; best: number }>();
</script>

<template>
    <div>
        <h2 class="flex items-center gap-2 font-semibold"><Flame aria-hidden="true" class="size-5 text-amber-600 dark:text-amber-400" /> روزهای متوالی فعال</h2>
        <p class="mt-3 text-3xl font-bold tabular-nums">{{ current }} روز</p>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">بهترین رکورد {{ best }} روز</p>
    </div>
</template>
