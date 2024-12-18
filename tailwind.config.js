const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {

  darkMode: 'selector',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            backgroundImage: {
                'menu'  : "url('/img/menu.jpg')",
                'content'  : "url('/img/content.jpg')",
                'content2'  : "url('/img/content2.jpg')",
                'scatter-forcefield' : "url('/img/scattered-forcefields.svg')",
                'geometric' : "url('/img/geometric.svg')",
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
