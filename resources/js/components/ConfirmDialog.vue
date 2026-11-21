
<script setup lang="ts">
import { watch, onBeforeUnmount } from 'vue';

const props = withDefaults(defineProps<{
    modelValue: boolean;
    title?: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'primary';
    loading?: boolean;
}>(), {
    title: 'Are you sure?',
    description: 'Please confirm that you want to continue.',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'danger',
    loading: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
    confirm: [];
    cancel: [];
}>();

function close() {
    if (props.loading) return;

    emit('update:modelValue', false);
    emit('cancel');
}

function confirm() {
    if (props.loading) return;

    emit('confirm');
}

function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape' && props.modelValue) {
        close();
    }
}

watch(
    () => props.modelValue,
    (open) => {
        if (typeof window === 'undefined') return;

        if (open) {
            window.addEventListener('keydown', handleKeydown);
        } else {
            window.removeEventListener('keydown', handleKeydown);
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('keydown', handleKeydown);
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="modelValue"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-[3px]"
                @click.self="close"
            >
                <Transition
                    appear
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="translate-y-2 scale-[0.98] opacity-0"
                    enter-to-class="translate-y-0 scale-100 opacity-100"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="translate-y-0 scale-100 opacity-100"
                    leave-to-class="translate-y-2 scale-[0.98] opacity-0"
                >
                    <div
                        v-if="modelValue"
                        role="alertdialog"
                        aria-modal="true"
                        :aria-labelledby="'confirm-dialog-title'"
                        :aria-describedby="'confirm-dialog-description'"
                        class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-2xl shadow-slate-950/15 dark:border-navy-600 dark:bg-navy-750"
                    >
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex size-11 shrink-0 items-center justify-center rounded-full"
                                    :class="variant === 'danger'
                                        ? 'bg-error/10 text-error'
                                        : 'bg-primary/10 text-primary'"
                                >
                                    <svg
                                        v-if="variant === 'danger'"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="size-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M12 9v4" />
                                        <path d="M12 17h.01" />
                                        <path d="M10.3 3.9 1.8 18.1a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                                    </svg>

                                    <svg
                                        v-else
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="size-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M12 8v4l3 2" />
                                        <circle cx="12" cy="12" r="10" />
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1 pt-0.5">
                                    <h2
                                        id="confirm-dialog-title"
                                        class="text-base font-semibold tracking-tight text-slate-900 dark:text-navy-50"
                                    >
                                        {{ title }}
                                    </h2>

                                    <p
                                        id="confirm-dialog-description"
                                        class="mt-1.5 text-sm leading-relaxed text-slate-500 dark:text-navy-300"
                                    >
                                        {{ description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-6 py-4 dark:border-navy-600 dark:bg-navy-800"
                        >
                            <button
                                type="button"
                                :disabled="loading"
                                class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100 dark:hover:bg-navy-600"
                                @click="close"
                            >
                                {{ cancelText }}
                            </button>

                            <button
                                type="button"
                                :disabled="loading"
                                class="inline-flex min-w-[90px] items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors disabled:cursor-not-allowed disabled:opacity-60"
                                :class="variant === 'danger'
                                    ? 'bg-error hover:bg-error/90'
                                    : 'bg-primary hover:bg-primary-focus'"
                                @click="confirm"
                            >
                                <svg
                                    v-if="loading"
                                    class="size-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        class="opacity-25"
                                    />
                                    <path
                                        d="M21 12a9 9 0 0 0-9-9"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    />
                                </svg>

                                {{ loading ? 'Please wait...' : confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
