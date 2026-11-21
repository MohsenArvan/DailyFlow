<script setup lang="ts">
import { computed } from 'vue';
import { Loader2 } from 'lucide-vue-next';

interface Props {
    variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
    size?: 'xs' | 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
    loading?: boolean;
    disabled?: boolean;
    block?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
    loading: false,
    disabled: false,
    block: false,
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-surface-subtleLight dark:bg-surface-subtleDark hover:bg-zinc-200 dark:hover:bg-zinc-700/60 text-zinc-800 dark:text-zinc-200 border border-surface-borderLight dark:border-surface-borderDark';
        case 'outline':
            return 'bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800/50 text-zinc-700 dark:text-zinc-200 border border-surface-borderLight dark:border-surface-borderDark';
        case 'ghost':
            return 'bg-transparent hover:bg-zinc-100 dark:hover:bg-zinc-800/60 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 border-transparent';
        case 'danger':
            return 'bg-rose-600 hover:bg-rose-500 text-white shadow-sm shadow-rose-600/20 border-transparent';
        case 'primary':
        default:
            return 'bg-brand-600 hover:bg-brand-500 text-white shadow-sm shadow-brand-600/25 border-transparent';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'xs':
            return 'px-2.5 py-1 text-[11px] rounded-lg gap-1.5';
        case 'sm':
            return 'px-3 py-1.5 text-xs rounded-xl gap-1.5';
        case 'lg':
            return 'px-5 py-3 text-base rounded-2xl gap-2.5';
        case 'md':
        default:
            return 'px-4 py-2 text-xs font-medium rounded-xl gap-2';
    }
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        class="inline-flex items-center justify-center font-vazir select-none transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-brand-500/50 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
        :class="[variantClasses, sizeClasses, block ? 'w-full' : '']"
    >
        <Loader2 v-if="loading" class="w-3.5 h-3.5 animate-spin shrink-0" />
        <slot name="prefix" v-else />
        <slot />
        <slot name="suffix" />
    </button>
</template>
