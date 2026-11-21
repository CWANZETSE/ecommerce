<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { PageProps } from '@/types';
import { admin_dashboard, admin_products_create, admin_products_edit } from '@/routes';
import Pagination from '@/components/Pagination.vue';
import BoxSVG from '@/components/SVG/boxSVG.vue';

interface Category { id: string; name: string }
interface Variation { id: string; product_id: string; stock_quantity: number }
interface ProductRow {
    is_featured: boolean;
    id: string;
    name: string;
    sku: string;
    price: string;
    is_active: boolean;
    category: Category | null;
    primary_image_url: string | null;
    variations: Variation[];
}

// Add the full Paginator interface here
interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<PageProps & {
    products: Paginator<ProductRow>; // Use the interface here
    categories: Category[];
    filters: Record<string, string | null>;
}>();

const search = ref(props.filters.search ?? '');

watch(search, (val) => {
    router.get('/admin/products', { ...props.filters, search: val || undefined }, {
        preserveState: true,
        replace: true,
    });
});

function totalStock(row: ProductRow): number {
    return row.variations.reduce((sum, v) => sum + v.stock_quantity, 0);
}
</script>

<template>
    <Head title="Products" />

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
                        Products
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
                            Products
                        </li>
                    </ol>
                </div>
                <div
                    class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <BoxSVG />
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <label class="relative">
                <span class="sr-only">Search</span>
                <input v-model="search" type="text" placeholder="Search name or SKU…"
                    class="form-input h-9 w-64 rounded-lg border border-slate-300 bg-transparent px-3 text-xs-plus text-slate-800 placeholder-slate-400 focus:border-primary dark:border-navy-450 dark:text-navy-100 dark:placeholder-navy-300 dark:focus:border-accent" />
            </label>
        </div>

        <div
            class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300">
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Image
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Name
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Featured
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                SKU
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Category
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Variations
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Base Price
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Stock (Total)
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
                        <tr v-for="p in products.data" :key="p.id"
                            class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]">
                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <div
                                    class="size-10 overflow-hidden rounded-lg border border-slate-200 bg-slate-50 dark:border-navy-500 dark:bg-navy-800">
                                    <img v-if="p.primary_image_url" :src="p.primary_image_url" :alt="p.name"
                                        class="h-full w-full object-cover" />

                                    <div v-else
                                        class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-500 dark:bg-navy-700 dark:text-navy-200">
                                        <span class="text-sm font-semibold">
                                            {{ p.name.charAt(0) }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <p class="min-w-[140px] font-semibold text-slate-700 dark:text-navy-100">
                                    {{ p.name }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <span :class="p.is_featured
                                    ? 'bg-success/10 text-success'
                                    : 'bg-slate-100 text-slate-500 dark:bg-navy-600 dark:text-navy-300'"
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold">
                                    {{ p.is_featured ? 'Yes' : 'No' }}
                                </span>
                            </td>

                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 font-mono text-xs text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                {{ p.sku }}
                            </td>

                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                {{ p.category?.name ?? '—' }}
                            </td>

                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                {{ p.variations.length }}
                            </td>

                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                KES {{ p.price }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <span class="font-semibold" :class="totalStock(p) === 0
                                    ? 'text-error'
                                    : 'text-slate-700 dark:text-navy-100'">
                                    {{ totalStock(p) }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <span :class="p.is_active
                                    ? 'bg-success/10 text-success'
                                    : 'bg-slate-100 text-slate-500 dark:bg-navy-600 dark:text-navy-300'"
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold">
                                    {{ p.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 text-right dark:border-slate-700">
                                <Link :href="admin_products_edit.url(p.id)"
                                    class="inline-flex h-9 items-center justify-center rounded-lg border border-primary/20 bg-primary/[0.06] px-3 text-xs font-semibold text-primary transition-colors hover:bg-primary/15 focus:bg-primary/15 active:bg-primary/20 dark:border-accent-light/20 dark:bg-accent-light/[0.06] dark:text-accent-light dark:hover:bg-accent-light/15">
                                    Edit
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="products.data.length === 0">
                            <td colspan="10"
                                class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300">
                                No products found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Replaced the broken <nav> with the correct prop -->
            <Pagination :paginator="products" />
            
        </div>
    </div>
</template>