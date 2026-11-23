import { effectScope, nextTick, ref } from 'vue';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import api from '@/services/api';
import { useProjectNotes } from '@/composables/useProjectNotes';

vi.mock('@/services/api', () => ({
    default: { get: vi.fn(), post: vi.fn(), patch: vi.fn() },
}));

let scope: ReturnType<typeof effectScope>;
beforeEach(() => { scope = effectScope(); });
const project = ref('first');
const first = { uuid: 'note-1', title: 'اول', body: 'متن', pinned_at: null, created_at: '', updated_at: '' };
const second = { ...first, uuid: 'note-2', title: 'دوم' };
const response = (data: unknown[], page = 1, next = false) => ({
    data: { data, meta: { current_page: page }, links: { next: next ? '?page=2' : null } },
});

afterEach(() => {
    scope.stop();
    vi.resetAllMocks();
    project.value = 'first';
});

it('loads additional pages and restarts from page one after pinning', async () => {
    vi.mocked(api.get)
        .mockResolvedValueOnce(response([first], 1, true))
        .mockResolvedValueOnce(response([second], 2))
        .mockResolvedValueOnce(response([second, first]));
    vi.mocked(api.patch).mockResolvedValue({ data: { data: second } });
    const notes = scope.run(() => useProjectNotes(project))!;
    await vi.waitFor(() => expect(notes.notes.value).toEqual([first]));
    await notes.loadMore();
    expect(notes.notes.value).toEqual([first, second]);
    await notes.save({ is_pinned: true }, second.uuid);
    await vi.waitFor(() => expect(notes.notes.value).toEqual([second, first]));
    expect(api.get).toHaveBeenLastCalledWith('/projects/first/notes', expect.objectContaining({ params: { page: 1 } }));
});

it('discards an old response after changing projects', async () => {
    let resolveOld!: (value: ReturnType<typeof response>) => void;
    vi.mocked(api.get).mockImplementationOnce(() => new Promise(resolve => { resolveOld = resolve; }))
        .mockResolvedValueOnce(response([second]));
    const notes = scope.run(() => useProjectNotes(project))!;

    project.value = 'second';
    await nextTick();
    await vi.waitFor(() => expect(notes.notes.value).toEqual([second]));
    resolveOld(response([first]));
    await nextTick();
    expect(notes.notes.value).toEqual([second]);
    expect(api.get).toHaveBeenLastCalledWith('/projects/second/notes', expect.anything());
});

it('reports a refresh error separately from successful creation and can retry an empty list', async () => {
    vi.mocked(api.get).mockResolvedValueOnce(response([]))
        .mockRejectedValueOnce(new Error('offline'))
        .mockResolvedValueOnce(response([first]));
    vi.mocked(api.post).mockResolvedValue({ data: { data: first } });
    const notes = scope.run(() => useProjectNotes(project))!;
    await vi.waitFor(() => expect(notes.loading.value).toBe(false));
    expect(notes.notes.value).toEqual([]);
    await expect(notes.save({ title: first.title, body: first.body })).resolves.toBeUndefined();
    await vi.waitFor(() => expect(notes.error.value).toBe(true));
    await notes.retry();
    expect(notes.error.value).toBe(false);
    expect(notes.notes.value).toEqual([first]);
});
