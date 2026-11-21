import type { Wallpaper } from '@/types/auth';

export const wallpapers: ReadonlyArray<{ id: Wallpaper; label: string; image?: string; preview?: string }> = [
    { id: 'lake', label: 'دریاچهٔ کوهستانی', image: '/wallpapers/lake.webp', preview: '/wallpapers/lake-preview.webp' },
    { id: 'dunes', label: 'تپه‌های شنی', image: '/wallpapers/dunes.webp', preview: '/wallpapers/dunes-preview.webp' },
    { id: 'coast', label: 'ساحل مه‌آلود', image: '/wallpapers/coast.webp', preview: '/wallpapers/coast-preview.webp' },
    { id: 'none', label: 'بدون تصویر' },
];
