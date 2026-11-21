
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Detect system dark mode preference immediately --}}
        <script>
            (function () {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia(
                        '(prefers-color-scheme: dark)'
                    ).matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Initial page background and loading spinner styles --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }

            /* Full-page loading overlay */
            #app-loading {
                position: fixed;
                inset: 0;
                z-index: 99999;
                display: flex;
                align-items: center;
                justify-content: center;
                background: oklch(1 0 0);
                transition: opacity .2s ease, visibility .2s ease;
            }

            html.dark #app-loading {
                background: oklch(0.145 0 0);
            }

            #app-loading.loaded {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
            }

            /* Three-dot spinner */
            .app-loading-spinner {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .app-loading-spinner span {
                width: 12px;
                height: 12px;
                flex-shrink: 0;
                border-radius: 9999px;
                background-color: #4f46e5;
                animation: app-loading-ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
            }

            .app-loading-spinner span:nth-child(2) {
                animation-delay: .2s;
            }

            .app-loading-spinner span:nth-child(3) {
                animation-delay: .4s;
            }

            @keyframes app-loading-ping {
                75%, 100% {
                    transform: scale(2);
                    opacity: 0;
                }
            }

            /* Reduced motion accessibility */
            @media (prefers-reduced-motion: reduce) {
                .app-loading-spinner span {
                    animation: none;
                    opacity: .7;
                }

                #app-loading {
                    transition: none;
                }
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite([
            'resources/css/app.css',
            'resources/js/app.ts',
            "resources/js/pages/{$page['component']}.vue"
        ])

        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>

    <body class="is-header-blur font-sans antialiased">

        {{-- Initial application loading spinner --}}
        <div id="app-loading" role="status" aria-label="Loading">
            <div class="app-loading-spinner">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </div>
        </div>

        {{-- Inertia application --}}
        <x-inertia::app />


        {{-- Automatically remove loading spinner after Vue mounts --}}
        <script>
            (function () {
                let observer = null;
                let fallbackTimer = null;
                let loaderRemoved = false;

                function hideLoader() {
                    if (loaderRemoved) {
                        return;
                    }

                    loaderRemoved = true;

                    if (observer) {
                        observer.disconnect();
                        observer = null;
                    }

                    if (fallbackTimer) {
                        clearTimeout(fallbackTimer);
                    }

                    const loader = document.getElementById('app-loading');

                    if (!loader) {
                        return;
                    }

                    loader.classList.add('loaded');

                    setTimeout(function () {
                        loader.remove();
                    }, 250);
                }

                function initializeLoader() {
                    const app = document.getElementById('app');

                    if (!app) {
                        hideLoader();
                        return;
                    }

                    // If the application already contains rendered content.
                    if (app.children.length > 0) {
                        hideLoader();
                        return;
                    }

                    // Observe Vue mounting into the Inertia application.
                    observer = new MutationObserver(function () {
                        if (app.children.length > 0) {
                            hideLoader();
                        }
                    });

                    observer.observe(app, {
                        childList: true,
                        subtree: true
                    });

                    // Safety fallback to prevent a permanently visible spinner.
                    fallbackTimer = setTimeout(function () {
                        hideLoader();
                    }, 15000);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener(
                        'DOMContentLoaded',
                        initializeLoader,
                        { once: true }
                    );
                } else {
                    initializeLoader();
                }
            })();
        </script>

    </body>
</html>
