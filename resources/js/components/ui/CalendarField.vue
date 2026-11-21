<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, useId } from 'vue';
import { CalendarDays, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { useAuthStore } from '@/stores/auth';
import { useAgendaDates } from '@/composables/useAgendaDates';
import { calendarMonth, moveCalendarMonth, shiftCalendarDay } from '@/utils/calendar';

const props = defineProps<{ modelValue: string | null; label: string; today?: string }>();
const emit = defineEmits<{ 'update:modelValue': [value: string | null] }>();

const auth = useAuthStore();
const { localDate } = useAgendaDates();
const system = computed(() => auth.userPreferences?.date_format === 'gregorian' ? 'gregory' : 'persian');
const weekStart = computed(() => auth.userPreferences?.start_of_week ?? 6);
const locale = computed(() => 'fa-IR-u-ca-' + system.value);
const today = () => props.today ?? localDate();
const visible = ref(false);
const cursor = ref('');
const trigger = ref<HTMLButtonElement>();
const panel = ref<HTMLElement>();
const panelId = useId();
const digits = new Intl.NumberFormat('fa-IR');
const names = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];
const shortNames = ['ی', 'د', 'س', 'چ', 'پ', 'ج', 'ش'];
const weekdays = computed(() => Array.from({ length: 7 }, (_, index) => (weekStart.value + index) % 7));
const month = computed(() => calendarMonth(cursor.value || props.modelValue || today(), system.value, weekStart.value));
const cells = computed(() => [...Array<null>(month.value.leading).fill(null), ...month.value.days]);

function format(date: string, options: Intl.DateTimeFormatOptions) {
    return new Intl.DateTimeFormat(locale.value, { ...options, timeZone: 'UTC' }).format(new Date(date));
}

const monthTitle = computed(() => format(month.value.first, { month: 'long', year: 'numeric' }));
const selectedLabel = computed(() => props.modelValue ? format(props.modelValue, { dateStyle: 'medium' }) : 'انتخاب تاریخ');
const accessibleDate = computed(() => new Intl.DateTimeFormat(locale.value, { dateStyle: 'full', timeZone: 'UTC' }));

async function focusDay(iso: string) {
    cursor.value = iso;
    await nextTick();
    position();
    panel.value?.querySelector<HTMLButtonElement>(`[data-date="${iso}"]`)?.focus();
}

function position() {
    const element = panel.value;
    const anchor = trigger.value?.getBoundingClientRect();
    if (!element || !anchor) return;

    const margin = 10;
    const gap = 6;
    const width = Math.min(312, window.innerWidth - margin * 2);
    const left = Math.max(margin, Math.min(anchor.right - width, window.innerWidth - width - margin));
    const below = window.innerHeight - anchor.bottom - gap - margin;
    const above = anchor.top - gap - margin;
    element.style.width = `${width}px`;
    const height = element.scrollHeight + 2;
    const openAbove = below < height && above > below;
    const maxHeight = Math.max(0, openAbove ? above : below);
    const top = openAbove ? anchor.top - gap - Math.min(height, maxHeight) : anchor.bottom + gap;
    Object.assign(element.style, { left: `${left}px`, top: `${top}px`, maxHeight: `${maxHeight}px` });
}

function outside(event: PointerEvent) {
    const target = event.target as Node;
    if (!panel.value?.contains(target) && !trigger.value?.contains(target)) close(false);
}

function stopPositioning() {
    window.removeEventListener('resize', position);
    window.removeEventListener('scroll', repositionOnScroll, true);
    document.removeEventListener('pointerdown', outside, true);
}

function repositionOnScroll(event: Event) {
    if (event.target !== panel.value) position();
}

async function toggle() {
    if (visible.value) {
        close();
        return;
    }
    cursor.value = props.modelValue || today();
    visible.value = true;
    await nextTick();
    if (!visible.value || !panel.value) return;
    panel.value.showPopover();
    position();
    window.addEventListener('resize', position);
    window.addEventListener('scroll', repositionOnScroll, true);
    document.addEventListener('pointerdown', outside, true);
    void focusDay(cursor.value);
}

function close(restoreFocus = true) {
    stopPositioning();
    panel.value?.hidePopover();
    visible.value = false;
    if (restoreFocus) trigger.value?.focus();
}

onBeforeUnmount(stopPositioning);

function closeOnEscape(event: KeyboardEvent) {
    if (!visible.value) return;
    event.preventDefault();
    event.stopPropagation();
    close();
}

