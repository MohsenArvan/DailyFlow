<script setup lang="ts">
import { CalendarDays, Columns3, ChartNoAxesColumnIncreasing, CheckCircle2 } from 'lucide-vue-next';

const features = [
    { icon: CalendarDays, title: 'برنامهٔ روزانه', description: 'کارهای امروز و موعدهای پیش‌رو را در یک نگاه ببینید.' },
    { icon: Columns3, title: 'پروژه‌ها و کانبان', description: 'کارها را گروه‌بندی کنید و پیشرفت هر پروژه را دنبال کنید.' },
    { icon: ChartNoAxesColumnIncreasing, title: 'داشبورد بهره‌وری', description: 'فعالیت هفتگی و پیشرفت نسبت به هدف روزانه را بررسی کنید.' },
];
</script>

<template>
    <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-8 sm:py-14">
        <section class="workspace-hero px-6 py-10 sm:px-12 sm:py-16">
            <div class="mb-6 flex items-center gap-2.5 font-bold" dir="ltr">
                <CheckCircle2 class="size-7 text-brand-700 dark:text-brand-300" aria-hidden="true" />
                <span>DailyFlow</span>
            </div>
            <h1 class="max-w-2xl text-3xl font-extrabold leading-snug sm:text-5xl">کارهای روزانه‌ات را یک‌جا مدیریت کن</h1>
            <p class="mt-4 max-w-lg leading-relaxed text-zinc-700 dark:text-zinc-300">برنامهٔ امروزت را مرتب کن، برای پروژه‌ها قدم بعدی را مشخص کن و پیشرفتت را ببین.</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <RouterLink to="/register" class="rounded-xl bg-brand-700 px-5 py-2.5 font-semibold text-white hover:bg-brand-800">ساخت حساب</RouterLink>
                <RouterLink to="/login" class="rounded-xl border bg-surface-cardLight px-5 py-2.5 font-semibold dark:bg-surface-cardDark">ورود به حساب</RouterLink>
            </div>
        </section>
        <ul class="mt-5 grid gap-3 sm:grid-cols-3">
            <li v-for="feature in features" :key="feature.title" class="workspace-panel p-5">
                <component :is="feature.icon" class="mb-3 size-5 text-brand-700 dark:text-brand-300" aria-hidden="true" />
                <h2 class="font-bold">{{ feature.title }}</h2>
                <p class="mt-1 text-sm leading-relaxed text-zinc-700 dark:text-zinc-300">{{ feature.description }}</p>
            </li>
        </ul>
    </main>
</template>
