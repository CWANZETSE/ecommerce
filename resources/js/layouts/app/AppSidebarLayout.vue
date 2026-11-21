<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { onMounted, onBeforeUnmount, ref } from 'vue';

import CustomSidebar from '@/components/CustomSidebar.vue';
import CustomAppHeader from '@/components/CustomAppHeader.vue';
import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.vue';
import { useToast } from '@/composables/useToast';
import { Toaster } from '@/components/ui/sonner';

interface Props {
    title?: string;
    breadcrumbs?: { label: string; href?: string }[];
}

defineProps<Props>();

// Mobile sidebar state.
const sidebarOpen = ref(false);

// Global loading state.
const isLoading = ref(false);

// Initialize global toast watcher.
useToast();

// Inertia event listener cleanup callbacks.
let removeStartListener: (() => void) | undefined;
let removeFinishListener: (() => void) | undefined;

onMounted(() => {
    // Show loading overlay when an Inertia request starts.
    removeStartListener = router.on('start', () => {
        isLoading.value = true;
    });

    // Hide loading overlay when an Inertia request finishes.
    removeFinishListener = router.on('finish', () => {
        isLoading.value = false;
    });
});

onBeforeUnmount(() => {
    removeStartListener?.();
    removeFinishListener?.();

    isLoading.value = false;
});
</script>

<template>
    <Head :title="title ?? 'Laravel'" />

    <div
        id="root"
        class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900"
    >
        <!-- Sidebar -->
        <CustomSidebar
            :open="sidebarOpen"
            @close="sidebarOpen = false"
        />

        <!-- Header -->
        <CustomAppHeader
            :breadcrumbs="breadcrumbs"
            @toggle-sidebar="sidebarOpen = !sidebarOpen"
        />

        <!-- Main content -->
        <main class="main-content w-full pb-8 md:ml-[260px]">
            <div class="p-4 sm:p-5 lg:p-6">
                <slot />
            </div>
        </main>

        <!-- Global loading overlay -->
        <GlobalLoadingOverlay :show="isLoading" />

        <!-- Global notifications -->
        <Toaster
            position="top-center"
            :close-button="true"
        />
    </div>
</template>