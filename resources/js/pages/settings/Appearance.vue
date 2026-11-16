<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Appearance settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Appearance settings" />

    <StorefrontLayout>
        <div class="mx-auto max-w-4xl px-4 py-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-150 pb-6 dark:border-navy-600">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">Appearance Settings</h1>
                    <p class="text-sm text-slate-400 dark:text-navy-300 mt-1">Customize how the dashboard and storefront appear on your device.</p>
                </div>
            </div>

            <!-- Content Card Container -->
            <div class="card mt-6 p-6 dark:bg-navy-750 border border-slate-150 dark:border-navy-600">
                <div class="space-y-6">
                    <Heading
                        variant="small"
                        title="Theme Preference"
                        description="Select your preferred system or manual appearance mode."
                    />
                    
                    <div class="pt-2 border-t border-slate-150 dark:border-navy-600">
                        <AppearanceTabs />
                    </div>
                </div>
            </div>
        </div>
    </StorefrontLayout>
</template>