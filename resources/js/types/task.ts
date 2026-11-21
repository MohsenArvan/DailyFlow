import type { Project } from '@/types/project';

export type TaskPriority = 'none' | 'low' | 'medium' | 'high' | 'urgent';

export type TaskStatus = 'pending' | 'completed';

export interface Subtask {
    id: number;
    title: string;
    is_completed: boolean;
    position: number;
    completed_at?: string | null;
    created_at?: string;
}

export interface Task {
    id: number;
    uuid: string;
    project_id?: number | null;
    title: string;
    description?: string | null;
    status: TaskStatus;
    priority: TaskPriority;
    priority_label: string;
    due_date?: string | null;
    due_time?: string | null;
    is_today: boolean;
    is_overdue: boolean;
    order: number;
    completed_at?: string | null;
    subtasks_count: number;
    subtasks_completed_count: number;
    subtasks_progress: number;
    subtasks?: Subtask[];
    project?: Project | null;
    created_at: string;
    updated_at: string;
}

export interface CreateTaskPayload {
    title: string;
    description?: string | null;
    priority?: TaskPriority;
    due_date?: string | null;
    due_time?: string | null;
    project_id?: number | null;
}

export interface UpdateTaskPayload {
    title?: string;
    description?: string | null;
    priority?: TaskPriority;
    due_date?: string | null;
    due_time?: string | null;
    project_id?: number | null;
}

export interface TaskCounts {
    today: number;
    inbox: number;
    upcoming: number;
    overdue: number;
    completed: number;
    today_completed?: number;
}
