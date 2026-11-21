<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { RouterView, useRoute } from 'vue-router';
import AppNavbar from '@/components/layout/AppNavbar.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const authStore = useAuthStore();

const showNavbar = computed(() => {
    return !authStore.isAuthenticated || route.path === '/login' || route.path === '/register';
});

onMounted(async () => {
    await authStore.initialize();
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-surface-light dark:bg-surface-dark text-zinc-900 dark:text-zinc-100 transition-colors duration-200 font-vazir">
        <AppNavbar v-if="showNavbar" />
        <component :is="showNavbar && route.path !== '/' ? 'main' : 'div'" class="flex flex-1 flex-col">
            <RouterView />
        </component>
        <ToastContainer />
    </div>
</template>
