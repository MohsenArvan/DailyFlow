<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

interface Props {
    align?: 'right' | 'left';
    widthClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    align: 'right',
    widthClass: 'w-48',
});

const isOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const close = () => {
    isOpen.value = false;
};

const handleClickOutside = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        isOpen.value = false;
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isOpen.value) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('keydown', handleKeydown);
});

defineExpose({ close, isOpen });
</script>

<template>
    <div class="relative inline-block text-right font-vazir" ref="dropdownRef">
        <!-- Trigger -->
        <div @click="toggle" class="cursor-pointer">
            <slot name="trigger" :is-open="isOpen" />
        </div>

        <!-- Menu Container -->
        <div
            v-if="isOpen"
            class="absolute mt-2 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark shadow-dropdown py-1 z-40 text-xs animate-in fade-in zoom-in-95 duration-150"
            :class="[
                widthClass,
                align === 'left' ? 'left-0' : 'right-0'
            ]"
            role="menu"
        >
            <slot :close="close" />
        </div>
    </div>
</template>
