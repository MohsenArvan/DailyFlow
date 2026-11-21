<script setup lang="ts">
import { onMounted, computed } from 'vue';
import { RouterView, useRoute } from 'vue-router';
import AppNavbar from '@/components/layout/AppNavbar.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { wallpapers } from '@/wallpapers';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const authStore = useAuthStore();

const wallpaper = computed(() => wallpapers.find(item => item.id === (authStore.userPreferences?.wallpaper ?? 'lake'))?.image);

const showNavbar = computed(() => {
    return !authStore.isAuthenticated || route.path === '/login' || route.path === '/register';
});

onMounted(async () => {
    await authStore.initialize();
});
</script>

<template>
    <div :class="{ 'has-wallpaper': wallpaper }" class="isolate min-h-screen flex flex-col bg-surface-light dark:bg-surface-dark text-zinc-900 dark:text-zinc-100 transition-colors duration-200 font-vazir">
        <div v-if="wallpaper" class="wallpaper-layer pointer-events-none fixed inset-0 -z-10" aria-hidden="true"
            :style="{ backgroundImage: `url(${wallpaper})` }"></div>
        <AppNavbar v-if="showNavbar" />
        <component :is="showNavbar && route.path !== '/' ? 'main' : 'div'" class="flex flex-1 flex-col">
            <RouterView />
        </component>
        <ToastContainer />
    </div>
</template>
