import { createPinia, setActivePinia } from 'pinia';
import { expect, it, vi } from 'vitest';
import api from '@/services/api';
import { useDashboardStore } from '@/stores/dashboard';
import { useAuthStore } from '@/stores/auth';
import type { User } from '@/types/auth';

vi.mock('@/services/api', () => ({ default: { get: vi.fn() } }));
vi.stubGlobal('localStorage', { getItem: () => null });

it('caches dashboard requests and clears data when the session changes', async () => {
    setActivePinia(createPinia());
    const auth = useAuthStore();
    auth.user = { id: 1, timezone: 'UTC' } as User;
    const store = useDashboardStore();
    vi.mocked(api.get).mockResolvedValue({ data: { data: { completed_today: 1 } } });

    await store.load();
    await store.load();
    expect(api.get).toHaveBeenCalledTimes(2);
    expect(store.summary?.completed_today).toBe(1);

    store.invalidate();
    await store.load();
    expect(api.get).toHaveBeenCalledTimes(4);

    auth.user = null;
    expect(store.summary).toBeNull();
    expect(store.velocity).toBeNull();
});
