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

// Response interceptor to handle unauthenticated sessions gracefully
api.interceptors.response.use(
    (response) => response,
    (error: AxiosError) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('dailyflow_token');
            if (window.location.pathname !== '/login' && window.location.pathname !== '/register') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

// Helper to ensure CSRF cookie is set before mutative requests
export async function getCsrfCookie(): Promise<void> {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    } catch (err) {
        console.warn('Could not fetch CSRF cookie:', err);
    }
}

export default api;
