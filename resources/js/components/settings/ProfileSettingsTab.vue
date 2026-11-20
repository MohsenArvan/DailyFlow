<script setup lang="ts">
import { ref, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { User, Mail, Globe, CheckCircle, AlertCircle, Loader2 } from 'lucide-vue-next';

const authStore = useAuthStore();

const name = ref(authStore.user?.name || '');
const email = ref(authStore.user?.email || '');
const timezone = ref(authStore.user?.timezone || 'Asia/Tehran');

const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const fieldErrors = ref<Record<string, string[]>>({});

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

watch(
    () => authStore.user,
    (newUser) => {
        if (newUser) {
            name.value = newUser.name;
            email.value = newUser.email;
            timezone.value = newUser.timezone;
        }
    },
    { immediate: true }
);

const handleSaveProfile = async () => {
    successMessage.value = '';
    errorMessage.value = '';
    fieldErrors.value = {};

    if (!name.value.trim()) {
        errorMessage.value = 'لطفاً نام را وارد کنید.';
        return;
    }

    isLoading.value = true;
    try {
        await authStore.updateProfile({
            name: name.value.trim(),
            timezone: timezone.value,
        });
        successMessage.value = 'اطلاعات پروفایل با موفقیت بروزرسانی شد.';
    } catch (err: any) {
        if (err.response?.status === 422) {
            fieldErrors.value = err.response.data?.errors || {};
            errorMessage.value = err.response.data?.message || 'اطلاعات نامعتبر است.';
        } else {
            errorMessage.value = 'خطایی در ذخیره اطلاعات رخ داد.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="space-y-6 font-vazir">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">اطلاعات حساب کاربری</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">نام و منطقه زمانی ثبت‌شده برای فعالیت‌های شما</p>
        </div>

        <!-- Success Alert -->
        <div
            v-if="successMessage"
            class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 flex items-center gap-2.5 text-xs text-emerald-700 dark:text-emerald-300"
            role="status"
        >
            <CheckCircle class="w-4 h-4 shrink-0 text-emerald-500" />
            <span>{{ successMessage }}</span>
        </div>

        <!-- Error Alert -->
        <div
            v-if="errorMessage"
            class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 flex items-center gap-2.5 text-xs text-rose-700 dark:text-rose-300"
            role="alert"
        >
            <AlertCircle class="w-4 h-4 shrink-0 text-rose-500" />
            <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleSaveProfile" class="space-y-4 max-w-lg">
            <!-- Name -->
            <div>
                <label for="profile-name" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    نام و نام خانوادگی
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                        <User class="w-4 h-4" />
                    </div>
                    <input
                        id="profile-name"
                        v-model="name"
                        type="text"
                        required
                        class="w-full pr-10 pl-3.5 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-colors"
                        :class="{ 'border-rose-500 ring-1 ring-rose-500': fieldErrors.name }"
                    />
                </div>
                <p v-if="fieldErrors.name" class="mt-1 text-[11px] text-rose-500">
                    {{ fieldErrors.name[0] }}
                </p>
            </div>

            <!-- Email (Read Only) -->
            <div>
                <label for="profile-email" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    نشانی ایمیل
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                        <Mail class="w-4 h-4" />
                    </div>
                    <input
                        id="profile-email"
                        v-model="email"
                        type="email"
                        disabled
                        dir="ltr"
                        class="w-full pr-10 pl-3.5 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-zinc-100/70 dark:bg-zinc-800/40 text-sm text-zinc-500 dark:text-zinc-400 cursor-not-allowed"
                    />
                </div>
                <p class="mt-1 text-[11px] text-zinc-400">
                    نشانی ایمیل شناسه اصلی حساب شماست و قابل تغییر نیست.
                </p>
            </div>

            <!-- Timezone -->
            <div>
                <label for="profile-timezone" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    منطقه زمانی
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                        <Globe class="w-4 h-4" />
                    </div>
                    <select
                        id="profile-timezone"
                        v-model="timezone"
                        class="w-full pr-10 pl-3.5 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-xs text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-colors"
                    >
                        <option v-for="tz in commonTimezones" :key="tz.value" :value="tz.value">
                            {{ tz.label }}
                        </option>
                    </select>
                </div>
                <p class="mt-1 text-[11px] text-zinc-400">
                    محاسبه کارهای امروز و یادآوری‌ها بر اساس این منطقه زمانی انجام می‌شود.
                </p>
            </div>

            <!-- Submit -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="isLoading"
                    class="py-2.5 px-5 rounded-xl bg-brand-600 hover:bg-brand-500 disabled:opacity-60 text-white font-medium text-xs shadow-md shadow-brand-600/20 flex items-center justify-center gap-2 transition-all focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                    <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
                    <span v-else>ذخیره تغییرات پروفایل</span>
                </button>
            </div>
        </form>
    </div>
</template>
