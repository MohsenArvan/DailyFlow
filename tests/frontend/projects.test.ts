import { createPinia, setActivePinia } from 'pinia';
import { expect, it, vi } from 'vitest';
import api from '@/services/api';
import { useProjectStore } from '@/stores/projects';

vi.mock('@/services/api', () => ({ default: { get: vi.fn(), post: vi.fn() } }));

it('preserves project ordering when moving a project into favorites', async () => {
    setActivePinia(createPinia());
    const first = { uuid: 'first', is_favorite: false, order: 1 };
    const second = { uuid: 'second', is_favorite: true, order: 2 };
    vi.mocked(api.get).mockResolvedValue({ data: { data: [first, second] } });
    vi.mocked(api.post).mockResolvedValue({ data: { project: { ...first, is_favorite: true } } });

    const projects = useProjectStore();
    await projects.fetchProjects();
    await projects.toggleFavorite(first.uuid);

    expect(api.post).toHaveBeenCalledWith('/projects/first/favorite');
    expect(projects.favoriteProjects.map(project => project.uuid)).toEqual(['first', 'second']);
    expect(projects.standardProjects).toEqual([]);
});
