<script setup lang="ts">
import { computed, nextTick, ref, useId } from 'vue';
import { Check, Clock3, Minus, Plus, X } from 'lucide-vue-next';

const props = defineProps<{ modelValue: string | null; label: string }>();
const emit = defineEmits<{ 'update:modelValue': [value: string | null] }>();

const trigger = ref<HTMLButtonElement>();
const panel = ref<HTMLElement>();
const minuteButton = ref<HTMLButtonElement>();
const panelId = useId();
const open = ref(false);
const unit = ref<'hour' | 'minute'>('hour');
const hour = ref(9);
const minute = ref(0);
const digits = new Intl.NumberFormat('fa-IR', { minimumIntegerDigits: 2 });
const savedLabel = computed(() => props.modelValue?.slice(0, 5).split(':').map(value => digits.format(Number(value))).join(':'));
const secondHalf = computed(() => hour.value >= 12);
const dialValue = computed(() => unit.value === 'hour' ? hour.value : minute.value);
const angle = computed(() => unit.value === 'hour' ? (hour.value % 12) * 30 : minute.value * 6);
const marks = computed(() => Array.from({ length: 12 }, (_, index) => {
    const radians = index * Math.PI / 6;
    return {
        value: unit.value === 'minute' ? index * 5 : index + (secondHalf.value ? 12 : 0),
        left: `${50 + Math.sin(radians) * 39}%`,
        top: `${50 - Math.cos(radians) * 39}%`,
    };
}));

async function show() {
    if (panel.value?.matches(':popover-open')) {
        close();
        return;
    }

    const [savedHour, savedMinute] = props.modelValue?.split(':').map(Number) ?? [9, 0];
    hour.value = savedHour;
    minute.value = savedMinute;
    unit.value = 'hour';
    panel.value?.showPopover();
    await nextTick();
    panel.value?.querySelector<HTMLButtonElement>('[data-active-mark]')?.focus();
}

function close() {
    panel.value?.hidePopover();
    trigger.value?.focus();
}

function handleToggle(event: Event) {
    open.value = (event as ToggleEvent).newState === 'open';
}

function pick(value: number) {
    if (unit.value === 'minute') {
        minute.value = value;
        return;
    }

    hour.value = value;
    unit.value = 'minute';
    void nextTick(() => minuteButton.value?.focus());
}

function confirm() {
    const time = [hour.value, minute.value].map(value => String(value).padStart(2, '0')).join(':');
    emit('update:modelValue', time);
    close();
}

function clear() {
    emit('update:modelValue', null);
    close();
}
</script>

