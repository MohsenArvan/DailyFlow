<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import type { VelocityDay } from '@/types/dashboard';

const props = defineProps<{ days: VelocityDay[] }>();
const auth = useAuthStore();
const peak = computed(() => Math.max(1, ...props.days.map(day => day.count)));
const dateFormat = computed(() => new Intl.DateTimeFormat(
    auth.userPreferences?.date_format === 'gregorian' ? 'fa-IR-u-ca-gregory' : 'fa-IR',
    { month: 'short', day: 'numeric', timeZone: 'UTC' },
));
</script>

<template>
    <figure>
        <svg aria-hidden="true" viewBox="0 0 700 145" class="w-full">
            <path d="M0 144 H700" class="stroke-zinc-300 dark:stroke-zinc-700" />
            <rect v-for="(day, index) in days" :key="day.date"
                :x="(index + 0.2) * 700 / days.length"
                :width="420 / days.length"
                :height="Math.max(2, day.count / peak * 130)"
                :y="144 - Math.max(2, day.count / peak * 130)"
                :class="index === days.length - 1 ? 'fill-brand-600 dark:fill-brand-400' : 'fill-zinc-400 dark:fill-zinc-500'"
                rx="3">
                <title>{{ dateFormat.format(new Date(day.date)) }} • {{ day.count }}</title>
            </rect>
        </svg>
        <ol class="mt-2 flex justify-between gap-1 text-xs text-zinc-600 dark:text-zinc-300" dir="ltr">
            <li v-for="(day, index) in days" :key="day.date" :class="days.length > 7 && index % 7 !== 0 && index !== days.length - 1 ? 'sr-only' : ''">
                {{ dateFormat.format(new Date(day.date)) }}
                <span class="sr-only">{{ day.count }} تسک تکمیل‌شده</span>
            </li>
        </ol>
    </figure>
</template>
