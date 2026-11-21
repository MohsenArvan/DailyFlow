<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    variant?: 'default' | 'brand' | 'success' | 'warning' | 'danger' | 'urgent' | 'high' | 'medium' | 'low';
    size?: 'sm' | 'md';
    dot?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
    size: 'sm',
    dot: false,
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'brand':
            return 'bg-brand-500/10 text-brand-600 dark:text-brand-400 border-brand-500/20';
        case 'success':
            return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
        case 'warning':
            return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
        case 'danger':
        case 'urgent':
            return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20';
        case 'high':
            return 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20';
        case 'medium':
            return 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20';
        case 'low':
            return 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20';
        case 'default':
        default:
            return 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 border-surface-borderLight dark:border-surface-borderDark';
    }
});

const dotColor = computed(() => {
    switch (props.variant) {
        case 'brand': return 'bg-brand-500';
        case 'success': return 'bg-emerald-500';
        case 'warning': return 'bg-amber-500';
        case 'danger':
        case 'urgent': return 'bg-rose-500';
        case 'high': return 'bg-orange-500';
        case 'medium': return 'bg-blue-500';
        case 'low': return 'bg-zinc-400';
        default: return 'bg-zinc-500';
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 font-vazir font-medium border rounded-full select-none"
        :class="[
            variantClasses,
            size === 'md' ? 'px-2.5 py-0.5 text-xs' : 'px-2 py-0.5 text-[11px]'
        ]"
    >
        <span v-if="dot" class="w-1.5 h-1.5 rounded-full shrink-0" :class="dotColor"></span>
        <slot />
    </span>
</template>
