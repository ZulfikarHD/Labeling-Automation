import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
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
        'menu': "url('/img/menu.jpg')",
        'content': "url('/img/content.jpg')",
        'content2': "url('/img/content2.jpg')",
        'scatter-forcefield': "url('/img/scattered-forcefields.svg')",
        'geometric': "url('/img/geometric.svg')",
      },
    },
  },

  plugins: [forms],
};
