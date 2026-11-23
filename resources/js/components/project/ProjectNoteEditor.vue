<script setup lang="ts">
import { isAxiosError } from 'axios';
import { computed, onMounted, onUnmounted, ref, shallowRef } from 'vue';
import { onBeforeRouteLeave, onBeforeRouteUpdate } from 'vue-router';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import BaseTextarea from '@/components/ui/BaseTextarea.vue';
import type { ProjectNote, ProjectNoteDraft } from '@/types/projectNote';

const props = defineProps<{
    note: ProjectNote | null;
    save: (draft: ProjectNoteDraft, uuid?: string) => Promise<void>;
}>();
const emit = defineEmits<{ close: [] }>();
const title = shallowRef(props.note?.title ?? '');
const body = shallowRef(props.note?.body ?? '');
const saving = shallowRef(false);
const error = shallowRef('');
const fieldErrors = ref<Partial<Record<keyof ProjectNoteDraft, string[]>>>({});
const dirty = computed(() => title.value !== (props.note?.title ?? '') || body.value !== (props.note?.body ?? ''));

function canLeave() {
    return !saving.value && (!dirty.value || window.confirm('تغییرات ذخیره نشده‌اند. از یادداشت خارج می‌شوید؟'));
}

function close() {
    if (canLeave()) emit('close');
}

async function submit() {
    if (saving.value) return;
    error.value = '';
    fieldErrors.value = {};
    saving.value = true;
    try {
        await props.save({ title: title.value, body: body.value }, props.note?.uuid);
        emit('close');
    } catch (failure) {
        if (isAxiosError(failure) && failure.response?.status === 422) {
            const errors = failure.response.data?.errors;
            if (errors?.title || errors?.body) {
                fieldErrors.value = { title: errors.title, body: errors.body };
                return;
            }
        }
        error.value = 'ذخیرهٔ یادداشت ممکن نشد. دوباره تلاش کنید.';
    } finally {
        saving.value = false;
    }
}

function warnBeforeUnload(event: BeforeUnloadEvent) {
    if (!dirty.value && !saving.value) return;
    event.preventDefault();
    event.returnValue = '';
}

onBeforeRouteLeave(canLeave);
onBeforeRouteUpdate((to, from) => {
    if (to.params.uuid !== from.params.uuid || to.query.tab !== from.query.tab) return canLeave();
});
onMounted(() => window.addEventListener('beforeunload', warnBeforeUnload));
onUnmounted(() => window.removeEventListener('beforeunload', warnBeforeUnload));
</script>

<template>
    <BaseModal :is-open="true" :title="note ? 'ویرایش یادداشت' : 'یادداشت جدید'" @close="close">
        <form class="space-y-4" @submit.prevent="submit">
            <BaseInput
                id="project-note-title"
                v-model="title"
                label="عنوان"
                :disabled="saving"
                :error="fieldErrors.title?.[0]"
                @update:model-value="fieldErrors.title = undefined"
            />
            <BaseTextarea
                id="project-note-body"
                v-model="body"
                label="متن یادداشت"
                :rows="8"
                :disabled="saving"
                :error="fieldErrors.body?.[0]"
                @update:model-value="fieldErrors.body = undefined"
            />
            <p v-if="error" role="alert" class="text-sm text-rose-700 dark:text-rose-300">{{ error }}</p>
            <div class="flex flex-wrap justify-end gap-2">
                <BaseButton variant="ghost" :disabled="saving" @click="close">انصراف</BaseButton>
                <BaseButton type="submit" :loading="saving">ذخیره</BaseButton>
            </div>
        </form>
    </BaseModal>
</template>
