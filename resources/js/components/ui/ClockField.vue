<script setup lang="ts">
import { computed, ref } from 'vue';
import { Clock3, X } from 'lucide-vue-next';

const props = defineProps<{ modelValue: string | null; label: string }>();
const emit = defineEmits<{ 'update:modelValue': [value: string | null] }>();
const hourSelect = ref<HTMLSelectElement>();
function clear() {
    hourSelect.value?.focus();
    emit('update:modelValue', null);
}
const parts = computed(() => props.modelValue?.split(':') ?? ['', '']);
const format = new Intl.NumberFormat('fa-IR', { minimumIntegerDigits: 2 });
const values = (count: number) => Array.from({ length: count }, (_, value) => ({
    id: String(value).padStart(2, '0'),
    label: format.format(value),
}));
const hours = values(24);
const minutes = values(60);

function change(index: number, event: Event) {
    const next = (event.target as HTMLSelectElement).value;
    const [hour, minute] = parts.value;
    emit('update:modelValue', index === 0 ? `${next}:${minute || '00'}` : `${hour || '00'}:${next}`);
}
</script>

<template>
    <fieldset class="min-w-0">
        <legend class="task-field-label">{{ label }}</legend>
        <div class="flex min-h-11 items-center gap-2 rounded-xl border bg-surface-subtleLight/65 px-2 py-1 dark:bg-surface-subtleDark/75">
            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand-100 text-brand-800 dark:bg-brand-950 dark:text-brand-200">
                <Clock3 aria-hidden="true" class="size-4" />
            </span>
            <div dir="ltr" class="flex min-w-0 flex-1 items-center gap-1 text-sm font-semibold tabular-nums">
                <select ref="hourSelect" :value="parts[0]" :aria-label="`${label}، ساعت`" class="min-w-0 flex-1 rounded-md bg-surface-cardLight px-1 py-1.5 dark:bg-surface-cardDark" @change="change(0, $event)">
                    <option value="" disabled>ساعت</option>
                    <option v-for="entry in hours" :key="entry.id" :value="entry.id">{{ entry.label }}</option>
                </select>
                <span aria-hidden="true" class="text-brand-700 dark:text-brand-200">:</span>
                <select :value="parts[1]" :aria-label="`${label}، دقیقه`" class="min-w-0 flex-1 rounded-md bg-surface-cardLight px-1 py-1.5 dark:bg-surface-cardDark" @change="change(1, $event)">
                    <option value="" disabled>دقیقه</option>
                    <option v-for="entry in minutes" :key="entry.id" :value="entry.id">{{ entry.label }}</option>
                </select>
            </div>
            <button v-if="modelValue" type="button" :aria-label="`پاک کردن ${label}`"
                class="grid size-8 shrink-0 place-items-center rounded-lg text-zinc-600 hover:bg-brand-100 dark:text-zinc-300 dark:hover:bg-brand-950" @click="clear">
                <X aria-hidden="true" class="size-4" />
            </button>
        </div>
    </fieldset>
</template>
