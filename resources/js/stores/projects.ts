import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';
import type { Project, CreateProjectPayload, UpdateProjectPayload } from '@/types/project';
import { useToast } from '@/composables/useToast';

export const useProjectStore = defineStore('projects', () => {
    const projects = ref<Project[]>([]);
    const currentProject = ref<Project | null>(null);
    const isLoading = ref<boolean>(false);
    const isModalOpen = ref<boolean>(false);
    const modalMode = ref<'create' | 'edit'>('create');
    const editingProject = ref<Project | null>(null);
    const toast = useToast();
    let detailRequest = 0;

    const favoriteProjects = computed(() => projects.value.filter(p => p.is_favorite));
    const standardProjects = computed(() => projects.value.filter(p => !p.is_favorite));

    async function fetchProjects(search?: string): Promise<void> {
        isLoading.value = true;
        try {
            const params: Record<string, string> = {};
            if (search) params.search = search;
            const response = await api.get<{ data: Project[] }>('/projects', { params });
            projects.value = response.data.data;
        } catch (err) {
            console.error('Failed to fetch projects:', err);
            toast.error('خطا در بارگذاری پروژه‌ها.');
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchProject(uuid: string): Promise<Project | null> {
        const request = ++detailRequest;
        isLoading.value = true;
        try {
            const response = await api.get<{ data: Project }>(`/projects/${uuid}`);
            if (request === detailRequest) currentProject.value = response.data.data;
            return response.data.data;
        } catch (err) {
            console.error('Failed to fetch project:', err);
            if (request === detailRequest) toast.error('خطا در یافتن پروژه.');
            return null;
        } finally {
            if (request === detailRequest) isLoading.value = false;
        }
    }

    async function createProject(payload: CreateProjectPayload): Promise<Project | null> {
        try {
            const response = await api.post<{ message: string; project: Project }>('/projects', payload);
            const newProject = response.data.project;
            projects.value.push(newProject);
            toast.success('پروژه با موفقیت ایجاد شد.');
            closeModal();
            return newProject;
        } catch (err: any) {
            const msg = err.response?.data?.message || 'خطا در ایجاد پروژه.';
            toast.error(msg);
            return null;
        }
    }

    async function updateProject(uuid: string, payload: UpdateProjectPayload): Promise<Project | null> {
        try {
            const response = await api.put<{ message: string; project: Project }>(`/projects/${uuid}`, payload);
            const updated = response.data.project;

            const idx = projects.value.findIndex(p => p.uuid === uuid);
            if (idx !== -1) {
                projects.value[idx] = updated;
            }

            if (currentProject.value?.uuid === uuid) {
                currentProject.value = updated;
            }

            toast.success('پروژه با موفقیت ویرایش شد.');
            closeModal();
            return updated;
        } catch (err: any) {
            const msg = err.response?.data?.message || 'خطا در ویرایش پروژه.';
            toast.error(msg);
            return null;
        }
    }

    async function deleteProject(uuid: string, deleteTasks: boolean = false): Promise<boolean> {
        try {
            await api.delete(`/projects/${uuid}`, {
                params: { delete_tasks: deleteTasks ? 1 : 0 },
            });

            projects.value = projects.value.filter(p => p.uuid !== uuid);
            if (currentProject.value?.uuid === uuid) {
                currentProject.value = null;
            }

            toast.success('پروژه با موفقیت حذف شد.');
            return true;
        } catch (err) {
            console.error('Failed to delete project:', err);
            toast.error('خطا در حذف پروژه.');
            return false;
        }
    }

    async function toggleFavorite(uuid: string): Promise<void> {
        const project = projects.value.find(p => p.uuid === uuid);
        if (project) {
            project.is_favorite = !project.is_favorite;
        }

        try {
            const response = await api.post<{ message: string; project: Project }>(`/projects/${uuid}/favorite`);
            const updated = response.data.project;
            const idx = projects.value.findIndex(p => p.uuid === uuid);
            if (idx !== -1) {
                projects.value[idx] = updated;
            }
            if (currentProject.value?.uuid === uuid) {
                currentProject.value = updated;
            }
        } catch (err) {
            console.error('Failed to toggle favorite:', err);
            // Revert
            if (project) {
                project.is_favorite = !project.is_favorite;
            }
            toast.error('خطا در تغییر وضعیت علاقه‌مندی.');
        }
    }

    function openCreateModal(): void {
        modalMode.value = 'create';
        editingProject.value = null;
        isModalOpen.value = true;
    }

    function openEditModal(project: Project): void {
        modalMode.value = 'edit';
        editingProject.value = project;
        isModalOpen.value = true;
    }

    function closeModal(): void {
        isModalOpen.value = false;
        editingProject.value = null;
    }

    return {
        projects,
        currentProject,
        isLoading,
        isModalOpen,
        modalMode,
        editingProject,
        favoriteProjects,
        standardProjects,
        fetchProjects,
        fetchProject,
        createProject,
        updateProject,
        deleteProject,
        toggleFavorite,
        openCreateModal,
        openEditModal,
        closeModal,
    };
});
