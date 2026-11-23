import { onScopeDispose, ref, shallowRef, toValue, watch, type MaybeRefOrGetter } from 'vue';
import { createProjectNote, deleteProjectNote, listProjectNotes, updateProjectNote } from '@/services/projectNotes';
import type { ProjectNote, ProjectNoteChanges, ProjectNoteDraft } from '@/types/projectNote';

export function useProjectNotes(projectUuid: MaybeRefOrGetter<string>) {
    const notes = ref<ProjectNote[]>([]);
    const loading = shallowRef(false);
    const error = shallowRef(false);
    const hasMore = shallowRef(false);
    let page = 1;
    let requestedPage = 1;
    let request: AbortController | undefined;
    let disposed = false;

    async function load(targetPage: number) {
        request?.abort();
        const controller = new AbortController();
        request = controller;
        const uuid = toValue(projectUuid);
        const isCurrent = () => !controller.signal.aborted && uuid === toValue(projectUuid);

        requestedPage = targetPage;
        loading.value = true;
        error.value = false;
        try {
            const result = await listProjectNotes(uuid, targetPage, controller.signal);
            if (!isCurrent()) return;
            notes.value = targetPage === 1 ? result.data : [
                ...notes.value,
                ...result.data.filter(note => !notes.value.some(existing => existing.uuid === note.uuid)),
            ];
            page = result.meta.current_page;
            hasMore.value = result.links.next !== null;
        } catch {
            if (isCurrent()) error.value = true;
        } finally {
            if (isCurrent()) loading.value = false;
        }
    }

    function refresh(uuid: string) {
        if (disposed || uuid !== toValue(projectUuid)) return;
        hasMore.value = false;
        void load(1);
    }

    async function save(input: ProjectNoteDraft): Promise<void>;
    async function save(input: ProjectNoteChanges, noteUuid: string): Promise<void>;
    async function save(input: ProjectNoteChanges, noteUuid?: string) {
        const uuid = toValue(projectUuid);
        if (noteUuid) {
            const updated = await updateProjectNote(uuid, noteUuid, input);
            if (!disposed && uuid === toValue(projectUuid)) {
                notes.value = notes.value.map(note => note.uuid === noteUuid ? updated : note);
            }
        } else {
            await createProjectNote(uuid, input as ProjectNoteDraft);
        }
        refresh(uuid);
    }

    async function remove(noteUuid: string) {
        const uuid = toValue(projectUuid);
        await deleteProjectNote(uuid, noteUuid);
        if (!disposed && uuid === toValue(projectUuid)) {
            notes.value = notes.value.filter(note => note.uuid !== noteUuid);
        }
        refresh(uuid);
    }

    watch(() => toValue(projectUuid), uuid => {
        notes.value = [];
        refresh(uuid);
    }, { immediate: true });

    onScopeDispose(() => {
        disposed = true;
        request?.abort();
    });

    return {
        notes, loading, error, hasMore, save, remove,
        loadMore: () => hasMore.value && !loading.value ? load(page + 1) : undefined,
        retry: () => load(requestedPage),
    };
}