<template>
    <div class="min-w-0">
        <span class="task-field-label">{{ label }}</span>
        <button ref="trigger" type="button" :aria-label="label" :aria-expanded="open" :aria-controls="panelId"
            :aria-describedby="panelId + '-value'"
            class="group flex min-h-12 w-full items-center gap-3 rounded-xl border bg-brand-50 px-2.5 py-1.5 text-start text-sm font-semibold text-brand-900 hover:border-brand-400 dark:bg-brand-950/70 dark:text-brand-200"
            @click="show">
            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-white text-brand-700 dark:bg-brand-900 dark:text-brand-200">
                <Clock3 aria-hidden="true" class="size-4" />
            </span>
            <bdi v-if="savedLabel" :id="panelId + '-value'" dir="ltr" class="text-base font-extrabold tabular-nums">{{ savedLabel }}</bdi>
            <span v-else :id="panelId + '-value'">انتخاب زمان</span>
        </button>

        <section :id="panelId" ref="panel" popover="auto" :aria-label="`انتخاب ${label}`"
            class="workspace-panel fixed inset-0 m-auto max-h-[calc(100dvh-1.25rem)] w-[min(19rem,calc(100vw-1.25rem))] overflow-y-auto overscroll-contain p-4 text-zinc-900 shadow-modal dark:text-zinc-100"
            @toggle="handleToggle" @keydown.esc.stop.prevent="close">
            <header class="mb-3 flex items-center justify-between gap-2">
                <h3 class="text-sm font-bold">{{ label }}</h3>
                <button type="button" aria-label="بستن انتخاب زمان" class="grid size-8 shrink-0 place-items-center rounded-lg hover:bg-brand-50 dark:hover:bg-brand-950" @click="close">
                    <X aria-hidden="true" class="size-4" />
                </button>
            </header>

            <div class="flex items-stretch justify-center gap-2 rounded-xl bg-brand-50 p-2 text-brand-900 tabular-nums dark:bg-brand-950 dark:text-brand-200" dir="ltr">
                <button type="button" aria-label="انتخاب ساعت" :aria-pressed="unit === 'hour'"
                    class="rounded-lg px-3 py-1 text-2xl font-extrabold aria-pressed:bg-white aria-pressed:shadow-sm dark:aria-pressed:bg-brand-800"
                    @click="unit = 'hour'">{{ digits.format(hour) }}</button>
                <span aria-hidden="true" class="self-center text-lg font-bold">:</span>
                <button ref="minuteButton" type="button" aria-label="انتخاب دقیقه" :aria-pressed="unit === 'minute'"
                    class="rounded-lg px-3 py-1 text-2xl font-extrabold aria-pressed:bg-white aria-pressed:shadow-sm dark:aria-pressed:bg-brand-800"
                    @click="unit = 'minute'">{{ digits.format(minute) }}</button>
            </div>

            <div class="relative mx-auto my-3 aspect-square w-full max-w-52 rounded-full border border-brand-200 bg-brand-50/60 dark:border-brand-800 dark:bg-brand-950/80" dir="ltr">
                <span aria-hidden="true" class="absolute bottom-1/2 left-1/2 h-[39%] w-0.5 origin-bottom rounded-full bg-brand-500/70"
                    :style="{ transform: `translateX(-50%) rotate(${angle}deg)` }"></span>
                <span aria-hidden="true" class="absolute left-1/2 top-1/2 size-2 -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand-600 dark:bg-brand-300"></span>
                <button v-for="mark in marks" :key="mark.value" type="button"
                    :aria-label="`${unit === 'hour' ? 'ساعت' : 'دقیقه'} ${digits.format(mark.value)}`"
                    :aria-pressed="mark.value === dialValue" :data-active-mark="mark.value === dialValue ? '' : null"
                    class="absolute grid size-8 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full text-xs font-bold text-brand-900 hover:bg-brand-200 aria-pressed:bg-brand-700 aria-pressed:text-white dark:text-brand-100 dark:hover:bg-brand-800"
                    :style="{ left: mark.left, top: mark.top }" @click="pick(mark.value)">
                    {{ digits.format(mark.value) }}
                </button>
            </div>

            <div v-if="unit === 'hour'" role="group" aria-label="بازهٔ ساعت" class="segmented-control flex justify-center">
                <button type="button" :aria-pressed="!secondHalf" @click="hour %= 12">۰۰ تا ۱۱</button>
                <button type="button" :aria-pressed="secondHalf" @click="hour = hour % 12 + 12">۱۲ تا ۲۳</button>
            </div>
            <div v-else class="flex items-center justify-center gap-3 text-xs font-semibold">
                <button type="button" aria-label="یک دقیقه کمتر" class="grid size-9 place-items-center rounded-lg border bg-brand-50 dark:bg-brand-950" @click="minute = (minute + 59) % 60">
                    <Minus aria-hidden="true" class="size-4" />
                </button>
                <output class="min-w-14 text-center" aria-live="polite">{{ digits.format(minute) }} دقیقه</output>
                <button type="button" aria-label="یک دقیقه بیشتر" class="grid size-9 place-items-center rounded-lg border bg-brand-50 dark:bg-brand-950" @click="minute = (minute + 1) % 60">
                    <Plus aria-hidden="true" class="size-4" />
                </button>
            </div>

            <footer class="mt-4 flex justify-between gap-2 border-t pt-3">
                <button type="button" class="rounded-lg px-2 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-brand-50 dark:text-zinc-300 dark:hover:bg-brand-950"
                    @click="modelValue ? clear() : close()">{{ modelValue ? 'پاک کردن' : 'انصراف' }}</button>
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-brand-700 px-3 py-2 text-xs font-bold text-white hover:bg-brand-800" @click="confirm">
                    <Check aria-hidden="true" class="size-4" /> تأیید زمان
                </button>
            </footer>
        </section>
    </div>
</template>
