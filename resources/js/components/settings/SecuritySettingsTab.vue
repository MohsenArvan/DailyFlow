<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { Lock, Eye, EyeOff, CheckCircle, AlertCircle, Loader2, ShieldCheck } from 'lucide-vue-next';

const authStore = useAuthStore();

const currentPassword = ref('');
const password = ref('');
const passwordConfirmation = ref('');
const showPassword = ref(false);

const isLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const fieldErrors = ref<Record<string, string[]>>({});

const handleUpdatePassword = async () => {
    successMessage.value = '';
    errorMessage.value = '';
    fieldErrors.value = {};

    if (!currentPassword.value || !password.value) {
        errorMessage.value = 'لطفاً کلمه عبور فعلی و جدید را وارد کنید.';
        return;
    }

    if (password.value.length < 8) {
        errorMessage.value = 'کلمه عبور جدید باید حداقل ۸ کاراکتر باشد.';
        return;
    }

    if (password.value !== passwordConfirmation.value) {
        errorMessage.value = 'تکرار کلمه عبور جدید مطابقت ندارد.';
        return;
    }

    isLoading.value = true;
    try {
        await authStore.updatePassword({
            current_password: currentPassword.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });

        currentPassword.value = '';
        password.value = '';
        passwordConfirmation.value = '';
        successMessage.value = 'کلمه عبور شما با موفقیت تغییر یافت.';
    } catch (err: any) {
        if (err.response?.status === 422) {
            fieldErrors.value = err.response.data?.errors || {};
            errorMessage.value = err.response.data?.message || 'کلمه عبور فعلی نادرست است یا اطلاعات نامعتبر است.';
        } else {
            errorMessage.value = 'خطایی در تغییر کلمه عبور رخ داد.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="space-y-6 font-vazir">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">امنیت و تغییر کلمه عبور</h2>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">جهت حفظ امنیت حساب خود، کلمه عبور قوی انتخاب کنید</p>
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

        <form @submit.prevent="handleUpdatePassword" class="space-y-4 max-w-lg">
            <!-- Current Password -->
            <div>
                <label for="current-password" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    کلمه عبور فعلی
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                        <Lock class="w-4 h-4" />
                    </div>
                    <input
                        id="current-password"
                        v-model="currentPassword"
                        :type="showPassword ? 'text' : 'password'"
                        dir="ltr"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full pr-10 pl-10 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-colors"
                        :class="{ 'border-rose-500 ring-1 ring-rose-500': fieldErrors.current_password }"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors focus:outline-none"
                    >
                        <EyeOff v-if="showPassword" class="w-4 h-4" />
                        <Eye v-else class="w-4 h-4" />
                    </button>
                </div>
                <p v-if="fieldErrors.current_password" class="mt-1 text-[11px] text-rose-500">
                    {{ fieldErrors.current_password[0] }}
                </p>
            </div>

            <!-- New Password -->
            <div>
                <label for="new-password" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    کلمه عبور جدید (حداقل ۸ کاراکتر)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                        <Lock class="w-4 h-4" />
                    </div>
                    <input
                        id="new-password"
                        v-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        dir="ltr"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full pr-10 pl-3.5 py-2.5 rounded-xl border border-surface-borderLight dark:border-surface-borderDark bg-surface-subtleLight dark:bg-surface-subtleDark text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 transition-colors"
                        :class="{ 'border-rose-500 ring-1 ring-rose-500': fieldErrors.password }"
                    />
                </div>
                <p v-if="fieldErrors.password" class="mt-1 text-[11px] text-rose-500">
                    {{ fieldErrors.password[0] }}
                </p>
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="new-password-confirmation" class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    تکرار کلمه عبور جدید
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-zinc-400">
                        <Lock class="w-4 h-4" />
                    </div>
                    <input
                        id="new-password-confirmation"
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

            <!-- Submit -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="isLoading"
                    class="py-2.5 px-5 rounded-xl bg-brand-600 hover:bg-brand-500 disabled:opacity-60 text-white font-medium text-xs shadow-md shadow-brand-600/20 flex items-center justify-center gap-2 transition-all focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                    <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
                    <span v-else>بروزرسانی کلمه عبور</span>
                </button>
            </div>
        </form>
    </div>
</template>