function pick(date: string | null) {
    emit('update:modelValue', date);
    close();
}

async function navigate(direction: -1 | 1) {
    cursor.value = moveCalendarMonth(cursor.value, system.value, direction);
    await nextTick();
    position();
}

function onDayKey(event: KeyboardEvent, iso: string) {
    const offsets: Record<string, number> = { ArrowRight: -1, ArrowLeft: 1, ArrowUp: -7, ArrowDown: 7 };
    const offset = offsets[event.key];
    if (offset === undefined) return;
    event.preventDefault();
    void focusDay(shiftCalendarDay(iso, offset));
}
</script>

<template>
    <div class="min-w-0">
        <span class="task-field-label">{{ label }}</span>
        <button ref="trigger" type="button" :aria-label="label" :aria-expanded="visible" :aria-controls="visible ? panelId : undefined" :aria-describedby="panelId + '-value'"
            class="task-field !flex items-center gap-2 text-start font-semibold" @click="toggle" @keydown.esc="closeOnEscape">
            <CalendarDays aria-hidden="true" class="size-4 shrink-0 text-brand-700 dark:text-brand-300" />
            <span :id="panelId + '-value'">{{ selectedLabel }}</span>
        </button>

        <section v-if="visible" :id="panelId" ref="panel" popover="manual" :aria-label="`تقویم ${label}`"
            class="workspace-panel fixed inset-auto m-0 select-none space-y-3 overflow-y-auto overscroll-contain p-2.5 text-zinc-900 shadow-modal dark:text-zinc-100 sm:p-3"
            @keydown.esc.stop.prevent="close()">
            <header class="flex items-center justify-between gap-1 rounded-xl bg-brand-50 px-1 py-1.5 dark:bg-brand-950">
                <button type="button" aria-label="ماه قبل" class="grid size-8 shrink-0 place-items-center rounded-lg hover:bg-brand-100 dark:hover:bg-brand-900" @click="navigate(-1)">
                    <ChevronRight aria-hidden="true" class="size-4" />
                </button>
                <span aria-live="polite" class="text-center text-xs font-extrabold text-brand-900 dark:text-brand-200 sm:text-sm">{{ monthTitle }}</span>
                <button type="button" aria-label="ماه بعد" class="grid size-8 shrink-0 place-items-center rounded-lg hover:bg-brand-100 dark:hover:bg-brand-900" @click="navigate(1)">
                    <ChevronLeft aria-hidden="true" class="size-4" />
                </button>
            </header>
            <div class="grid grid-cols-7 text-center">
                <abbr v-for="day in weekdays" :key="day" :title="names[day]" class="pb-2 text-xs font-semibold text-zinc-600 no-underline dark:text-zinc-400">{{ shortNames[day] }}</abbr>
                <template v-for="(day, index) in cells" :key="day?.iso ?? index">
                    <span v-if="!day" aria-hidden="true"></span>
                    <button v-else type="button" :data-date="day.iso" :aria-label="accessibleDate.format(new Date(day.iso))"
                        :aria-pressed="day.iso === modelValue" :aria-current="day.iso === today() ? 'date' : undefined"
                        :tabindex="day.iso === cursor ? 0 : -1"
                        class="my-px min-h-8 rounded-lg text-center text-xs font-medium hover:bg-brand-100 dark:hover:bg-brand-900"
                        :class="day.iso === modelValue ? 'bg-brand-700 !text-white hover:bg-brand-800 dark:bg-brand-700' : day.iso === today() ? 'font-bold text-brand-800 ring-1 ring-inset ring-brand-500 dark:text-brand-200' : ''"
                        @click="pick(day.iso)" @keydown="onDayKey($event, day.iso)">
                        {{ digits.format(day.number) }}
                    </button>
                </template>
            </div>
            <footer class="flex justify-between gap-2 border-t pt-2 text-xs font-semibold">
                <button type="button" class="rounded-lg px-2 py-1.5 text-brand-800 hover:bg-brand-50 dark:text-brand-200 dark:hover:bg-brand-950" @click="pick(today())">امروز</button>
                <button v-if="modelValue" type="button" class="rounded-lg px-2 py-1.5 text-zinc-600 hover:bg-surface-subtleLight dark:text-zinc-300 dark:hover:bg-surface-subtleDark" @click="pick(null)">پاک کردن</button>
            </footer>
        </section>
    </div>
</template>
