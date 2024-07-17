/** @type {import('tailwindcss').Config} */
const colors = require('tailwindcss/colors');
import preset from './vendor/filament/support/tailwind.config.preset';

export default {
    presets: [preset],
    darkMode: 'false',
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        ],
  theme: {
    extend: {
        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            black: colors.black,
            white: colors.white,
            gray: colors.gray,
            emerald: colors.emerald,
            indigo: colors.indigo,
            yellow: colors.yellow,
            danger: colors.rose,
            primary: colors.blue,
            success: colors.green,
            warning: colors.yellow,
            'dap-primary': '#2E3192',
            'dap-secondary': '#E9C103',
        }
    },
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
}

