<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import CustomSidebar from '@/components/CustomSidebar.vue';
import CustomAppHeader from '@/components/CustomAppHeader.vue';

interface Props {
    title?: string;
    breadcrumbs?: { label: string; href?: string }[];
}

defineProps<Props>();

// Mobile sidebar state — only affects <md, ignored on larger screens
const sidebarOpen = ref(false);
</script>

<template>
    <Head :title="title ?? 'Laravel'" />

    <div id="root" class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900">
        <!-- Sidebar (receives open + close) -->
        <CustomSidebar
            :open="sidebarOpen"
            @close="sidebarOpen = false"
        />

        <!-- Header (emits open) -->
        <CustomAppHeader
            :breadcrumbs="breadcrumbs"
            @toggle-sidebar="sidebarOpen = !sidebarOpen"
        />

        <!-- Main content -->
        <main class="main-content w-full md:ml-[260px] pb-8">
            <div class="p-4 sm:p-5 lg:p-6">
                <slot />
            </div>
        </main>
    </div>
</template>