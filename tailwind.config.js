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
                    50: '#eef0ff',
                    100: '#dfe3ff',
                    200: '#c8ceff',
                    300: '#adb6ff',
                    400: '#929eff',
                    500: '#6675ed',
                    600: '#4c55c7',
                    700: '#3f46ac',
                    800: '#343a90',
                    900: '#2e3375',
                    950: '#20244d',
                },
                surface: {
                    light: '#f5f6fa',
                    cardLight: '#FFFFFF',
                    sidebarLight: '#fafbfe',
                    subtleLight: '#eceff7',
                    borderLight: '#dce1ed',
                    dark: '#111421',
                    cardDark: '#1a1e2f',
                    sidebarDark: '#151827',
                    subtleDark: '#22273b',
                    borderDark: '#32384f',
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
