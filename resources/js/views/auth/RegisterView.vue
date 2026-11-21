<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import {
    UserPlus,
    User as UserIcon,
    Mail,
    Lock,
    Globe,
    Eye,
    EyeOff,
    AlertCircle,
    Loader2,
    ArrowLeft,
} from 'lucide-vue-next';

const router = useRouter();
const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const timezone = ref('Asia/Tehran');
const password = ref('');
const passwordConfirmation = ref('');
const showPassword = ref(false);
const errorMessage = ref('');
const fieldErrors = ref<Record<string, string[]>>({});
const isLoading = ref(false);

const commonTimezones = [
    { value: 'Asia/Tehran', label: 'تهران (Asia/Tehran - GMT+3:30)' },
    { value: 'UTC', label: 'جهانی هماهنگ (UTC)' },
    { value: 'Asia/Dubai', label: 'دبی (Asia/Dubai - GMT+4)' },
    { value: 'Europe/Istanbul', label: 'استانبول (Europe/Istanbul - GMT+3)' },
    { value: 'Europe/London', label: 'لندن (Europe/London - GMT+0/+1)' },
    { value: 'Europe/Berlin', label: 'برلین (Europe/Berlin - GMT+1/+2)' },
    { value: 'America/New_York', label: 'نیویورک (America/New_York - GMT-5/-4)' },
    { value: 'America/Los_Angeles', label: 'لس آنجلس (America/Los_Angeles - GMT-8/-7)' },
];

onMounted(() => {
    try {
        const detected = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (detected) {
            timezone.value = detected;
            if (!commonTimezones.some(tz => tz.value === detected)) {
                commonTimezones.unshift({ value: detected, label: `${detected} (تشخیص خودکار)` });
            }
        }
    } catch {
        // Fallback remains Asia/Tehran
    }
});

const handleRegister = async () => {
    errorMessage.value = '';
    fieldErrors.value = {};

    if (!name.value.trim() || !email.value.trim() || !password.value) {
        errorMessage.value = 'لطفاً تمامی فیلدهای الزامی را تکمیل کنید.';
        return;
    }

    if (password.value.length < 8) {
        errorMessage.value = 'کلمه عبور باید حداقل ۸ کاراکتر باشد.';
        return;
    }

    if (password.value !== passwordConfirmation.value) {
        errorMessage.value = 'تکرار کلمه عبور با کلمه عبور مطابقت ندارد.';
        return;
    }

    isLoading.value = true;
    try {
        await authStore.register({
            name: name.value.trim(),
            email: email.value.trim(),
            timezone: timezone.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });

        router.push('/');
    } catch (err: any) {
        if (err.response?.status === 422) {
            fieldErrors.value = err.response.data?.errors || {};
            errorMessage.value = err.response.data?.message || 'اطلاعات وارد شده نامعتبر است.';
        } else if (err.response?.status === 429) {
            errorMessage.value = 'درخواست‌های بیش از حد مجاز. لطفاً کمی بعد تلاش کنید.';
        } else {
            errorMessage.value = 'خطایی در ثبت‌نام رخ داد. لطفاً مجدداً تلاش کنید.';
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
            <div class="workspace-hero p-6 shadow-card sm:p-9">
                <!-- Header -->
                <div class="text-center mb-7">
                    <div class="w-12 h-12 rounded-2xl bg-brand-500/10 text-brand-600 dark:text-brand-400 mx-auto flex items-center justify-center mb-3">
                        <UserPlus class="w-6 h-6 stroke-[2]" />
                    </div>
                    <h1 class="text-xl font-bold text-zinc-900 dark:text-zinc-50">ثبت‌نام در DailyFlow</h1>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1.5">
                        حساب شخصی خود را بسازید و روزهایتان را منظم‌تر مدیریت کنید
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
                <form @submit.prevent="handleRegister" class="space-y-4" novalidate>
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                            نام و نام خانوادگی
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                                <UserIcon class="w-4 h-4" />
                            </div>
                            <input
                                id="name"
                                v-model="name"
                                type="text"
                                required
                                autocomplete="name"
                                placeholder="مثلاً: علی رضایی"
                                class="w-full pr-10 pl-3.5 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-colors"
                                :class="{ 'border-rose-500 ring-1 ring-rose-500': fieldErrors.name }"
                            />
                        </div>
                        <p v-if="fieldErrors.name" class="mt-1 text-[11px] text-rose-500">
                            {{ fieldErrors.name[0] }}
                        </p>
                    </div>

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

                    <!-- Timezone -->
                    <div>
                        <label for="timezone" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                            منطقه زمانی
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                                <Globe class="w-4 h-4" />
                            </div>
                            <select
                                id="timezone"
                                v-model="timezone"
                                class="w-full pr-10 pl-3.5 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-colors"
                            >
                                <option v-for="tz in commonTimezones" :key="tz.value" :value="tz.value">
                                    {{ tz.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                            کلمه عبور (حداقل ۸ کاراکتر)
                        </label>
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
                                autocomplete="new-password"
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

                    <!-- Confirm Password -->
                    <div>
                        <label for="passwordConfirmation" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                            تکرار کلمه عبور
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                                <Lock class="w-4 h-4" />
                            </div>
                            <input
                                id="passwordConfirmation"
                                v-model="passwordConfirmation"
                                :type="showPassword ? 'text' : 'password'"
                                dir="ltr"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full pr-10 pl-3.5 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="isLoading"
                        class="w-full py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-500 disabled:opacity-60 text-white font-medium text-sm shadow-md shadow-brand-600/25 flex items-center justify-center gap-2 transition-all focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                    >
                        <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
                        <span v-else>ایجاد حساب کاربری</span>
                    </button>
                </form>

                <!-- Footer Link -->
                <div class="mt-6 pt-5 border-t border-surface-borderLight dark:border-surface-borderDark text-center">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        قبلاً ثبت‌نام کرده‌اید؟
                        <router-link
                            to="/login"
                            class="text-brand-600 dark:text-brand-400 font-semibold hover:underline mr-1 inline-flex items-center gap-1"
                        >
                            <span>وارد شوید</span>
                            <ArrowLeft class="w-3 h-3" />
                        </router-link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
