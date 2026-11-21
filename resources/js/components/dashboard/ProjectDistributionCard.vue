<script setup lang="ts">
import type { DashboardSummary } from '@/types/dashboard';

defineProps<{ projects: DashboardSummary['project_distribution'] }>();
</script>

<template>
    <section>
        <h2 class="mb-3 font-semibold">کارهای باز پروژه‌ها</h2>
        <p v-if="!projects.length" class="text-sm text-zinc-600 dark:text-zinc-300">هنوز پروژه‌ای ندارید.</p>
        <ul v-else class="divide-y divide-zinc-200 dark:divide-zinc-700">
            <li v-for="project in projects" :key="project.id">
                <RouterLink :to="{ name: 'project-detail', params: { uuid: project.id } }" class="flex justify-between gap-3 rounded py-2 text-sm hover:underline">
                    <span>{{ project.name }}</span><strong>{{ project.count }}</strong>
                </RouterLink>
            </li>
        </ul>
    </section>
</template>
