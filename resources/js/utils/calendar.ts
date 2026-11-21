export type CalendarSystem = 'persian' | 'gregory';

const dayMilliseconds = 86_400_000;

export function shiftCalendarDay(iso: string, offset: number) {
    return new Date(Date.parse(iso) + offset * dayMilliseconds).toISOString().slice(0, 10);
}

const dayFormatters = {
    persian: new Intl.DateTimeFormat('en-u-ca-persian', { day: 'numeric', timeZone: 'UTC' }),
    gregory: new Intl.DateTimeFormat('en-u-ca-gregory', { day: 'numeric', timeZone: 'UTC' }),
};

function dayNumber(iso: string, system: CalendarSystem) {
    return Number(dayFormatters[system].format(new Date(iso)));
}

export function calendarMonth(iso: string, system: CalendarSystem, weekStart: number) {
    const first = shiftCalendarDay(iso, 1 - dayNumber(iso, system));
    const leading = (new Date(first).getUTCDay() - weekStart + 7) % 7;
    const days = [{ iso: first, number: 1 }];

    for (let number = 2; number <= 31; number++) {
        const date = shiftCalendarDay(first, number - 1);
        if (dayNumber(date, system) === 1) break;
        days.push({ iso: date, number });
    }

    return { first, leading, days };
}

export function moveCalendarMonth(iso: string, system: CalendarSystem, direction: -1 | 1) {
    const { first, days } = calendarMonth(iso, system, 0);
    if (direction === 1) return shiftCalendarDay(first, days.length);

    const previous = shiftCalendarDay(first, -1);
    return shiftCalendarDay(previous, 1 - dayNumber(previous, system));
}
