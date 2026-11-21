<script setup lang="ts">
import { ref } from 'vue';

interface Props {
    text: string;
    position?: 'top' | 'bottom' | 'right' | 'left';
}

const props = withDefaults(defineProps<Props>(), {
    position: 'top',
});

const isVisible = ref(false);
</script>

<template>
    <div
        class="relative inline-flex font-vazir"
        @mouseenter="isVisible = true"
        @mouseleave="isVisible = false"
        @focusin="isVisible = true"
        @focusout="isVisible = false"
    >
        <slot />

        <div
            v-if="isVisible"
            class="absolute z-50 px-2.5 py-1 text-[11px] font-medium text-white bg-zinc-900 dark:bg-zinc-800 rounded-lg shadow-md whitespace-nowrap pointer-events-none animate-in fade-in zoom-in-95 duration-150"
            :class="[
                position === 'top' ? 'bottom-full mb-1.5 left-1/2 -translate-x-1/2' : '',
                position === 'bottom' ? 'top-full mt-1.5 left-1/2 -translate-x-1/2' : '',
                position === 'right' ? 'right-full mr-1.5 top-1/2 -translate-y-1/2' : '',
                position === 'left' ? 'left-full ml-1.5 top-1/2 -translate-y-1/2' : '',
            ]"
            role="tooltip"
        >
            {{ text }}
        </div>
    </div>
</template>
