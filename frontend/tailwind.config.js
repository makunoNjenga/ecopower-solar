/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        // Eco Power Tech Global Brand Colors
        primary: {
          light: '#4CAF50',  // Light Green
          DEFAULT: '#2E7D32', // Primary Green
          dark: '#1B5E20'    // Dark Green
        },
        secondary: {
          light: '#FFF176',  // Light Yellow
          DEFAULT: '#FBC02D', // Primary Yellow
          dark: '#F57C00'    // Dark Yellow
        },
        accent: {
          light: '#29B6F6',  // Light Blue
          DEFAULT: '#0288D1', // Primary Blue
          dark: '#01579B'    // Dark Blue
        },
        neutral: {
          white: '#FFFFFF',
          light: '#F5F5F5',   // Light Grey
          charcoal: '#212121', // Charcoal
          dark: '#263238'     // Dark Charcoal
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Poppins', 'system-ui', 'sans-serif'],
      },
      animation: {
        'fade-in': 'fadeIn 0.5s ease-in-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'bounce-gentle': 'bounceGentle 2s infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        bounceGentle: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-5px)' },
        }
      }
    },
  },
  plugins: [],
}