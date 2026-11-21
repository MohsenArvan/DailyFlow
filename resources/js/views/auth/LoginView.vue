<script setup lang="ts">
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import {
    LogIn,
    Mail,
    Lock,
    Eye,
    EyeOff,
    AlertCircle,
    Loader2,
    ArrowLeft,
} from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const remember = ref(false);
const showPassword = ref(false);
const errorMessage = ref('');
const fieldErrors = ref<Record<string, string[]>>({});
const isLoading = ref(false);

const handleLogin = async () => {
    errorMessage.value = '';
    fieldErrors.value = {};

    if (!email.value.trim() || !password.value) {
        errorMessage.value = 'لطفاً ایمیل و کلمه عبور را وارد کنید.';
        return;
    }

    isLoading.value = true;
    try {
        await authStore.login({
            email: email.value.trim(),
            password: password.value,
            remember: remember.value,
        });

        const redirectPath = (route.query.redirect as string) || '/';
        router.push(redirectPath);
    } catch (err: any) {
        if (err.response?.status === 422) {
            fieldErrors.value = err.response.data?.errors || {};
            errorMessage.value = err.response.data?.message || 'اطلاعات وارد شده نامعتبر است.';
        } else if (err.response?.status === 401) {
            errorMessage.value = 'ایمیل یا کلمه عبور اشتباه است.';
        } else if (err.response?.status === 429) {
            errorMessage.value = 'درخواست‌های بیش از حد مجاز. لطفاً کمی بعد دوباره تلاش کنید.';
        } else {
            errorMessage.value = 'خطایی در برقراری ارتباط با سرور رخ داد. لطفاً مجدداً تلاش کنید.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="flex-1 flex items-center justify-center p-4 sm:p-6 my-auto font-vazir">
        <div class="w-full max-w-md">
            <!-- Card Container -->
            <div class="rounded-2xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-cardLight dark:bg-surface-cardDark p-7 sm:p-9 shadow-xl shadow-zinc-950/5">
                <!-- Header -->
                <div class="text-center mb-7">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 mx-auto flex items-center justify-center mb-3">
                        <LogIn class="w-6 h-6 stroke-[2]" />
                    </div>
                    <h1 class="text-xl font-bold text-zinc-900 dark:text-zinc-50">ورود به DailyFlow</h1>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1.5">
                        به فضای مدیریت کارهای روزانه‌تان خوش آمدید
                    </p>
                </div>

                <!-- Error Banner -->
                <div
                    v-if="errorMessage"
                    class="mb-5 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 flex items-start gap-2.5 text-xs text-rose-700 dark:text-rose-300"
                    role="alert"
                >
                    <AlertCircle class="w-4 h-4 shrink-0 mt-0.5 text-rose-500" />
                    <span>{{ errorMessage }}</span>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleLogin" class="space-y-4" novalidate>
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                            نشانی ایمیل
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                                <Mail class="w-4 h-4" />
                            </div>
                            <input
                                id="email"
                                v-model="email"
                                type="email"
                                dir="ltr"
                                required
                                autocomplete="email"
                                placeholder="name@example.com"
                                class="w-full pr-10 pl-3.5 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-colors"
                                :class="{ 'border-rose-500 ring-1 ring-rose-500': fieldErrors.email }"
                            />
                        </div>
                        <p v-if="fieldErrors.email" class="mt-1 text-[11px] text-rose-500">
                            {{ fieldErrors.email[0] }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                کلمه عبور
                            </label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                                <Lock class="w-4 h-4" />
                            </div>
                            <input
                                id="password"
                                v-model="password"
                                :type="showPassword ? 'text' : 'password'"
                                dir="ltr"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full pr-10 pl-10 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-colors"
                                :class="{ 'border-rose-500 ring-1 ring-rose-500': fieldErrors.password }"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors focus:outline-none"
                                :title="showPassword ? 'مخفی‌کردن کلمه عبور' : 'نمایش کلمه عبور'"
                            >
                                <EyeOff v-if="showPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="fieldErrors.password" class="mt-1 text-[11px] text-rose-500">
                            {{ fieldErrors.password[0] }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input
                                v-model="remember"
                                type="checkbox"
                                class="w-4 h-4 rounded border-surface-borderLight dark:border-surface-borderDark text-brand-600 focus:ring-brand-500/30 bg-surface-subtleLight dark:bg-surface-subtleDark"
                            />
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">مرا به خاطر بسپار</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 disabled:opacity-60 text-white font-medium text-sm shadow-md shadow-brand-600/25 flex items-center justify-center gap-2 transition-all focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                    >
                        <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
                        <span v-else>ورود به حساب</span>
                    </button>
                </form>

                <!-- Footer Link -->
                <div class="mt-6 pt-5 border-t border-surface-borderLight dark:border-surface-borderDark text-center">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        حساب کاربری ندارید؟
                        <router-link
                            to="/register"
                            class="text-brand-600 dark:text-brand-400 font-semibold hover:underline mr-1 inline-flex items-center gap-1"
                        >
                            <span>ثبت‌نام کنید</span>
                            <ArrowLeft class="w-3 h-3" />
                        </router-link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
