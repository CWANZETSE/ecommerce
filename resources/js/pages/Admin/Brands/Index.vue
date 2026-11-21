<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_brands_index,
    admin_brands_create,
    admin_brands_edit,
    admin_brands_destroy,
    admin_dashboard,
} from '@/routes';
import Pagination from '@/components/Pagination.vue';
import CirclesSVG from '@/components/SVG/circlesSVG.vue';

interface Brand {
    id: string;
    name: string;
    slug: string;
    logo_url: string | null;
    is_active: boolean;
    products_count: number;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<PageProps & { brands: Paginator<Brand> }>();

function destroy(id: string, name: string) {
    if (confirm(`Delete brand "${name}"?`)) {
        router.delete(admin_brands_destroy.url(id));
    }
}
</script>

<template>
    <Head title="Brands" />

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
                        Brands
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
                            Brands
                        </li>
                    </ol>
                </div>
                <div
                    class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <CirclesSVG />
                </div>
            </div>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300">
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Logo
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Name
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Products
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Status
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                        <!-- Loop through brands.data instead of brands -->
                        <tr
                            v-for="brand in brands.data"
                            :key="brand.id"
                            class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                        >
                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <div class="h-10 w-10 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 dark:border-navy-500 dark:bg-navy-800">
                                    <img
                                        v-if="brand.logo_url"
                                        :src="brand.logo_url"
                                        :alt="brand.name"
                                        class="h-full w-full object-contain p-0.5"
                                    />
                                    <span
                                        v-else
                                        class="flex h-full w-full items-center justify-center text-slate-400 dark:text-navy-300"
                                    >
                                        <svg
                                            class="size-5 shrink-0"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                        >
                                            <rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.5"></rect>
                                            <circle cx="8.5" cy="9.5" r="1.5" fill="currentColor"></circle>
                                            <path d="M21 16l-4.5-4.5-6 6-2.5-2.5L3 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                        </svg>
                                    </span>
                                </div>
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                {{ brand.name }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                {{ brand.products_count }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <span
                                    :class="brand.is_active
                                        ? 'bg-success/10 text-success'
                                        : 'bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-300'"
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                >
                                    {{ brand.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="admin_brands_edit.url(brand.id)"
                                        class="inline-flex size-9 items-center justify-center rounded-lg border border-primary/20 bg-primary/[0.06] text-primary transition-colors hover:bg-primary/15 focus:bg-primary/15 active:bg-primary/20 dark:border-accent-light/20 dark:bg-accent-light/[0.06] dark:text-accent-light dark:hover:bg-accent-light/15"
                                        title="Edit"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="size-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                    </Link>

                                    <button
                                        type="button"
                                        class="inline-flex size-9 items-center justify-center rounded-lg border border-secondary/20 bg-secondary/[0.06] text-secondary transition-colors hover:bg-secondary/15 focus:bg-secondary/15 active:bg-secondary/20 dark:border-secondary-light/20 dark:bg-secondary-light/[0.06] dark:text-secondary-light dark:hover:bg-secondary-light/15"
                                        title="Delete"
                                        @click="destroy(brand.id, brand.name)"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="size-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Check the length of brands.data for empty state -->
                        <tr v-if="brands.data.length === 0">
                            <td
                                colspan="5"
                                class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300"
                            >
                                No brands yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Add the pagination component here -->
            <Pagination :paginator="brands" />
            
        </div>
    </div>
</template>