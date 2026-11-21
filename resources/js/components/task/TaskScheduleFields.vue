<script setup lang="ts">
import { ref, watch } from 'vue';
import { useTaskStore } from '@/stores/tasks';
import type { RecurrenceRule, Task } from '@/types/task';

const props = defineProps<{ task: Task }>();
const store = useTaskStore();
const frequency = ref<RecurrenceRule['frequency'] | ''>('');
const interval = ref(1);
const weekdays = ref<number[]>([]);
const endDate = ref('');
const reminder = ref('');
const dueDate = ref('');
const dueTime = ref('');
const saving = ref(false);
const status = ref('');
const dayNames = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];

watch(() => props.task, task => {
    frequency.value = task.recurrence_rule?.frequency ?? '';
    interval.value = task.recurrence_rule?.interval ?? 1;
    weekdays.value = task.recurrence_rule?.by_weekday ?? [];
    endDate.value = task.recurrence_rule?.end_date ?? '';
    dueDate.value = task.due_date ?? '';
    dueTime.value = task.due_time?.slice(0, 5) ?? '';
    const date = task.reminder_at ? new Date(task.reminder_at) : null;
    reminder.value = date
        ? new Date(date.getTime() - date.getTimezoneOffset() * 60_000).toISOString().slice(0, 16)
        : '';
}, { immediate: true });

async function save() {
    if (saving.value) return;
    if (frequency.value === 'custom' && !weekdays.value.length) {
        status.value = 'حداقل یک روز هفته را انتخاب کنید.';
        return;
    }

    saving.value = true;
    status.value = '';
    try {
        const updated = await store.updateTask(props.task.uuid, {
            due_date: dueDate.value || null,
            due_time: dueTime.value || null,
            recurrence_rule: frequency.value ? {
                frequency: frequency.value,
                interval: interval.value,
                ...(frequency.value === 'custom' ? { by_weekday: weekdays.value } : {}),
                end_date: endDate.value || null,
            } : null,
            reminder_at: reminder.value ? new Date(reminder.value).toISOString().replace('.000Z', '+00:00') : null,
        });
        status.value = updated ? 'زمان‌بندی ذخیره شد.' : 'ذخیرهٔ زمان‌بندی ممکن نشد.';
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <details class="rounded-xl border border-zinc-300 p-3 text-sm dark:border-zinc-700">
        <summary class="cursor-pointer font-semibold">زمان‌بندی، تکرار و یادآور</summary>
        <form class="mt-3 grid gap-3" @submit.prevent="save">
            <label>تاریخ سررسید <input v-model="dueDate" type="date" :required="!!frequency" class="block w-full rounded border bg-transparent p-1" /></label>
            <label>ساعت سررسید <input v-model="dueTime" type="time" class="block w-full rounded border bg-transparent p-1" /></label>
            <label>تکرار
                <select v-model="frequency" class="block w-full rounded border bg-surface-cardLight p-1 dark:bg-surface-cardDark">
                    <option value="">بدون تکرار</option>
                    <option value="daily">روزانه</option><option value="weekdays">دوشنبه تا جمعه</option>
                    <option value="weekly">هفتگی</option><option value="monthly">ماهانه</option>
                    <option value="yearly">سالانه</option><option value="custom">روزهای انتخابی هفته</option>
                </select>
            </label>
            <template v-if="frequency">
                <label>فاصلهٔ تکرار <input v-model.number="interval" type="number" required min="1" max="365" class="block w-full rounded border bg-transparent p-1" /></label>
                <fieldset v-if="frequency === 'custom'" class="flex flex-wrap gap-2">
                    <legend>روزهای هفته</legend>
                    <label v-for="(day, index) in dayNames" :key="day">
                        <input v-model="weekdays" type="checkbox" :value="index" /> {{ day }}
                    </label>
                </fieldset>
                <label>پایان تکرار <input v-model="endDate" type="date" class="block w-full rounded border bg-transparent p-1" /></label>
            </template>
            <label>یادآور (به ساعت دستگاه)
                <input v-model="reminder" type="datetime-local" class="block w-full rounded border bg-transparent p-1" />
            </label>
            <button type="submit" :disabled="saving" class="justify-self-start rounded-lg border border-brand-500 px-3 py-2">ذخیرهٔ زمان‌بندی</button>
            <p role="status">{{ status }}</p>
        </form>
    </details>
</template>
