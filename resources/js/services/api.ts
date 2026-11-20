import axios, { AxiosInstance, AxiosError, InternalAxiosRequestConfig } from 'axios';

const api: AxiosInstance = axios.create({
    baseURL: '/api/v1',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true,
    withXSRFToken: true,
});

// Request interceptor to attach bearer token if available
api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('dailyflow_token');
    if (token && config.headers) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Helper to ensure CSRF cookie is set before mutative requests
export async function getCsrfCookie(): Promise<void> {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    } catch (err) {
        console.warn('Could not fetch CSRF cookie:', err);
    }
}

export default api;
