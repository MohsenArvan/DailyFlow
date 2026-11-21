export interface Project {
    id: number;
    uuid: string;
    name: string;
    description?: string | null;
    color: string;
    icon?: string | null;
    is_favorite: boolean;
    order: number;
    view_mode: 'list' | 'board';
    tasks_count: number;
    pending_tasks_count: number;
    completed_tasks_count: number;
    progress_percentage: number;
    created_at: string;
    updated_at: string;
}

export interface CreateProjectPayload {
    name: string;
    description?: string | null;
    color?: string;
    icon?: string | null;
    is_favorite?: boolean;
    view_mode?: 'list' | 'board';
}

export interface UpdateProjectPayload {
    name?: string;
    description?: string | null;
    color?: string;
    icon?: string | null;
    is_favorite?: boolean;
    view_mode?: 'list' | 'board';
}
