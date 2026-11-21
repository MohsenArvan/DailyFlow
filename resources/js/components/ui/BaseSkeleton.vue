<script setup lang="ts">
interface Props {
    variant?: 'text' | 'circular' | 'rectangular';
    width?: string;
    height?: string;
    count?: number;
}

withDefaults(defineProps<Props>(), {
    variant: 'text',
    count: 1,
});
</script>

<template>
    <div class="space-y-2">
        <div
            v-for="i in count"
            :key="i"
            class="animate-pulse bg-zinc-200/70 dark:bg-zinc-800/60"
            :class="[
                variant === 'circular' ? 'rounded-full' : '',
                variant === 'rectangular' ? 'rounded-xl' : '',
                variant === 'text' ? 'rounded-md h-3.5' : '',
            ]"
            :style="{
                width: width || (variant === 'circular' ? '40px' : '100%'),
                height: height || (variant === 'circular' ? '40px' : undefined)
            }"
        ></div>
    </div>
</template>
