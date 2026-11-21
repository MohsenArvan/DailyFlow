export type Wallpaper = 'lake' | 'dunes' | 'coast' | 'none';

export interface UserPreferences {
    theme: 'dark' | 'light' | 'system';
    date_format: 'jalali' | 'gregorian';
    start_of_week: number; // 6 = Saturday (Shanbeh)
    daily_goal: number;
    wallpaper?: Wallpaper;
}

export interface User {
    id: number;
    name: string;
    email: string;
    timezone: string;
    preferences: UserPreferences;
    created_at: string;
}

export interface LoginCredentials {
    email: string;
    password: string;
    remember?: boolean;
}

export interface RegisterData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    timezone?: string;
}

export interface UpdateProfileData {
    name: string;
    timezone: string;
}

export interface UpdatePreferencesData {
    theme?: 'dark' | 'light' | 'system';
    date_format?: 'jalali' | 'gregorian';
    start_of_week?: number;
    daily_goal?: number;
    wallpaper?: Wallpaper;
}

export interface UpdatePasswordData {
    current_password: string;
    password: string;
    password_confirmation: string;
}

export interface AuthResponse {
    message: string;
    token?: string;
    user: User;
}
