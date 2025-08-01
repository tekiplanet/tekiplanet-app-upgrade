/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#0033AB',
          dark: '#141F78',
          darker: '#141F77',
        },
        secondary: {
          DEFAULT: '#F25100',
        }
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
} 