<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_brands_index,
    admin_brands_create,
    admin_brands_edit,
    admin_brands_destroy,
} from '@/routes';

interface Brand {
    id: string;
    name: string;
    slug: string;
    logo_url: string | null;
    is_active: boolean;
    products_count: number;
}

const props = defineProps<PageProps & { brands: Brand[] }>();

function destroy(id: string, name: string) {
    if (confirm(`Delete brand "${name}"?`)) {
        router.delete(admin_brands_destroy.url(id));
    }
}
</script>

<template>
    <Head title="Brands" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    Brands
                </h2>
                <div class="hidden h-full py-1 sm:flex">
                    <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
                </div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <Link
                            class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            :href="admin_brands_index.url()"
                        >
                            Catalog
                        </Link>
                        <svg
                            x-ignore
                            xmlns="http://www.w3.org/2000/svg"
                            class="size-4 text-slate-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                    <li class="text-slate-500 dark:text-navy-300">Brands</li>
                </ul>
            </div>
            <Link
                :href="admin_brands_create.url()"
                class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
            >
                New Brand
            </Link>
        </div>

        <div class="mt-4 overflow-hidden rounded-lg border border-slate-150 dark:border-navy-600">
    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
        <table class="is-zebra w-full text-left">
            <thead>
                <tr>
                    <th class="whitespace-nowrap rounded-l-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                        Logo
                    </th>
                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                        Name
                    </th>
                    
                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                        Products
                    </th>
                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                        Status
                    </th>
                    <th class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="brand in brands" :key="brand.id">
                    <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                        <div class="h-10 w-10 overflow-hidden rounded-lg border border-slate-200 bg-slate-100 dark:border-navy-500 dark:bg-navy-800">
                            <img
                                v-if="brand.logo_url"
                                :src="brand.logo_url"
                                :alt="brand.name"
                                class="h-full w-full object-contain p-0.5"
                            />
                            <span v-else class="flex h-full w-full items-center justify-center text-xs text-slate-400 dark:text-navy-300">
                                <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.5"></rect>
    <circle cx="8.5" cy="9.5" r="1.5" fill="currentColor"></circle>
    <path d="M21 16l-4.5-4.5-6 6-2.5-2.5L3 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
</svg>
                            </span>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                        {{ brand.name }}
                    </td>
                    
                    <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                        {{ brand.products_count }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                        <span
                            :class="brand.is_active ? 'bg-success/10 text-success' : 'bg-slate-200 text-slate-600 dark:bg-navy-600 dark:text-navy-300'"
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                        >
                            {{ brand.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap rounded-r-lg px-4 py-3 text-right sm:px-5">
                        <div class="flex space-x-2">
                            <Link
                                :href="admin_brands_edit.url(brand.id)"
                                class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                                title="Edit"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </Link>
                            <button
                                type="button"
                                class="btn border border-secondary/30 bg-secondary/10 font-medium text-secondary hover:bg-secondary/20 focus:bg-secondary/20 active:bg-secondary/25 dark:border-secondary-light/30 dark:bg-secondary-light/10 dark:text-secondary-light dark:hover:bg-secondary-light/20 dark:focus:bg-secondary-light/20 dark:active:bg-secondary-light/25"
                                title="Delete"
                                @click="destroy(brand.id, brand.name)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

                <tr v-if="brands.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 dark:text-navy-300">
                        No brands yet.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
    </div>
</template>