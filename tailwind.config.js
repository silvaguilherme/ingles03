import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'alura': {
                    'dark': '#0f1729',
                    'darker': '#0a0e1a',
                    'card': '#1a1f3a',
                    'accent': '#1f90ff',
                    'accent-hover': '#0066cc',
                    'text': '#e0e0e0',
                    'text-muted': '#8b92a1',
                },
            },
        },
    },

    plugins: [forms],
};
