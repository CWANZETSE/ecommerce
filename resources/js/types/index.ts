export * from './auth';
export * from './navigation';
export * from './ui';

export type UserRole = 'admin' | 'customer';

export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    role: UserRole;
    created_at: string;
    updated_at: string;
}

export interface Flash {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

/**
 * Base props every Inertia page receives.
 *
 * Page components must declare a concrete interface that extends this:
 *   interface Props extends SharedProps { products: ... }
 *
 * Vue's compiler cannot resolve generic type aliases or intersection types
 * inside `defineProps<...>()`. Plain interfaces only.
 */
export interface PageProps {
    auth: {
        user: User | null;
    };
    flash: Flash;
    cart_count: number;
}