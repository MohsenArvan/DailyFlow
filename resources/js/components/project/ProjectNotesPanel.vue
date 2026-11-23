<script setup lang="ts">
import { computed, shallowRef } from 'vue';
import { MoreHorizontal, NotebookPen, Pin, Plus } from 'lucide-vue-next';
import { useProjectNotes } from '@/composables/useProjectNotes';
import { useAuthStore } from '@/stores/auth';
import type { ProjectNote, ProjectNoteDraft } from '@/types/projectNote';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseEmptyState from '@/components/ui/BaseEmptyState.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue';
import ProjectNoteEditor from './ProjectNoteEditor.vue';

const props = defineProps<{ projectUuid: string }>();
const { notes, loading, error, hasMore, save, remove, loadMore, retry } = useProjectNotes(() => props.projectUuid);
const auth = useAuthStore();
const editing = shallowRef<ProjectNote | null | undefined>(undefined);
const reading = shallowRef<ProjectNote>();
const deleting = shallowRef<ProjectNote>();
const pending = shallowRef(false);
const actionError = shallowRef('');
const dateFormatter = computed(() => new Intl.DateTimeFormat('fa-IR', {
    dateStyle: 'medium',
    calendar: auth.user?.preferences.date_format === 'gregorian' ? 'gregory' : 'persian',
    timeZone: auth.user?.timezone ?? 'UTC',
}));

function closeActions(event: Event) {
    const actions = (event.currentTarget as HTMLElement).closest('details');
    if (!actions?.open) return;
    actions.open = false;
    actions.querySelector('summary')?.focus();
}

function editNote(note: ProjectNote, event: Event) {
    closeActions(event);
    editing.value = note;
}

function saveDraft(draft: ProjectNoteDraft, uuid?: string) {
    return uuid ? save(draft, uuid) : save(draft);
}

async function togglePin(note: ProjectNote, event: Event) {
    closeActions(event);
    pending.value = true;
    actionError.value = '';
    try {
        await save({ is_pinned: !note.pinned_at }, note.uuid);
    } catch {
        actionError.value = 'تغییر وضعیت سنجاق ممکن نشد.';
    } finally {
        pending.value = false;
    }
}

function confirmDeletion(note: ProjectNote, event: Event) {
    closeActions(event);
    actionError.value = '';
    deleting.value = note;
}

async function deleteNote() {
    if (!deleting.value) return;
    pending.value = true;
    actionError.value = '';
    try {
        await remove(deleting.value.uuid);
        deleting.value = undefined;
    } catch {
        actionError.value = 'حذف یادداشت ممکن نشد. دوباره تلاش کنید.';
    } finally {
        pending.value = false;
    }
}

function closeDeletion() {
    if (pending.value) return;
    deleting.value = undefined;
    actionError.value = '';
}
</script>

