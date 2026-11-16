import { router } from '@inertiajs/vue3';
import type { Flash } from '@/types';

type FlashKey = keyof Flash;

/**
 * Listen to Inertia navigation events and dispatch a flash message
 * via alertify whenever the server returns one in `props.flash`.
 *
 * Called once from app.ts.
 */
export function initializeFlashToast(): void {
    router.on('success', (event) => {
        const page = event.detail.page;
        const flash = (page.props.flash ?? {}) as Flash;

        (['success', 'error', 'warning', 'info'] as FlashKey[]).forEach((key) => {
            const message = flash[key];
            if (typeof message === 'string' && message.length > 0) {
                notify(key, message);
            }
        });
    });
}

function notify(type: FlashKey, message: string): void {
    const alertify = (window as unknown as { alertify?: AlertifyApi }).alertify;
    if (!alertify) {
        console.warn('[flashToast] alertify is not loaded on window');
        return;
    }

    switch (type) {
        case 'success':
            alertify.success(message);
            break;
        case 'error':
            alertify.error(message);
            break;
        case 'warning':
            alertify.warning(message);
            break;
        case 'info':
        default:
            alertify.message(message);
            break;
    }
}

/**
 * Minimal shape of alertify's API that we use. Add more methods if needed.
 */
interface AlertifyApi {
    success: (msg: string) => void;
    error: (msg: string) => void;
    warning: (msg: string) => void;
    message: (msg: string) => void;
}