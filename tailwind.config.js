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
            colors: {
                primary: '#1a146b',
                'primary-container': '#312e81',
                'on-primary': '#ffffff',
                'on-primary-container': '#9c9af4',
                'primary-fixed': '#e2dfff',
                'primary-fixed-dim': '#c3c0ff',
                'on-primary-fixed': '#100563',
                secondary: '#416900',
                'secondary-container': '#acf847',
                'secondary-fixed': '#acf847',
                'secondary-fixed-dim': '#91db2a',
                'on-secondary': '#ffffff',
                'on-secondary-container': '#457000',
                'on-secondary-fixed': '#102000',
                'on-secondary-fixed-variant': '#304f00',
                surface: '#f8f9ff',
                'surface-bright': '#f8f9ff',
                'surface-dim': '#cbdbf5',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#eff4ff',
                'surface-container': '#e5eeff',
                'surface-container-high': '#dce9ff',
                'surface-container-highest': '#d3e4fe',
                'on-surface': '#0b1c30',
                'on-surface-variant': '#474651',
                outline: '#777682',
                'outline-variant': '#c8c5d3',
                tertiary: '#1c2437',
                'tertiary-container': '#31394e',
                error: '#ba1a1a',
                'error-container': '#ffdad6',
                'on-error': '#ffffff',
                'on-error-container': '#93000a',
                background: '#f8f9ff',
                'on-background': '#0b1c30',
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                headline: ['"Plus Jakarta Sans"', 'sans-serif'],
                body: ['"Plus Jakarta Sans"', 'sans-serif'],
                label: ['"Plus Jakarta Sans"', 'sans-serif'],
            },
        },
    },

    plugins: [forms],
};
