import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const shopColor = (name) => `rgb(var(--shop-${name}-rgb) / <alpha-value>)`;

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
                sans: ['Vazirmatn', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                shop: {
                    primary: shopColor('primary'),
                    'primary-hover': shopColor('primary-hover'),
                    'primary-dark': shopColor('primary-dark'),
                    'primary-deeper': shopColor('primary-deeper'),
                    accent: shopColor('accent'),
                    'accent-hover': shopColor('accent-hover'),
                    surface: shopColor('surface'),
                    background: shopColor('background'),
                    text: shopColor('text'),
                    muted: shopColor('muted'),
                    'on-hero': shopColor('on-hero'),
                    border: shopColor('border'),
                    'hero-from': shopColor('hero-from'),
                    'hero-via': shopColor('hero-via'),
                    'hero-to': shopColor('hero-to'),
                },
            },
        },
    },

    plugins: [forms],
};
