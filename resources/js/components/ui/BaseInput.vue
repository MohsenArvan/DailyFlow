<script setup lang="ts">
import { computed } from 'vue';
import { X } from 'lucide-vue-next';

interface Props {
    modelValue?: string | number;
    type?: string;
    label?: string;
    placeholder?: string;
    error?: string;
    hint?: string;
    disabled?: boolean;
    clearable?: boolean;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    type: 'text',
    disabled: false,
    clearable: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const inputId = computed(() => props.id || `input-${Math.random().toString(36).substring(2, 9)}`);

const handleInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};

const handleClear = () => {
    emit('update:modelValue', '');
};
</script>

<template>
    <div class="w-full font-vazir text-right">
        <label
            v-if="label"
            :for="inputId"
            class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5"
        >
            {{ label }}
        </label>

        <div class="relative flex items-center">
            <!-- Prefix slot/icon -->
            <div
                v-if="$slots.prefix"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400"
            >
                <slot name="prefix" />
            </div>

            <input
                :id="inputId"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :aria-invalid="!!error"
                :aria-describedby="error || hint ? inputId + '-message' : undefined"
                @input="handleInput"
                class="w-full py-2.5 rounded-xl border bg-surface-subtleLight dark:bg-surface-subtleDark text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-600 dark:placeholder-zinc-400 focus:outline-none focus:ring-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                :class="[
                    $slots.prefix ? 'pr-10' : 'pr-3.5',
                    $slots.suffix || clearable ? 'pl-10' : 'pl-3.5',
                    error
                        ? 'border-rose-500 focus:ring-rose-500/30 focus:border-rose-500'
                        : 'border-surface-borderLight dark:border-surface-borderDark focus:ring-brand-500/30 focus:border-brand-500',
                ]"
            />

            <!-- Clear button or Suffix slot -->
            <button
                v-if="clearable && modelValue && !disabled"
                type="button"
                aria-label="پاک کردن ورودی"
                @click="handleClear"
                class="absolute inset-y-0 left-0 pl-3 flex items-center cursor-pointer text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
            >
                <X aria-hidden="true" class="w-4 h-4" />
            </button>
            <div
                v-else-if="$slots.suffix"
                class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-400"
            >
                <slot name="suffix" />
            </div>
        </div>

        <p v-if="error" :id="inputId + '-message'" role="status" class="mt-1 text-xs font-medium text-rose-700 dark:text-rose-300">
            {{ error }}
        </p>
        <p v-else-if="hint" :id="inputId + '-message'" class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
            {{ hint }}
        </p>
    </div>
</template>
