import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'home',
        component: () => import('@/views/HomeView.vue'),
        meta: { title: 'خانه' },
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/auth/LoginView.vue'),
        meta: { guestOnly: true, title: 'ورود' },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/auth/RegisterView.vue'),
        meta: { guestOnly: true, title: 'ثبت‌نام' },
    },
    {
        path: '/settings',
        name: 'settings',
        component: () => import('@/views/SettingsView.vue'),
        meta: { requiresAuth: true, title: 'تنظیمات' },
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to, _from, next) => {
    const authStore = useAuthStore();

    if (!authStore.isInitialized) {
        await authStore.initialize();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return next({
            name: 'login',
            query: { redirect: to.fullPath },
        });
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
        return next({ name: 'home' });
    }

    next();
});

router.afterEach((to) => {
    const title = to.meta.title ? `${to.meta.title} • DailyFlow` : 'DailyFlow';
    document.title = title;
});

export default router;
