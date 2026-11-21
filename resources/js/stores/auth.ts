import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api, { getCsrfCookie } from '@/services/api';
import type {
    User,
    LoginCredentials,
    RegisterData,
    UpdateProfileData,
    UpdatePreferencesData,
    UpdatePasswordData,
    AuthResponse,
} from '@/types/auth';

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);
    const token = ref<string | null>(localStorage.getItem('dailyflow_token'));
    const isLoading = ref<boolean>(false);
    const isInitialized = ref<boolean>(false);
    let initPromise: Promise<void> | null = null;

    const isAuthenticated = computed(() => !!user.value);
    const userPreferences = computed(() => user.value?.preferences);

    function applyTheme(theme?: 'dark' | 'light' | 'system') {
        const selected = theme || user.value?.preferences?.theme || 'dark';
        if (selected === 'dark' || (selected === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    async function initialize(): Promise<void> {
        if (isInitialized.value) return;
        if (initPromise) return initPromise;

        initPromise = (async () => {
            isLoading.value = true;
            try {
                const response = await api.get<{ user: User }>('/auth/me');
                user.value = response.data.user;
                applyTheme(user.value.preferences?.theme);
            } catch {
                user.value = null;
                token.value = null;
                localStorage.removeItem('dailyflow_token');
            } finally {
                isLoading.value = false;
                isInitialized.value = true;
                initPromise = null;
            }
        })();

        return initPromise;
    }

    async function login(credentials: LoginCredentials): Promise<User> {
        isLoading.value = true;
        try {
            await getCsrfCookie();
            const response = await api.post<AuthResponse>('/auth/login', credentials);
            user.value = response.data.user;
            if (response.data.token) {
                token.value = response.data.token;
                localStorage.setItem('dailyflow_token', response.data.token);
            }
            applyTheme(user.value.preferences?.theme);
            return user.value;
        } finally {
            isLoading.value = false;
        }
    }

    async function register(data: RegisterData): Promise<User> {
        isLoading.value = true;
        try {
            await getCsrfCookie();
            const response = await api.post<AuthResponse>('/auth/register', data);
            user.value = response.data.user;
            if (response.data.token) {
                token.value = response.data.token;
                localStorage.setItem('dailyflow_token', response.data.token);
            }
            applyTheme(user.value.preferences?.theme);
            return user.value;
        } finally {
            isLoading.value = false;
        }
    }

    async function logout(): Promise<void> {
        isLoading.value = true;
        try {
            await api.post('/auth/logout');
        } catch (err) {
            console.warn('Logout API error:', err);
        } finally {
            user.value = null;
            token.value = null;
            localStorage.removeItem('dailyflow_token');
            isLoading.value = false;
        }
    }

    async function updateProfile(data: UpdateProfileData): Promise<User> {
        isLoading.value = true;
        try {
            const response = await api.put<{ message: string; user: User }>('/user/profile', data);
            user.value = response.data.user;
            return user.value;
        } finally {
            isLoading.value = false;
        }
    }

    async function updatePreferences(data: UpdatePreferencesData): Promise<User> {
        isLoading.value = true;
        try {
            const response = await api.put<{ message: string; user: User }>('/user/preferences', data);
            user.value = response.data.user;
            if (data.theme) {
                applyTheme(data.theme);
            }
            return user.value;
        } finally {
            isLoading.value = false;
        }
    }

    async function updatePassword(data: UpdatePasswordData): Promise<void> {
        isLoading.value = true;
        try {
            await api.put('/user/password', data);
        } finally {
            isLoading.value = false;
        }
    }

    return {
        user,
        token,
        isLoading,
        isInitialized,
        isAuthenticated,
        userPreferences,
        initialize,
        login,
        register,
        logout,
        updateProfile,
        updatePreferences,
        updatePassword,
        applyTheme,
    };
});
