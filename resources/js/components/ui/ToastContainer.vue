<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { CheckCircle2, AlertCircle, AlertTriangle, Info, X } from 'lucide-vue-next';

const { toasts, remove } = useToast();
</script>

<template>
    <Teleport to="body">
        <div class="fixed bottom-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none font-vazir text-right px-4 sm:px-0">
            <TransitionGroup
                enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto flex items-start gap-3 p-3.5 rounded-2xl border shadow-dropdown bg-surface-cardLight dark:bg-surface-cardDark"
                    :class="[
                        toast.type === 'success' ? 'border-emerald-500/30 text-emerald-900 dark:text-emerald-100' : '',
                        toast.type === 'error' ? 'border-rose-500/30 text-rose-900 dark:text-rose-100' : '',
                        toast.type === 'warning' ? 'border-amber-500/30 text-amber-900 dark:text-amber-100' : '',
                        toast.type === 'info' ? 'border-brand-500/30 text-brand-900 dark:text-brand-100' : '',
                    ]"
                    role="status"
                >
                    <!-- Type Icon -->
                    <div class="shrink-0 mt-0.5">
                        <CheckCircle2 v-if="toast.type === 'success'" class="w-4 h-4 text-emerald-500" />
                        <AlertCircle v-else-if="toast.type === 'error'" class="w-4 h-4 text-rose-500" />
                        <AlertTriangle v-else-if="toast.type === 'warning'" class="w-4 h-4 text-amber-500" />
                        <Info v-else class="w-4 h-4 text-brand-500" />
                    </div>

                    <!-- Message -->
                    <div class="flex-1 text-xs font-medium leading-relaxed">
                        {{ toast.message }}
                    </div>

                    <!-- Dismiss -->
                    <button
                        type="button"
                        @click="remove(toast.id)"
                        class="p-0.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors"
                        title="بستن" aria-label="بستن پیام"
                    >
                        <X class="w-3.5 h-3.5" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
