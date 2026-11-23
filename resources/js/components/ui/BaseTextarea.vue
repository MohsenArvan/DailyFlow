<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    modelValue?: string;
    label?: string;
    placeholder?: string;
    error?: string;
    hint?: string;
    disabled?: boolean;
    rows?: number;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    disabled: false,
    rows: 3,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const textareaId = computed(() => props.id || `textarea-${Math.random().toString(36).substring(2, 9)}`);

const handleInput = (e: Event) => {
    const target = e.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <div class="w-full font-vazir text-right">
        <label
            v-if="label"
            :for="textareaId"
            class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5"
        >
            {{ label }}
        </label>

        <textarea
            :id="textareaId"
            :value="modelValue"
            :rows="rows"
            :placeholder="placeholder"
            :disabled="disabled"
            :aria-invalid="!!error"
            :aria-describedby="error || hint ? textareaId + '-message' : undefined"
            @input="handleInput"
            class="w-full px-3.5 py-2.5 rounded-xl border bg-surface-subtleLight dark:bg-surface-subtleDark text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed resize-y"
            :class="[
                error
                    ? 'border-rose-500 focus:ring-rose-500/30 focus:border-rose-500'
                    : 'border-surface-borderLight dark:border-surface-borderDark focus:ring-brand-500/30 focus:border-brand-500',
            ]"
        ></textarea>

        <p v-if="error" :id="textareaId + '-message'" role="status" class="mt-1 text-[11px] text-rose-500 font-medium">
            {{ error }}
        </p>
        <p v-else-if="hint" :id="textareaId + '-message'" class="mt-1 text-[11px] text-zinc-400">
            {{ hint }}
        </p>
    </div>
</template>
