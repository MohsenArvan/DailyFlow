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
        path: '/today',
        name: 'today',
        component: () => import('@/views/TodayView.vue'),
        meta: { requiresAuth: true, title: 'امروز' },
    },
    {
        path: '/inbox',
        name: 'inbox',
        component: () => import('@/views/InboxView.vue'),
        meta: { requiresAuth: true, title: 'صندوق ورودی' },
    },
    {
        path: '/upcoming',
        name: 'upcoming',
        component: () => import('@/views/UpcomingView.vue'),
        meta: { requiresAuth: true, title: 'به‌زودی' },
    },
    {
        path: '/overdue',
        name: 'overdue',
        component: () => import('@/views/OverdueView.vue'),
        meta: { requiresAuth: true, title: 'عقب‌افتاده' },
    },
    {
        path: '/completed',
        name: 'completed',
        component: () => import('@/views/CompletedView.vue'),
        meta: { requiresAuth: true, title: 'انجام‌شده' },
    },
    {
        path: '/projects',
        name: 'projects',
        component: () => import('@/views/ProjectsView.vue'),
        meta: { requiresAuth: true, title: 'پروژه‌ها' },
    },
    {
        path: '/projects/:uuid',
        name: 'project-detail',
        component: () => import('@/views/ProjectDetailView.vue'),
        meta: { requiresAuth: true, title: 'پروژه' },
    },
    {
        path: '/settings',
        name: 'settings',
        component: () => import('@/views/SettingsView.vue'),
        meta: { requiresAuth: true, title: 'تنظیمات' },
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
        return next({ name: 'today' });
    }

    if (to.path === '/' && authStore.isAuthenticated) {
        return next({ name: 'today' });
    }

    next();
});

router.afterEach((to) => {
    const title = to.meta.title ? `${to.meta.title} • DailyFlow` : 'DailyFlow';
    document.title = title;
});

export default router;
