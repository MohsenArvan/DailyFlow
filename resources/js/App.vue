<script setup lang="ts">
import { onMounted } from 'vue';
import { RouterView } from 'vue-router';
import AppNavbar from '@/components/layout/AppNavbar.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

onMounted(async () => {
    await authStore.initialize();
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-surface-light dark:bg-surface-dark text-zinc-900 dark:text-zinc-100 transition-colors duration-200">
        <AppNavbar />
        <main class="flex-1 flex flex-col">
            <RouterView />
        </main>
    </div>
</template>
