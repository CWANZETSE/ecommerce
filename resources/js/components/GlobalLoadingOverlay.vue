
<template>
    <Transition name="overlay">
        <div
            v-if="show"
            class="fixed inset-0 z-[1080] flex items-center justify-center bg-white px-4 backdrop-blur-[3px] dark:bg-navy-700"
            role="status"
            aria-label="Loading"
            aria-busy="true"
        >
            <!-- Compact loading card -->
            <div
                class="flex w-full max-w-[260px] items-center gap-4 rounded-2xl  bg-white/95 px-5 py-4 shadow-[0_12px_40px_rgba(15,23,42,0.16)] ring-1 ring-black/[0.03] dark:border-slate-700/70 dark:bg-navy-750 "
            >
                <!-- Indigo pulse loader -->
                <div
                    class="loader"
                    aria-hidden="true"
                ></div>

                <!-- Loading message -->
                <div class="min-w-0">
                    <p
                        class="text-sm font-semibold leading-5 text-slate-500 dark:text-grey-300"
                    >
                        Please wait...
                    </p>

                    <p
                        class="mt-0.5 text-xs leading-4 text-slate-500 dark:text-slate-400"
                    >
                        Processing your request
                    </p>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
defineProps<{
    show: boolean;
}>();
</script>

<style scoped>
/* Smooth overlay appearance */
.overlay-enter-active,
.overlay-leave-active {
    transition: opacity 0.22s ease;
}

.overlay-enter-from,
.overlay-leave-to {
    opacity: 0;
}

/* Compact indigo pulse loader */
.loader {
    --loader-color: #4f46e5;

    position: relative;
    display: inline-block;
    box-sizing: border-box;
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    border-radius: 50%;
    color: var(--loader-color);
    background: currentColor;
    animation: loader-p 1s ease-in-out infinite;
}

@keyframes loader-p {
    0% {
        transform: scale(0);
        opacity: 1;
    }

    100% {
        transform: scale(1);
        opacity: 0;
    }
}

/* Dark mode indigo */
:global(.dark) .loader {
    --loader-color: #818cf8;
}

/* Reduced motion accessibility */
@media (prefers-reduced-motion: reduce) {
    .loader {
        animation: loader-calm 1.8s ease-in-out infinite !important;
    }

    @keyframes loader-calm {
        50% {
            opacity: 0.45;
        }
    }

    .overlay-enter-active,
    .overlay-leave-active {
        transition: none;
    }
}
</style>
