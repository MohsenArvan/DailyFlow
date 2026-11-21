<script setup lang="ts">
import { watch, onMounted, onUnmounted, computed } from 'vue';
import { X } from 'lucide-vue-next';

interface Props {
    isOpen: boolean;
    title?: string;
    description?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
});

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm': return 'max-w-sm';
        case 'lg': return 'max-w-2xl';
        case 'xl': return 'max-w-4xl';
        case 'md':
        default:
            return 'max-w-lg';
    }
});

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.isOpen) {
        emit('close');
    }
};

watch(
    () => props.isOpen,
    (open) => {
        if (open) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
);

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 font-vazir text-right">
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm transition-opacity animate-in fade-in duration-200"
                @click="emit('close')"
            ></div>

            <!-- Modal Window -->
            <div
                class="relative w-full rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark p-6 shadow-modal z-10 animate-in zoom-in-95 fade-in duration-200"
                :class="sizeClasses"
                role="dialog"
                aria-modal="true"
            >
                <!-- Modal Header -->
                <div class="flex items-start justify-between pb-4 border-b border-surface-borderLight dark:border-surface-borderDark">
                    <div>
                        <h3 v-if="title" class="text-base font-bold text-zinc-900 dark:text-zinc-50">
                            {{ title }}
                        </h3>
                        <p v-if="description" class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            {{ description }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="emit('close')"
                        class="p-1 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500"
                        title="بستن"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="py-4">
                    <slot />
                </div>

                <!-- Modal Footer -->
                <div v-if="$slots.footer" class="pt-4 border-t border-surface-borderLight dark:border-surface-borderDark flex items-center justify-end gap-2.5">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </Teleport>
</template>
