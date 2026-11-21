import { ref } from 'vue';

export interface Toast {
    id: string;
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
    duration?: number;
}

const toasts = ref<Toast[]>([]);

export function useToast() {
    const add = (message: string, type: Toast['type'] = 'info', duration: number = 4000) => {
        const id = Math.random().toString(36).substring(2, 9);
        const toastItem: Toast = { id, type, message, duration };
        toasts.value.push(toastItem);

        if (duration > 0) {
            setTimeout(() => {
                remove(id);
            }, duration);
        }

        return id;
    };

    const remove = (id: string) => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    };

    const success = (message: string, duration?: number) => add(message, 'success', duration);
    const error = (message: string, duration?: number) => add(message, 'error', duration);
    const warning = (message: string, duration?: number) => add(message, 'warning', duration);
    const info = (message: string, duration?: number) => add(message, 'info', duration);

    return {
        toasts,
        add,
        remove,
        success,
        error,
        warning,
        info,
    };
}