<template>
    <section class="space-y-4" :aria-busy="loading">
        <div class="flex flex-wrap justify-end gap-3">
            <BaseButton @click="editing = null">
                <template #prefix><Plus class="size-4" aria-hidden="true" /></template>
                یادداشت جدید
            </BaseButton>
        </div>

        <p v-if="actionError && !deleting" role="alert" class="text-sm text-rose-700 dark:text-rose-300">
            {{ actionError }}
        </p>
        <div v-if="error" class="workspace-panel flex flex-wrap items-center justify-between gap-2 px-4 py-3">
            <p role="alert" class="text-sm">دریافت یادداشت‌ها ممکن نشد.</p>
            <BaseButton size="sm" variant="outline" @click="retry">تلاش دوباره</BaseButton>
        </div>
        <BaseSkeleton v-else-if="loading && !notes.length" variant="rectangular" height="6rem" class="w-full" />
        <BaseEmptyState
            v-else-if="!notes.length"
            title="هنوز یادداشتی برای این پروژه ثبت نشده"
        >
            <template #icon><NotebookPen class="size-5" aria-hidden="true" /></template>
        </BaseEmptyState>

        <article
            v-for="note in notes"
            :key="note.uuid"
            class="workspace-panel grid min-w-0 grid-cols-[minmax(0,1fr)_auto] items-start gap-3 p-4 sm:p-5"
            :class="{ 'border-brand-200 bg-brand-50/70 dark:border-brand-800 dark:bg-brand-950/30': note.pinned_at }"
        >
            <div class="min-w-0 space-y-2">
                <div class="flex items-start gap-2">
                    <span v-if="note.pinned_at" aria-label="سنجاق‌شده" role="img" class="mt-1 text-brand-700 dark:text-brand-300">
                        <Pin aria-hidden="true" class="size-4" />
                    </span>
                    <h3 class="min-w-0">
                        <button
                            type="button"
                            dir="auto"
                            class="block max-w-full break-words rounded text-start text-base font-bold hover:text-brand-700 focus-visible:outline focus-visible:outline-brand-500 dark:hover:text-brand-300"
                            @click="reading = note"
                        >{{ note.title }}</button>
                    </h3>
                </div>
                <p dir="auto" class="line-clamp-2 whitespace-pre-line break-words text-start text-sm leading-7 text-zinc-600 dark:text-zinc-300">{{ note.body }}</p>
                <time :datetime="note.updated_at" class="block text-xs text-zinc-600 dark:text-zinc-400">
                    {{ dateFormatter.format(new Date(note.updated_at)) }}
                </time>
            </div>

            <details class="relative" @keydown.esc.stop="closeActions">
                <summary
                    :aria-label="'گزینه‌های ' + note.title"
                    role="button"
                    class="flex size-10 cursor-pointer list-none items-center justify-center rounded-lg text-zinc-600 hover:bg-zinc-900/5 focus-visible:outline focus-visible:outline-brand-500 dark:text-zinc-300 dark:hover:bg-white/10 [&::-webkit-details-marker]:hidden"
                ><MoreHorizontal class="size-5" aria-hidden="true" /></summary>
                <div class="absolute left-0 z-10 mt-1 flex w-40 flex-col rounded-xl border border-surface-borderLight bg-surface-cardLight p-1 shadow-dropdown dark:border-surface-borderDark dark:bg-surface-cardDark">
                    <BaseButton size="sm" variant="ghost" :disabled="pending" @click="editNote(note, $event)">ویرایش</BaseButton>
                    <BaseButton size="sm" variant="ghost" :disabled="pending || loading" @click="togglePin(note, $event)">
                        {{ note.pinned_at ? 'برداشتن سنجاق' : 'سنجاق کردن' }}
                    </BaseButton>
                    <BaseButton size="sm" variant="ghost" :disabled="pending" @click="confirmDeletion(note, $event)">حذف</BaseButton>
                </div>
            </details>
        </article>

        <div v-if="hasMore && !error" class="flex justify-center">
            <BaseButton variant="outline" :loading="loading" @click="loadMore">نمایش بیشتر</BaseButton>
        </div>

        <BaseModal v-if="reading" :is-open="true" :title="reading.title" size="lg" @close="reading = undefined">
            <p dir="auto" class="whitespace-pre-wrap break-words text-start text-sm leading-8">{{ reading.body }}</p>
        </BaseModal>
        <ProjectNoteEditor v-if="editing !== undefined" :note="editing" :save="saveDraft" @close="editing = undefined" />
        <BaseModal v-if="deleting" :is-open="true" title="حذف یادداشت" size="sm" @close="closeDeletion">
            <p class="break-words text-sm">یادداشت «{{ deleting.title }}» حذف شود؟</p>
            <p v-if="actionError" role="alert" class="mt-2 text-sm text-rose-700 dark:text-rose-300">{{ actionError }}</p>
            <template #footer>
                <BaseButton variant="ghost" :disabled="pending" @click="closeDeletion">انصراف</BaseButton>
                <BaseButton variant="danger" :loading="pending" @click="deleteNote">حذف</BaseButton>
            </template>
        </BaseModal>
    </section>
</template>
