
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['dark' => ($appearance ?? 'system') == 'dark']); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Shop online at Bazario Kenya for quality products at great prices. Enjoy convenient online shopping, secure M-Pesa payments, reliable delivery, and a seamless shopping experience across Kenya.">
        <meta name="keywords" content="Bazario Kenya, online shopping Kenya, ecommerce Kenya, shop online Kenya, online store Kenya, M-Pesa shopping, electronics, fashion, home products, affordable products Kenya">
        <meta property="og:description" content="Discover and shop quality products at Bazario Kenya. Enjoy great prices, convenient online shopping, secure M-Pesa payments, and reliable delivery across Kenya.">

        
        <script>
            (function () {
                const appearance = '<?php echo e($appearance ?? "system"); ?>';

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

        <?php echo app('Illuminate\Foundation\Vite')->fonts(); ?>

        <?php echo app('Illuminate\Foundation\Vite')([
            'resources/css/app.css',
            'resources/js/app.ts',
            "resources/js/pages/{$page['component']}.vue"
        ]); ?>

        <?php if (isset($component)) { $__componentOriginal56673881198e3a2924721e242dee6899 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal56673881198e3a2924721e242dee6899 = $attributes; } ?>
<?php $component = Inertia\View\Components\Head::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('inertia::head'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Inertia\View\Components\Head::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <title><?php echo e(config('app.name', 'Bazario')); ?></title>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal56673881198e3a2924721e242dee6899)): ?>
<?php $attributes = $__attributesOriginal56673881198e3a2924721e242dee6899; ?>
<?php unset($__attributesOriginal56673881198e3a2924721e242dee6899); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal56673881198e3a2924721e242dee6899)): ?>
<?php $component = $__componentOriginal56673881198e3a2924721e242dee6899; ?>
<?php unset($__componentOriginal56673881198e3a2924721e242dee6899); ?>
<?php endif; ?>
    </head>

    <body class="is-header-blur font-sans antialiased">

        
        <div id="app-loading" role="status" aria-label="Loading">
            <div class="app-loading-spinner">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </div>
        </div>

        
        <?php if (isset($component)) { $__componentOriginal1830bdc8f8b965a5838ec47487b5507c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1830bdc8f8b965a5838ec47487b5507c = $attributes; } ?>
<?php $component = Inertia\View\Components\App::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('inertia::app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Inertia\View\Components\App::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1830bdc8f8b965a5838ec47487b5507c)): ?>
<?php $attributes = $__attributesOriginal1830bdc8f8b965a5838ec47487b5507c; ?>
<?php unset($__attributesOriginal1830bdc8f8b965a5838ec47487b5507c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1830bdc8f8b965a5838ec47487b5507c)): ?>
<?php $component = $__componentOriginal1830bdc8f8b965a5838ec47487b5507c; ?>
<?php unset($__componentOriginal1830bdc8f8b965a5838ec47487b5507c); ?>
<?php endif; ?>


        
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
<?php /**PATH D:\SoftwareDevelopment\Vuejs\laravel_vuejs_inertia_tailwind_bootstrap\Ecommerce\resources\views\app.blade.php ENDPATH**/ ?>