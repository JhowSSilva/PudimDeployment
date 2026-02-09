import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/js/**/*.vue',
    ],

    // Safelist dynamic classes used in dashboard.blade.php language cards
    safelist: [
        'bg-primary-900/30', 'bg-primary-800/40', 'ring-primary-500/20', 'text-primary-400',
        'bg-green-900/30', 'bg-green-800/40', 'ring-green-500/20', 'text-green-400',
        'bg-yellow-900/30', 'bg-yellow-800/40', 'ring-yellow-500/20', 'text-yellow-400',
        'bg-neutral-800/30', 'bg-neutral-800/40', 'ring-neutral-500/20', 'text-neutral-400',
    ],

    theme: {
        // ── Standardized Typography Scale ──────────────────────
        fontSize: {
            'xs':   ['0.75rem',  { lineHeight: '1rem' }],      // 12px — captions, badges
            'sm':   ['0.875rem', { lineHeight: '1.25rem' }],   // 14px — body small, labels, buttons
            'base': ['1rem',     { lineHeight: '1.5rem' }],    // 16px — body default
            'lg':   ['1.125rem', { lineHeight: '1.75rem' }],   // 18px — section titles, card headers
            'xl':   ['1.25rem',  { lineHeight: '1.75rem' }],   // 20px — h4
            '2xl':  ['1.5rem',   { lineHeight: '2rem' }],      // 24px — h3
            '3xl':  ['1.875rem', { lineHeight: '2.25rem' }],   // 30px — h2
            '4xl':  ['2.25rem',  { lineHeight: '2.5rem' }],    // 36px — h1
            '5xl':  ['3rem',     { lineHeight: '1' }],         // 48px — hero
        },

        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', 'Fira Code', 'Consolas', 'monospace'],
            },

            // ── Unified Color System ─────────────────────────────
            // Brand = amber/caramelo. "primary" IS amber (merged).
            // All raw Tailwind color usage (red-*, green-*, blue-*) should
            // use these semantic tokens instead.
            colors: {
                // Primary Brand Colors (Caramelo/Amber — unified)
                primary: {
                    50:  '#fffbeb',
                    100: '#fef3c7',
                    200: '#fde68a',
                    300: '#fcd34d',
                    400: '#fbbf24',
                    500: '#f59e0b',
                    600: '#d97706',
                    700: '#b45309',
                    800: '#92400e',
                    900: '#78350f',
                    950: '#451a03',
                },
                // Neutral/Gray scale
                neutral: {
                    50:  '#fafafa',
                    100: '#f5f5f5',
                    200: '#e5e5e5',
                    300: '#d4d4d4',
                    400: '#a3a3a3',
                    500: '#737373',
                    600: '#525252',
                    700: '#404040',
                    800: '#262626',
                    900: '#171717',
                    950: '#0a0a0a',
                },
                // Success (green)
                success: {
                    50:  '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#16a34a',
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                },
                // Error/Danger (red)
                error: {
                    50:  '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                },
                // Warning (orange-amber)
                warning: {
                    50:  '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316',
                    600: '#ea580c',
                    700: '#c2410c',
                    800: '#9a3412',
                    900: '#7c2d12',
                },
                // Info (blue)
                info: {
                    50:  '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
            },

            // ── Standardized Box Shadows ─────────────────────────
            boxShadow: {
                'xs':      '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                'sm':      '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1)',
                'md':      '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1)',
                'lg':      '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1)',
                'xl':      '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1)',
                '2xl':     '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
                'primary': '0 10px 25px -5px rgba(245, 158, 11, 0.3)',
                'success': '0 10px 25px -5px rgba(34, 197, 94, 0.3)',
                'error':   '0 10px 25px -5px rgba(239, 68, 68, 0.3)',
                'warning': '0 10px 25px -5px rgba(249, 115, 22, 0.3)',
            },

            // ── Standardized Border Radius ───────────────────────
            borderRadius: {
                'none': '0',
                'sm':   '0.25rem',    // 4px  — badges, small pills
                DEFAULT: '0.375rem',  // 6px  — inputs, buttons (default)
                'md':   '0.375rem',   // 6px
                'lg':   '0.5rem',     // 8px  — cards, modals
                'xl':   '0.75rem',    // 12px — large cards
                '2xl':  '1rem',       // 16px
                '3xl':  '1.5rem',     // 24px
                'full': '9999px',     // pills, avatars
            },

            // ── Standardized Animations ──────────────────────────
            animation: {
                'pulse-slow':  'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'bounce-slow': 'bounce 3s infinite',
                'spin-slow':   'spin 2s linear infinite',
                'fade-in':     'fadeIn 0.2s ease-out',
                'slide-up':    'slideUp 0.2s ease-out',
                'slide-down':  'slideDown 0.2s ease-out',
            },
            keyframes: {
                fadeIn:    { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                slideUp:   { '0%': { opacity: '0', transform: 'translateY(8px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                slideDown: { '0%': { opacity: '0', transform: 'translateY(-8px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
            },

            // ── Standardized Transitions ─────────────────────────
            transitionDuration: {
                '0':   '0ms',
                '150': '150ms',  // micro interactions (hover color)
                '200': '200ms',  // standard (DEFAULT for all components)
                '300': '300ms',  // modals, panels
            },
            transitionTimingFunction: {
                'spring': 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
            },
        },
    },

    plugins: [forms],
};
