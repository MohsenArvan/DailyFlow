<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    value: number;
    max?: number;
    color?: 'brand' | 'success' | 'warning' | 'danger';
    height?: 'sm' | 'md' | 'lg';
    showLabel?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    max: 100,
    color: 'brand',
    height: 'md',
    showLabel: false,
});

const percentage = computed(() => {
    if (props.max <= 0) return 0;
    const pct = Math.round((props.value / props.max) * 100);
    return Math.min(100, Math.max(0, pct));
});

const heightClasses = computed(() => {
    switch (props.height) {
        case 'sm': return 'h-1.5';
        case 'lg': return 'h-3';
        case 'md':
        default: return 'h-2';
    }
});

const colorClasses = computed(() => {
    switch (props.color) {
        case 'success': return 'bg-emerald-500';
        case 'warning': return 'bg-amber-500';
        case 'danger': return 'bg-rose-500';
        case 'brand':
        default: return 'bg-brand-500';
    }
});
</script>

<template>
    <div class="w-full font-vazir">
        <div v-if="showLabel" class="flex justify-between items-center text-xs text-zinc-500 dark:text-zinc-400 mb-1.5 font-medium">
            <span>پیشرفت</span>
            <span>{{ percentage }}%</span>
        </div>

        <div class="w-full rounded-full bg-zinc-100 dark:bg-zinc-800 overflow-hidden" :class="heightClasses">
            <div
                class="h-full rounded-full transition-all duration-500 ease-out"
                :class="colorClasses"
                :style="{ width: `${percentage}%` }"
                role="progressbar"
                :aria-valuenow="value"
                :aria-valuemin="0"
                :aria-valuemax="max"
            ></div>
        </div>
    </div>
</template>
