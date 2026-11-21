<script setup lang="ts">
import { Sparkles } from 'lucide-vue-next';

interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="py-12 px-4 text-center max-w-sm mx-auto font-vazir">
        <div class="w-12 h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800/80 border border-surface-borderLight dark:border-surface-borderDark text-zinc-500 dark:text-zinc-400 mx-auto flex items-center justify-center mb-3.5 shadow-sm">
            <slot name="icon">
                <Sparkles class="w-5 h-5 stroke-[1.8]" />
            </slot>
        </div>

        <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-200 mb-1">
            {{ title }}
        </h3>

        <p v-if="description" class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed mb-4">
            {{ description }}
        </p>

        <div v-if="$slots.action" class="flex justify-center">
            <slot name="action" />
        </div>
    </div>
</template>
