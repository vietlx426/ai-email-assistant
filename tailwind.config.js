/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
    ],
    theme: {
        extend: {
            colors: {
                'primary': '#4F46E5',
                'primary-dark': '#4338CA',
                'secondary': '#7C3AED',
                'accent': '#06B6D4',
                'success': '#10B981',
                'warning': '#F59E0B',
                'error': '#EF4444',
            }
        },
    },
    plugins: [],
}