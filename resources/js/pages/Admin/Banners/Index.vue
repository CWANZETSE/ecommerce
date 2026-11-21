<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_banners_create,
    admin_banners_edit,
    admin_banners_destroy,
    admin_dashboard,
} from '@/routes';
import Pagination from '@/components/Pagination.vue';
import BannerSVG from '@/components/SVG/bannerSVG.vue';

interface Banner {
    id: string;
    title: string;
    subtitle: string | null;
    image_url: string | null;
    is_active: boolean;
    sort_order: number;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<PageProps & { banners: Paginator<Banner> }>();

function destroy(banner: Banner) {
    if (confirm(`Delete banner "${banner.title}"?`)) {
        router.delete(admin_banners_destroy.url(banner.id));
    }
}
</script>

<template>
    <Head title="Banners" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div
            class="p-6 border-0 shadow-md dark:shadow-dark-md mb-6 py-4 bg-primary/10 dark:bg-primary/10 overflow-hidden rounded-md border-none !shadow-none dark:!shadow-none"
            style="border-radius: 12px"
        >
            <div class="flex items-center justify-between gap-6 relative">
                <div>
                    <h4
                        class="font-semibold text-xl text-dark dark:text-white mb-3"
                    >
                        Display Banners
                    </h4>
                    <ol
                        class="flex items-center whitespace-nowrap"
                        aria-label="Breadcrumb"
                    >
                        <li class="flex items-center">
                            <Link
                                class="opacity-80 text-sm text-link dark:text-darklink leading-none"
                                :href="admin_dashboard.url()"
                                data-discover="true"
                                >Home</Link
                            >
                        </li>
                        <li>
                            <div
                                class="p-0.5 rounded-full bg-primary/10 dark:bg-primary mx-2.5 flex items-center"
                            ></div>
                        </li>
                        <li
                            class="flex items-center text-sm text-default dark:text-darklink leading-none"
                            aria-current="page"
                        >
                            Banners
                        </li>
                    </ol>
                </div>
                <div
                    class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <BannerSVG />
                </div>
            </div>
        </div>
        <Link
                :href="admin_banners_create.url()"
                class="btn bg-primary/10 font-medium text-primary mb-2 hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
            >
                New Banner
            </Link>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-5">
            <!-- Loop through banners.data -->
            <div
                v-for="b in banners.data"
                :key="b.id"
                class="card overflow-hidden flex flex-col"
            >
                <div class="h-40 w-full overflow-hidden bg-slate-100 dark:bg-navy-900">
                    <img
                        v-if="b.image_url"
                        :src="b.image_url"
                        :alt="b.title"
                        class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center text-slate-300 dark:text-navy-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="p-4 sm:p-5 flex flex-col grow justify-between space-y-3">
                    <div class="space-y-1">
                        <h3 class="font-medium text-slate-700 dark:text-navy-100">{{ b.title }}</h3>
                        <p v-if="b.subtitle" class="text-xs text-slate-400 dark:text-navy-300 line-clamp-1">{{ b.subtitle }}</p>
                        <p class="text-xs text-slate-400 dark:text-navy-300 pt-1">
                            Order: {{ b.sort_order }} ·
                            <span :class="b.is_active ? 'badge space-x-2.5 rounded-full bg-success/10 text-success dark:bg-success/15' : 'badge space-x-2.5 rounded-full bg-error/10 text-error dark:bg-error/15'">
                                {{ b.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    <div class="flex items-center space-x-3 pt-2 border-t border-slate-150 dark:border-navy-600 text-sm">
                        <Link
                            :href="admin_banners_edit.url(b.id)"
                            class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                        >
                            Edit
                        </Link>
                        <button
                            type="button"
                            class="btn border border-secondary/30 bg-secondary/10 font-medium text-secondary hover:bg-secondary/20 focus:bg-secondary/20 active:bg-secondary/25 dark:border-secondary-light/30 dark:bg-secondary-light/10 dark:text-secondary-light dark:hover:bg-secondary-light/20 dark:focus:bg-secondary-light/20 dark:active:bg-secondary-light/25"
                            @click="destroy(b)"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Check banners.data.length -->
            <div
                v-if="banners.data.length === 0"
                class="col-span-full card p-8 text-center text-slate-400 dark:text-navy-300"
            >
                No banners yet.
            </div>
        </div>

        <!-- Add pagination component for the grid -->
        <div v-if="banners.data.length > 0" class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
            <Pagination :paginator="banners" />
        </div>
    </div>
</template>