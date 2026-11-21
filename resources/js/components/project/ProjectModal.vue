<script setup lang="ts">
import { useDialogFocus } from '@/composables/useDialogFocus';
import { ref, watch, computed } from 'vue';
import { useProjectStore } from '@/stores/projects';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseTextarea from '@/components/ui/BaseTextarea.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseCheckbox from '@/components/ui/BaseCheckbox.vue';
import {
    X,
    Folder,
    Briefcase,
    BookOpen,
    Star,
    Code,
    Target,
    Heart,
    Zap,
    CheckCircle2,
    Loader2,
} from 'lucide-vue-next';

const projectStore = useProjectStore();

const PRESET_COLORS = [
    { label: 'آبی', hex: '#3b82f6' },
    { label: 'زمردی', hex: '#10b981' },
    { label: 'کهربایی', hex: '#f59e0b' },
    { label: 'قرمز', hex: '#ef4444' },
    { label: 'بنفش', hex: '#8b5cf6' },
    { label: 'فیروزه‌ای', hex: '#06b6d4' },
    { label: 'نارنجی', hex: '#f97316' },
    { label: 'صورتی', hex: '#ec4899' },
    { label: 'نیلی', hex: '#6366f1' },
    { label: 'خاکستری', hex: '#64748b' },
];

const PRESET_ICONS = [
    { key: 'folder', label: 'پوشه', icon: Folder },
    { key: 'briefcase', label: 'کیف کار', icon: Briefcase },
    { key: 'book', label: 'کتاب', icon: BookOpen },
    { key: 'star', label: 'ستاره', icon: Star },
    { key: 'code', label: 'کد', icon: Code },
    { key: 'target', label: 'هدف', icon: Target },
    { key: 'heart', label: 'علاقه‌مندی', icon: Heart },
    { key: 'zap', label: 'ایده', icon: Zap },
    { key: 'check-circle', label: 'چک‌لیست', icon: CheckCircle2 },
];

const name = ref('');
const description = ref('');
const selectedColor = ref(PRESET_COLORS[0].hex);
const selectedIcon = ref('folder');
const isFavorite = ref(false);
const isSubmitting = ref(false);

const isEditMode = computed(() => projectStore.modalMode === 'edit');
const modalTitle = computed(() => isEditMode.value ? 'ویرایش پروژه' : 'پروژه جدید');

watch(
    () => projectStore.isModalOpen,
    (isOpen) => {
        if (isOpen) {
            if (isEditMode.value && projectStore.editingProject) {
                name.value = projectStore.editingProject.name;
                description.value = projectStore.editingProject.description || '';
                selectedColor.value = projectStore.editingProject.color || PRESET_COLORS[0].hex;
                selectedIcon.value = projectStore.editingProject.icon || 'folder';
                isFavorite.value = projectStore.editingProject.is_favorite;
            } else {
                name.value = '';
                description.value = '';
                selectedColor.value = PRESET_COLORS[0].hex;
                selectedIcon.value = 'folder';
                isFavorite.value = false;
            }
        }
    }
);

const handleClose = () => {
    projectStore.closeModal();
};

const handleSubmit = async () => {
    if (!name.value.trim() || isSubmitting.value) return;

    isSubmitting.value = true;
    try {
        if (isEditMode.value && projectStore.editingProject) {
            await projectStore.updateProject(projectStore.editingProject.uuid, {
                name: name.value.trim(),
                description: description.value.trim() || null,
                color: selectedColor.value,
                icon: selectedIcon.value,
                is_favorite: isFavorite.value,
            });
        } else {
            await projectStore.createProject({
                name: name.value.trim(),
                description: description.value.trim() || null,
                color: selectedColor.value,
                icon: selectedIcon.value,
                is_favorite: isFavorite.value,
            });
        }
    } finally {
        isSubmitting.value = false;
    }
};

const dialogRoot = ref<HTMLElement>();
const trapFocus = useDialogFocus(dialogRoot, () => projectStore.isModalOpen);

const handleKeydown = (e: KeyboardEvent) => {
    trapFocus(e);
    if (e.key === 'Escape' && projectStore.isModalOpen) {
        handleClose();
    }
};
</script>

