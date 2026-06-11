import type { route as routeFn } from 'ziggy-js'

declare global {
    const route: typeof routeFn

    interface Window {
        route: typeof routeFn
    }
}

export interface User {
    id: number
    name: string
    email: string
    role: 'admin' | 'seller' | 'applicant'
}

export interface Store {
    id: number
    slug: string
    name: string
}

export type Rarity = 'common' | 'rare' | 'super' | 'legendary' | 'mythic'

/**
 * Props every Inertia page receives via HandleInertiaRequests middleware.
 * Extend page-specific props from this.
 */
export interface PageProps<T extends Record<string, unknown> = Record<string, unknown>> {
    auth: { user: User | null }
    flash: { success?: string; error?: string }
    ziggy: { location: string; url: string }
    [key: string]: unknown
}

export {}
