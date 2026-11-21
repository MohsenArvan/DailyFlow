import type { TaskPriority } from './task';

export interface DashboardSummary {
    completion_rate: number;
    current_streak_days: number;
    best_streak_days: number;
    priority_distribution: Record<TaskPriority, number>;
    project_distribution: { id: string; name: string; color: string; count: number }[];
    completed_today: number;
    daily_goal: number;
}

export interface VelocityDay {
    date: string;
    count: number;
}

export type Velocity = Record<'week' | 'month', VelocityDay[]>;