<template>
    <Teleport to="body">
        <div
            v-if="projectStore.isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 font-vazir text-right"
        >
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-zinc-950/50 backdrop-blur-xs transition-opacity"
                @click="handleClose"
            ></div>

            <!-- Modal Content -->
            <div ref="dialogRoot" role="dialog" aria-modal="true" tabindex="-1" @keydown="handleKeydown" :aria-label="modalTitle" class="relative w-full max-w-lg rounded-2xl bg-surface-cardLight dark:bg-surface-cardDark border border-surface-borderLight dark:border-surface-borderDark shadow-modal p-6 z-10 animate-in fade-in zoom-in-95 duration-150">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-surface-borderLight dark:border-surface-borderDark mb-5">
                    <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-50 flex items-center gap-2">
                        <span
                            class="w-3.5 h-3.5 rounded-full inline-block shrink-0 shadow-xs"
                            :style="{ backgroundColor: selectedColor }"
                        ></span>
                        <span>{{ modalTitle }}</span>
                    </h3>
                    <button
                        type="button"
                        @click="handleClose"
                        aria-label="بستن پروژه"
                        class="p-1.5 rounded-xl text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <!-- Project Name -->
                    <div>
                        <BaseInput
                            v-model="name"
                            label="نام پروژه"
                            placeholder="مثلاً: طراحی وب‌سایت، اهداف سلامت، یادگیری زبان"
                            required
                        />
                    </div>

                    <!-- Description -->
                    <div>
                        <BaseTextarea
                            v-model="description"
                            label="توضیحات (اختیاری)"
                            placeholder="هدف یا جزئیات کلی این پروژه را یادداشت کنید..."
                            :rows="2"
                        />
                    </div>

                    <!-- Color Picker Palette -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-2">
                            رنگ شناسه
                        </label>
                        <div class="flex flex-wrap gap-2.5">
                            <button
                                v-for="c in PRESET_COLORS"
                                :key="c.hex"
                                type="button"
                                @click="selectedColor = c.hex"
                                :title="c.label"
                                class="w-7 h-7 rounded-full transition-transform focus:outline-none flex items-center justify-center shadow-xs"
                                :class="selectedColor === c.hex ? 'ring-2 ring-offset-2 ring-zinc-900 dark:ring-zinc-100 dark:ring-offset-zinc-900 scale-110' : 'hover:scale-105'"
                                :style="{ backgroundColor: c.hex }"
                            >
                                <span v-if="selectedColor === c.hex" class="w-2 h-2 rounded-full bg-white"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Icon Picker -->
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-2">
                            آیکون پروژه
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="item in PRESET_ICONS"
                                :key="item.key"
                                type="button"
                                @click="selectedIcon = item.key"
                                :title="item.label"
                                class="p-2 rounded-xl border transition-all flex items-center justify-center text-xs"
                                :class="selectedIcon === item.key
                                    ? 'border-brand-500 bg-brand-500/10 text-brand-600 dark:text-brand-400 shadow-xs'
                                    : 'border-surface-borderLight dark:border-surface-borderDark text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800'"
                            >
                                <component :is="item.icon" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Favorite Checkbox -->
                    <div class="pt-2">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-medium text-zinc-700 dark:text-zinc-300 select-none">
                            <BaseCheckbox
                                :model-value="isFavorite"
                                @change="isFavorite = !isFavorite"
                            />
                            <span>افزودن به پروژه‌های مورد علاقه (نمایش در بالای لیست)</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-surface-borderLight dark:border-surface-borderDark mt-6">
                        <BaseButton
                            type="button"
                            variant="secondary"
                            size="sm"
                            @click="handleClose"
                        >
                            انصراف
                        </BaseButton>
                        <BaseButton
                            type="submit"
                            variant="primary"
                            size="sm"
                            :loading="isSubmitting"
                            :disabled="!name.trim()"
                        >
                            {{ isEditMode ? 'ذخیره تغییرات' : 'ایجاد پروژه' }}
                        </BaseButton>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
