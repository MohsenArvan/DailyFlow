import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

export function useAgendaDates() {
    const auth = useAuthStore();
    const timezone = computed(() => auth.user?.timezone || 'UTC');

    function localDate(date = new Date()) {
        return new Intl.DateTimeFormat('sv-SE', { timeZone: timezone.value }).format(date);
    }

    function offsetDate(offset: number) {
        const day = new Date(localDate());
        day.setUTCDate(day.getUTCDate() + offset);
        return day.toISOString().slice(0, 10);
    }

    function formatDay(date: string) {
        return new Intl.DateTimeFormat(
            auth.userPreferences?.date_format === 'gregorian' ? 'fa-IR-u-ca-gregory' : 'fa-IR',
            { weekday: 'long', month: 'long', day: 'numeric', timeZone: 'UTC' },
        ).format(new Date(date));
    }

    return { timezone, localDate, offsetDate, formatDay };
}
