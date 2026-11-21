import { describe, expect, it } from 'vitest';
import { calendarMonth, moveCalendarMonth } from '@/utils/calendar';

describe('Persian scheduling calendar', () => {
    it('starts Farvardin on Nowruz and aligns the grid to Saturday', () => {
        const month = calendarMonth('2025-03-21', 'persian', 6);
        expect(month.first).toBe('2025-03-21');
        expect(month.days).toHaveLength(31);
        expect(month.leading).toBe(6);
        expect(month.days[0]).toEqual({ iso: '2025-03-21', number: 1 });
    });

    it('navigates between Esfand and Farvardin across leap years', () => {
        expect(calendarMonth('2025-03-20', 'persian', 6).days).toHaveLength(30);
        expect(moveCalendarMonth('2025-03-20', 'persian', 1)).toBe('2025-03-21');
        expect(moveCalendarMonth('2025-03-21', 'persian', -1)).toBe('2025-02-19');
        expect(calendarMonth('2024-03-19', 'persian', 6).days).toHaveLength(29);
    });

    it('preserves the ISO dates when the calendar preference is Gregorian', () => {
        expect(calendarMonth('2024-02-29', 'gregory', 1).days.at(-1)?.iso).toBe('2024-02-29');
        expect(moveCalendarMonth('2024-02-29', 'gregory', 1)).toBe('2024-03-01');
    });
});
