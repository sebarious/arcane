import type { Config } from 'tailwindcss'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

export default {
    darkMode: 'class',
    content: [
        './resources/**/*.{js,ts,vue,blade.php}',
        './app/Filament/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                arcane: {
                    bg:       '#0a0a0f',
                    surface:  '#12121a',
                    elevated: '#1a1a26',
                    border:   '#2a2a3a',
                    muted:    '#6b6b80',
                    text:     '#e8e8f0',
                    accent:   '#a78bfa',
                    accent2:  '#22d3ee',
                    common:    '#9ca3af',
                    rare:      '#60a5fa',
                    super:     '#a78bfa',
                    legendary: '#f59e0b',
                    mythic:    '#ef4444',
                },
            },
            fontFamily: {
                sans:    ['Inter', 'ui-sans-serif', 'system-ui'],
                display: ['Cinzel', 'serif'],
            },
        },
    },
    plugins: [forms, typography],
} satisfies Config
