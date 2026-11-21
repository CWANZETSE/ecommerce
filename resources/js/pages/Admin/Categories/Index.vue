<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_categories_index,
    admin_categories_create,
    admin_categories_edit,
    admin_categories_destroy,
} from '@/routes';
import Pagination from '@/components/Pagination.vue';

interface Subcategory {
    id: string;
    name: string;
    slug: string;
    image_url: string | null;
    is_active: boolean;
    products_count: number;
}
interface Category {
    id: string;
    name: string;
    slug: string;
    image_url: string | null;
    is_active: boolean;
    sort_order: number;
    products_count: number;
    children: Subcategory[];
}

// Corrected type to match Laravel Paginator
interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<PageProps & { categories: Paginator<Category> }>();

function destroy(id: string, name: string) {
    if (confirm(`Delete category "${name}"?`)) {
        router.delete(admin_categories_destroy.url(id));
    }
}
</script>

<template>
    <Head title="Categories" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div
            class="p-6 border-0 shadow-md dark:shadow-dark-md mb-6 py-4 bg-primary/10 dark:bg-primary/10 overflow-hidden rounded-md border-none !shadow-none dark:!shadow-none"
            style="border-radius: 12px"
        >
            <div class="flex items-center justify-between gap-6 relative">
                <div>
                    <h4
                        class="font-semibold text-xl text-dark dark:text-slate-800 mb-3"
                    >
                        Categories
                    </h4>
                    
                </div>
                <div
                    class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <StripsSVG />
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between py-1 lg:py-1">
            <div class="flex items-center space-x-4">
                
                
            </div>
            <Link
                :href="admin_categories_create.url()"
                class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
            >
                New Category
            </Link>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300">
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Image
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Name
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Products
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Sort
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
                        <!-- Loop over categories.data because it's paginated now -->
                        <template v-for="root in categories.data" :key="root.id">
                            <!-- Parent row -->
                            <tr class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]">
                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                    <div class="h-10 w-10 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 dark:border-navy-500 dark:bg-navy-800">
                                        <img
                                            v-if="root.image_url"
                                            :src="root.image_url"
                                            :alt="root.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                </td>

                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                    {{ root.name }}
                                </td>

                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                    {{ root.products_count }}
                                </td>

                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                    {{ root.sort_order }}
                                </td>

                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                    <span
                                        :class="root.is_active
                                            ? 'bg-success/10 text-success'
                                            : 'bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-300'"
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                    >
                                        {{ root.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="admin_categories_edit.url({ category: root.id })"
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
                                            @click="destroy(root.id, root.name)"
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

                            <!-- Child rows -->
                            <tr
                                v-for="child in root.children"
                                :key="child.id"
                                class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                            >
                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                    <div class="flex items-center gap-2 pl-3">
                                        <span class="select-none text-slate-400 dark:text-navy-300">↳</span>

                                        <div class="h-8 w-8 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 dark:border-navy-500 dark:bg-navy-800">
                                            <img
                                                v-if="child.image_url"
                                                :src="child.image_url"
                                                :alt="child.name"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 pl-10 font-medium text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                    {{ child.name }}
                                </td>

                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                    {{ child.products_count }}
                                </td>

                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-400 dark:border-slate-700 dark:text-navy-400">
                                    —
                                </td>

                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                    <span
                                        :class="child.is_active
                                            ? 'bg-success/10 text-success'
                                            : 'bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-300'"
                                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                    >
                                        {{ child.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="admin_categories_edit.url(child.id)"
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
                                            @click="destroy(child.id, child.name)"
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
                        </template>

                        <tr v-if="categories.data.length === 0">
                            <td
                                colspan="6"
                                class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300"
                            >
                                No categories yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Cleaned up Pagination Component -->
            <Pagination :paginator="categories" />
            
        </div>
    </div>
</template>