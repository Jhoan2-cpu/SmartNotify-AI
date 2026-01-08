/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./includes/**/*.php",
    "./assets/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'wp-blue': '#2271b1',
        'wp-blue-dark': '#135e96',
        'sentiment-positive': '#10b981',
        'sentiment-neutral': '#6b7280',
        'sentiment-negative': '#ef4444',
      },
      fontFamily: {
        sans: ['-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'Oxygen-Sans', 'Ubuntu', 'Cantarell', '"Helvetica Neue"', 'sans-serif'],
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
  // Prefix para evitar conflictos con WordPress
  prefix: 'sn-',
  // No usar important para mejor compatibilidad
  important: false,
  // Configuración para WordPress admin
  corePlugins: {
    preflight: false, // Desactivar reset CSS para no interferir con WordPress
  },
}
