import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import api from '@/services/api';
import { useAuthStore } from './auth';
import type { DashboardSummary, Velocity } from '@/types/dashboard';

export const useDashboardStore = defineStore('dashboard', () => {
    const auth = useAuthStore();
    const summary = ref<DashboardSummary | null>(null);
    const velocity = ref<Velocity | null>(null);
    const loading = ref(false);
    const error = ref(false);
    let generation = 0;
    let expires = 0;

    function invalidate() {
        generation++;
        expires = 0;
        loading.value = false;
    }

    watch(() => [auth.user?.id, auth.user?.timezone, auth.userPreferences?.daily_goal], () => {
        invalidate();
        summary.value = null;
        velocity.value = null;
        error.value = false;
    }, { flush: 'sync' });

    async function load() {
        if (!auth.user || loading.value || Date.now() < expires) return;

        const request = ++generation;
        loading.value = true;
        error.value = false;

        try {
            const [details, activity] = await Promise.all([
                api.get<{ data: DashboardSummary }>('/dashboard/summary'),
                api.get<{ data: Velocity }>('/dashboard/velocity'),
            ]);
            if (generation !== request) return;
            summary.value = details.data.data;
            velocity.value = activity.data.data;
            expires = Date.now() + 30_000;
        } catch {
            if (generation === request) error.value = true;
        } finally {
            if (generation === request) loading.value = false;
        }
    }

    return { summary, velocity, loading, error, invalidate, load };
});
