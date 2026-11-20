/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.{vue,js,ts,jsx,tsx}',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                    950: '#172554',
                },
                surface: {
                    light: '#FBFBFA',
                    cardLight: '#FFFFFF',
                    subtleLight: '#F3F3F1',
                    borderLight: '#E9E9E6',
                    dark: '#121214',
                    cardDark: '#18181B',
                    subtleDark: '#27272A',
                    borderDark: '#2E2E33',
                },
                priority: {
                    urgent: '#EF4444',
                    high: '#F59E0B',
                    medium: '#3B82F6',
                    low: '#9CA3AF',
                },
            },
            fontFamily: {
                sans: ['Inter', 'Vazirmatn', 'system-ui', '-apple-system', 'sans-serif'],
                vazir: ['Vazirmatn', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
