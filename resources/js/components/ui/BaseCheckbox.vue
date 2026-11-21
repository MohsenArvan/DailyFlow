<script setup lang="ts">
import { computed } from 'vue';
import { Check } from 'lucide-vue-next';

interface Props {
    modelValue?: boolean;
    label?: string;
    ariaLabel?: string;
    priority?: 'urgent' | 'high' | 'medium' | 'low' | 'none';
    disabled?: boolean;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: false,
    priority: 'none',
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
    (e: 'change', value: boolean): void;
}>();

const checkboxId = computed(() => props.id || `checkbox-${Math.random().toString(36).substring(2, 9)}`);

const toggle = () => {
    if (props.disabled) return;
    const newVal = !props.modelValue;
    emit('update:modelValue', newVal);
    emit('change', newVal);
};

const priorityBorderColor = computed(() => {
    if (props.modelValue) return 'border-brand-600 bg-brand-600 dark:border-brand-500 dark:bg-brand-500 text-white';
    switch (props.priority) {
        case 'urgent':
            return 'border-rose-500 hover:border-rose-600 hover:bg-rose-500/10 text-rose-500';
        case 'high':
            return 'border-amber-500 hover:border-amber-600 hover:bg-amber-500/10 text-amber-500';
        case 'medium':
            return 'border-blue-500 hover:border-blue-600 hover:bg-blue-500/10 text-blue-500';
        case 'low':
            return 'border-zinc-400 dark:border-zinc-500 hover:border-zinc-600 hover:bg-zinc-500/10 text-zinc-500';
        case 'none':
        default:
            return 'border-zinc-300 dark:border-zinc-600 hover:border-brand-500 hover:bg-brand-500/10 text-brand-600';
    }
});
</script>

<template>
    <div
        class="inline-flex items-center gap-2.5 font-vazir select-none cursor-pointer group"
        :class="{ 'opacity-50 cursor-not-allowed': disabled }"
        @click="toggle"
    >
        <button
            type="button"
            role="checkbox"
            :id="checkboxId"
            :aria-label="ariaLabel"
            :aria-checked="modelValue"
            :disabled="disabled"
            class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-500/40"
            :class="priorityBorderColor"
        >
            <Check
                v-if="modelValue"
                class="w-3 h-3 stroke-[3] text-white animate-in zoom-in-75 duration-150"
            />
        </button>

        <label
            v-if="label"
            :for="checkboxId"
            class="text-xs font-medium cursor-pointer transition-colors"
            :class="modelValue ? 'text-zinc-400 line-through' : 'text-zinc-800 dark:text-zinc-200 group-hover:text-zinc-950 dark:group-hover:text-white'"
        >
            {{ label }}
        </label>
    </div>
</template>
