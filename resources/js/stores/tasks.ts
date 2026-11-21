import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';
import { useDashboardStore } from './dashboard';
import type {
    Task,
    Subtask,
    CreateTaskPayload,
    UpdateTaskPayload,
    TaskCounts,
} from '@/types/task';
import { useToast } from '@/composables/useToast';

export const useTaskStore = defineStore('tasks', () => {
    const tasks = ref<Task[]>([]);
    const currentTask = ref<Task | null>(null);
    const counts = ref<TaskCounts>({
        today: 0,
        inbox: 0,
        upcoming: 0,
        overdue: 0,
        completed: 0,
        today_completed: 0,
    });
    const isLoading = ref<boolean>(false);
    const isDetailsOpen = ref<boolean>(false);
    const activeView = ref<string>('today');
    const toast = useToast();
    const revision = ref(0);
    const dashboard = useDashboardStore();
    let taskRequest = 0;

    function notifyMutation() {
        dashboard.invalidate();
        revision.value++;
    }

    const pendingTasks = computed(() => tasks.value.filter(t => t.status !== 'completed'));
    const completedTasks = computed(() => tasks.value.filter(t => t.status === 'completed'));

    async function fetchCounts(): Promise<void> {
        try {
            const response = await api.get<{ counts: TaskCounts }>('/tasks/counts');
            counts.value = response.data.counts;
        } catch (err) {
            console.error('Failed to fetch task counts:', err);
        }
    }

    async function fetchTasks(view: string = 'today', search?: string, signal?: AbortSignal): Promise<boolean> {
        const request = ++taskRequest;
        isLoading.value = true;
        activeView.value = view;
        try {
            const params: Record<string, string> = { view };
            if (search) params.search = search;
            const response = await api.get<{ data: Task[] }>('/tasks', { params, ...(signal ? { signal } : {}) });
            if (request !== taskRequest || signal?.aborted) return false;
            tasks.value = response.data.data;
            await fetchCounts();
            return request === taskRequest && !signal?.aborted;
        } catch (err) {
            if (request !== taskRequest || signal?.aborted) return false;
            console.error('Failed to fetch tasks:', err);
            toast.error('خطا در دریافت لیست تسک‌ها.');
            return false;
        } finally {
            if (request === taskRequest) isLoading.value = false;
        }
    }

    async function createTask(payload: CreateTaskPayload): Promise<Task | null> {
        try {
            const response = await api.post<{ message: string; task: Task }>('/tasks', payload);
            const newTask = response.data.task;

            // Optimistically insert into active list if view matches
            if (
                activeView.value === 'all' ||
                (activeView.value === 'today' && newTask.is_today) ||
                (activeView.value === 'inbox' && !newTask.due_date) ||
                (activeView.value === 'upcoming' && !newTask.is_today && !newTask.is_overdue && newTask.due_date)
            ) {
                tasks.value.unshift(newTask);
            }

            notifyMutation();
            await fetchCounts();
            toast.success('تسک با موفقیت ایجاد شد.');
            return newTask;
        } catch (err: any) {
            const msg = err.response?.data?.message || 'خطا در ایجاد تسک.';
            toast.error(msg);
            return null;
        }
    }

    async function updateTask(uuid: string, payload: UpdateTaskPayload): Promise<Task | null> {
        try {
            const response = await api.put<{ message: string; task: Task }>(`/tasks/${uuid}`, payload);
            const updated = response.data.task;

            const index = tasks.value.findIndex(t => t.uuid === uuid);
            if (index !== -1) {
                tasks.value[index] = updated;
            }

            if (currentTask.value?.uuid === uuid) {
                currentTask.value = updated;
            }

            notifyMutation();
            await fetchCounts();
            return updated;
        } catch (err: any) {
            const msg = err.response?.data?.message || 'خطا در ویرایش تسک.';
            toast.error(msg);
            return null;
        }
    }

    async function completeTask(uuid: string): Promise<void> {
        try {
            const response = await api.post<{ message: string; task: Task }>(`/tasks/${uuid}/complete`);
            const completed = response.data.task;
            const index = tasks.value.findIndex(t => t.uuid === uuid);
            if (index !== -1) {
                tasks.value[index] = completed;
            }
            if (currentTask.value?.uuid === uuid) {
                currentTask.value = completed;
            }
            notifyMutation();
            await fetchCounts();
        } catch (err) {
            console.error('Failed to complete task:', err);
            toast.error('خطا در تکمیل تسک.');

        }
    }

    async function reopenTask(uuid: string): Promise<void> {
        try {
            const response = await api.post<{ message: string; task: Task }>(`/tasks/${uuid}/reopen`);
            const reopened = response.data.task;
            const index = tasks.value.findIndex(t => t.uuid === uuid);
            if (index !== -1) {
                tasks.value[index] = reopened;
            }
            if (currentTask.value?.uuid === uuid) {
                currentTask.value = reopened;
            }
            notifyMutation();
            await fetchCounts();
        } catch (err) {
            console.error('Failed to reopen task:', err);
            toast.error('خطا در بازگشایی تسک.');
        }
    }

    async function deleteTask(uuid: string): Promise<boolean> {
        try {
            await api.delete(`/tasks/${uuid}`);
            tasks.value = tasks.value.filter(t => t.uuid !== uuid);
            if (currentTask.value?.uuid === uuid) {
                closeDetails();
            }
            notifyMutation();
            await fetchCounts();
            toast.success('تسک حذف شد.');
            return true;
        } catch (err) {
            console.error('Failed to delete task:', err);
            toast.error('خطا در حذف تسک.');
            return false;
        }
    }

    function openDetails(task: Task) {
        currentTask.value = task;
        isDetailsOpen.value = true;
    }

    function closeDetails() {
        currentTask.value = null;
        isDetailsOpen.value = false;
    }

    // Subtask actions
    async function addSubtask(taskUuid: string, title: string): Promise<Subtask | null> {
        try {
            const response = await api.post<{ message: string; subtask: Subtask }>(`/tasks/${taskUuid}/subtasks`, { title });
            const subtask = response.data.subtask;

            const listed = tasks.value.find(task => task.uuid === taskUuid);
            const selected = currentTask.value?.uuid === taskUuid ? currentTask.value : null;
            for (const task of new Set([listed, selected])) {
                if (!task) continue;
                task.subtasks ??= [];
                task.subtasks.push(subtask);
                task.subtasks_count++;
                task.subtasks_progress = Math.round((task.subtasks_completed_count / task.subtasks_count) * 100);
            }

            return subtask;
        } catch (err) {
            console.error('Failed to add subtask:', err);
            toast.error('خطا در افزودن زیرتسک.');
            return null;
        }
    }

    async function toggleSubtask(subtaskId: number): Promise<void> {
        try {
            const response = await api.post<{ message: string; subtask: Subtask }>(`/subtasks/${subtaskId}/toggle`);
            const toggled = response.data.subtask;

            // Update in current task
            if (currentTask.value?.subtasks) {
                const sub = currentTask.value.subtasks.find(s => s.id === subtaskId);
                if (sub) {
                    sub.is_completed = toggled.is_completed;
                    const compCount = currentTask.value.subtasks.filter(s => s.is_completed).length;
                    currentTask.value.subtasks_completed_count = compCount;
                    currentTask.value.subtasks_progress = Math.round((compCount / currentTask.value.subtasks.length) * 100);
                }
            }

            // Update in tasks list
            for (const t of tasks.value) {
                if (t.subtasks) {
                    const sub = t.subtasks.find(s => s.id === subtaskId);
                    if (sub) {
                        sub.is_completed = toggled.is_completed;
                        const compCount = t.subtasks.filter(s => s.is_completed).length;
                        t.subtasks_completed_count = compCount;
                        t.subtasks_progress = Math.round((compCount / t.subtasks.length) * 100);
                        break;
                    }
                }
            }
        } catch (err) {
            console.error('Failed to toggle subtask:', err);
            toast.error('خطا در تغییر وضعیت زیرتسک.');
        }
    }

    async function deleteSubtask(subtaskId: number): Promise<void> {
        try {
            await api.delete(`/subtasks/${subtaskId}`);

            if (currentTask.value?.subtasks) {
                currentTask.value.subtasks = currentTask.value.subtasks.filter(s => s.id !== subtaskId);
                currentTask.value.subtasks_count = currentTask.value.subtasks.length;
                const compCount = currentTask.value.subtasks.filter(s => s.is_completed).length;
                currentTask.value.subtasks_completed_count = compCount;
                currentTask.value.subtasks_progress = currentTask.value.subtasks.length > 0
                    ? Math.round((compCount / currentTask.value.subtasks.length) * 100) : 0;
            }

            for (const t of tasks.value) {
                if (t.subtasks) {
                    const hadSub = t.subtasks.some(s => s.id === subtaskId);
                    if (hadSub) {
                        t.subtasks = t.subtasks.filter(s => s.id !== subtaskId);
                        t.subtasks_count = t.subtasks.length;
                        const compCount = t.subtasks.filter(s => s.is_completed).length;
                        t.subtasks_completed_count = compCount;
                        t.subtasks_progress = t.subtasks.length > 0 ? Math.round((compCount / t.subtasks.length) * 100) : 0;
                        break;
                    }
                }
            }
        } catch (err) {
            console.error('Failed to delete subtask:', err);
            toast.error('خطا در حذف زیرتسک.');
        }
    }

    return {
        tasks,
        revision,
        currentTask,
        counts,
        isLoading,
        isDetailsOpen,
        activeView,
        pendingTasks,
        completedTasks,
        fetchTasks,
        fetchCounts,
        createTask,
        updateTask,
        completeTask,
        reopenTask,
        deleteTask,
        openDetails,
        closeDetails,
        addSubtask,
        toggleSubtask,
        deleteSubtask,
    };
});
