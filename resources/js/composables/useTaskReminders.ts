import { onMounted, onUnmounted } from 'vue';
import api from '@/services/api';
import { useToast } from './useToast';

interface Reminder {
    id: string;
    data: { title: string };
}

const shown = new Set<string>();

export function useTaskReminders() {
    const toast = useToast();
    let timer: ReturnType<typeof setInterval>;
    let stopped = false;
    let polling = false;

    async function poll() {
        if (polling) return;
        polling = true;
        try {
            const response = await api.get<{ data: Reminder[] }>('/notifications/unread');
            if (stopped) return;
            for (const { id, data } of response.data.data) {
                if (!shown.has(id)) {
                    shown.add(id);
                    toast.info('یادآور: ' + data.title);
                    if ('Notification' in window && Notification.permission === 'granted') {
                        try {
                            new Notification('DailyFlow', { body: data.title, tag: id });
                        } catch {
                            // In-app notification remains available on unsupported devices.
                        }
                    }
                }
                await api.patch('/notifications/' + id + '/read');
                shown.delete(id);
            }
        } catch {
            // The next poll retries unread reminders.
        } finally {
            polling = false;
        }
    }

    onMounted(() => {
        void poll();
        timer = setInterval(poll, 30_000);
    });
    onUnmounted(() => {
        stopped = true;
        clearInterval(timer);
    });
}
