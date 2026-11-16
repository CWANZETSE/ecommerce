import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import 'alertifyjs/build/css/alertify.css';
import 'alertifyjs/build/css/themes/default.css';
import alertify from 'alertifyjs';

(window as unknown as { alertify: typeof alertify }).alertify = alertify;
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    
    // ✅ ADD THIS: Tells Inertia where your page components live
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob('./pages/**/*.vue'),
        ),

    
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('Storefront/'):
                return StorefrontLayout;
            case name.startsWith('Customer/'):
                return StorefrontLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();
initializeFlashToast();