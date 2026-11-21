import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import api from '@/services/api';
import { useTaskStore } from '@/stores/tasks';

vi.mock('@/services/api', () => ({ default: { get: vi.fn() } }));
vi.stubGlobal('localStorage', { getItem: () => null });

describe('task views', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.mocked(api.get).mockReset();
    });

    it.each(['today', 'upcoming', 'completed'])('requests the %s view and its counters', async view => {
        const task = { title: 'Review', status: view === 'completed' ? 'completed' : 'pending' };
        vi.mocked(api.get).mockResolvedValueOnce({ data: { data: [task] } })
            .mockResolvedValueOnce({ data: { counts: { [view]: 1 } } });

        const store = useTaskStore();
        await store.fetchTasks(view);

        expect(api.get).toHaveBeenNthCalledWith(1, '/tasks', { params: { view } });
        expect(api.get).toHaveBeenNthCalledWith(2, '/tasks/counts');
        expect(store.activeView).toBe(view);
        expect(store.tasks).toEqual([task]);
        expect(store.counts).toEqual({ [view]: 1 });
    });
});
