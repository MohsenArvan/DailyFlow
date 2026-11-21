<script setup lang="ts">
import { ref, watch } from 'vue';
import CalendarField from '@/components/ui/CalendarField.vue';
import ClockField from '@/components/ui/ClockField.vue';
import { useTaskStore } from '@/stores/tasks';
import type { RecurrenceRule, Task } from '@/types/task';

const props = defineProps<{ task: Task }>();
const store = useTaskStore();
const frequency = ref<RecurrenceRule['frequency'] | ''>('');
const interval = ref(1);
const weekdays = ref<number[]>([]);
const endDate = ref<string | null>(null);
const reminderDate = ref<string | null>(null);
const reminderTime = ref<string | null>(null);
const dueDate = ref<string | null>(null);
const dueTime = ref<string | null>(null);
const saving = ref(false);
const status = ref('');
const deviceToday = new Intl.DateTimeFormat('sv-SE').format(new Date());
const dayNames = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];

function deviceWallTime(date: Date) {
    return new Date(date.getTime() - date.getTimezoneOffset() * 60_000).toISOString().slice(0, 16);
}

watch(() => props.task, task => {
    frequency.value = task.recurrence_rule?.frequency ?? '';
    interval.value = task.recurrence_rule?.interval ?? 1;
    weekdays.value = task.recurrence_rule?.by_weekday ?? [];
    endDate.value = task.recurrence_rule?.end_date ?? null;
    dueDate.value = task.due_date ?? null;
    dueTime.value = task.due_time?.slice(0, 5) ?? null;
    const localReminder = task.reminder_at ? deviceWallTime(new Date(task.reminder_at)) : '';
    reminderDate.value = localReminder.slice(0, 10) || null;
    reminderTime.value = localReminder.slice(11) || null;
}, { immediate: true });

async function save() {
    if (saving.value) return;
    if (frequency.value && !dueDate.value) {
        status.value = 'برای تکرار، تاریخ سررسید را تعیین کنید.';
        return;
    }
    if (Boolean(reminderDate.value) !== Boolean(reminderTime.value)) {
        status.value = 'برای یادآور، تاریخ و ساعت را با هم تعیین یا پاک کنید.';
        return;
    }
    if (frequency.value === 'custom' && !weekdays.value.length) {
        status.value = 'حداقل یک روز هفته را انتخاب کنید.';
        return;
    }

    const wallTime = reminderDate.value && reminderTime.value ? `${reminderDate.value}T${reminderTime.value}` : null;
    const storedWallTime = props.task.reminder_at ? deviceWallTime(new Date(props.task.reminder_at)) : null;
    let reminderUpdate: string | null | undefined;
    if (wallTime !== storedWallTime) {
        const nextReminder = wallTime ? new Date(wallTime) : null;
        if (nextReminder && (!Number.isFinite(nextReminder.getTime()) || deviceWallTime(nextReminder) !== wallTime)) {
            status.value = 'این ساعت در تاریخ انتخاب‌شده وجود ندارد. ساعت دیگری را انتخاب کنید.';
            return;
        }
        reminderUpdate = nextReminder?.toISOString().replace('.000Z', '+00:00') ?? null;
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
            ...(reminderUpdate !== undefined ? { reminder_at: reminderUpdate } : {}),
        });
        status.value = updated ? 'زمان‌بندی ذخیره شد.' : 'ذخیرهٔ زمان‌بندی ممکن نشد.';
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <details class="rounded-xl border bg-surface-subtleLight/60 p-4 text-sm dark:bg-surface-subtleDark/40">
        <summary class="cursor-pointer font-semibold">زمان‌بندی، تکرار و یادآور</summary>
        <form class="mt-4 grid gap-3 sm:grid-cols-2" @submit.prevent="save">
            <CalendarField v-model="dueDate" label="تاریخ سررسید" />
            <ClockField v-model="dueTime" label="ساعت سررسید" />
            <label>تکرار
                <select v-model="frequency" class="task-field mt-1.5">
                    <option value="">بدون تکرار</option>
                    <option value="daily">روزانه</option><option value="weekdays">دوشنبه تا جمعه</option>
                    <option value="weekly">هفتگی</option><option value="monthly">ماهانه</option>
                    <option value="yearly">سالانه</option><option value="custom">روزهای انتخابی هفته</option>
                </select>
            </label>
            <template v-if="frequency">
                <label>فاصلهٔ تکرار <input v-model.number="interval" type="number" required min="1" max="365" class="task-field mt-1.5" /></label>
                <fieldset v-if="frequency === 'custom'" class="flex flex-wrap gap-2 sm:col-span-2">
                    <legend>روزهای هفته</legend>
                    <label v-for="(day, index) in dayNames" :key="day">
                        <input v-model="weekdays" type="checkbox" :value="index" /> {{ day }}
                    </label>
                </fieldset>
                <CalendarField v-model="endDate" label="پایان تکرار" />
            </template>
            <CalendarField v-model="reminderDate" label="تاریخ یادآور" :today="deviceToday" />
            <ClockField v-model="reminderTime" label="ساعت یادآور (به ساعت دستگاه)" />
            <button type="submit" :disabled="saving" class="justify-self-start rounded-lg border border-brand-500 px-3 py-2">ذخیرهٔ زمان‌بندی</button>
            <p v-if="status" role="status" class="sm:col-span-2">{{ status }}</p>
        </form>
    </details>
</template>
