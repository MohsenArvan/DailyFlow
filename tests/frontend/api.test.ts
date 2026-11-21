import { AxiosError, type AxiosResponse } from 'axios';
import { afterEach, expect, it, vi } from 'vitest';
import api from '@/services/api';

afterEach(() => vi.unstubAllGlobals());

it('clears the saved bearer token and redirects on a 401', async () => {
    const location = { pathname: '/dashboard', href: '/dashboard' };
    const removeItem = vi.fn();
    vi.stubGlobal('localStorage', { getItem: () => 'test-token', removeItem });
    vi.stubGlobal('window', { location });
    const adapter = vi.fn(config => {
        return Promise.reject(new AxiosError('Unauthorized', undefined, config, undefined, { status: 401 } as AxiosResponse));
    });

    await expect(api.get('/tasks', { adapter })).rejects.toThrow('Unauthorized');

    expect(adapter.mock.calls[0][0].headers.get('Authorization')).toBe('Bearer test-token');
    expect(removeItem).toHaveBeenCalledWith('dailyflow_token');
    expect(location.href).toBe('/login');
});
