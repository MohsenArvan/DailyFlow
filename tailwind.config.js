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
                    sidebarLight: '#F7F7F5',
                    subtleLight: '#F1F1EF',
                    borderLight: '#E5E5E2',
                    dark: '#121214',
                    cardDark: '#18181B',
                    sidebarDark: '#141416',
                    subtleDark: '#222226',
                    borderDark: '#2A2A30',
                },
                priority: {
                    urgent: '#EF4444',
                    high: '#F59E0B',
                    medium: '#3B82F6',
                    low: '#9CA3AF',
                    none: '#D1D5DB',
                },
            },
            fontFamily: {
                sans: ['Inter', 'Vazirmatn Variable', 'system-ui', '-apple-system', 'sans-serif'],
                vazir: ['Vazirmatn Variable', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
            boxShadow: {
                subtle: '0 1px 2px 0 rgba(0, 0, 0, 0.04), 0 1px 3px 0 rgba(0, 0, 0, 0.02)',
                card: '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05)',
                dropdown: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1)',
                modal: '0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.2)',
            },
        },
    },
    plugins: [],
};
