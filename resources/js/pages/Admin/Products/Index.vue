<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { PageProps } from '@/types';
import { admin_products_create, admin_products_edit } from '@/routes';

interface Category { id: string; name: string }
interface Variation { id: string; product_id: string; stock_quantity: number }
interface ProductRow {
    id: string;
    name: string;
    sku: string;
    price: string;
    is_active: boolean;
    category: Category | null;
    primary_image: { path: string } | null;
    variations: Variation[];
}

const props = defineProps<PageProps & {
    products: {
        data: ProductRow[];
        links: { url: string | null; label: string; active: boolean }[];
        current_page: number;
    };
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
        <!-- Page header -->
        <div class="flex items-center justify-between">
            <h2 class="text-base font-medium tracking-wide text-slate-800 dark:text-navy-100">
                Products
            </h2>
            <Link :href="admin_products_create.url()"
                class="btn h-9 space-x-2 bg-primary/10 px-4 text-xs-plus font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                New Product
            </Link>
        </div>

        <!-- Filters -->
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <label class="relative">
                <span class="sr-only">Search</span>
                <input v-model="search" type="text" placeholder="Search name or SKU…"
                    class="form-input h-9 w-64 rounded-lg border border-slate-300 bg-transparent px-3 text-xs-plus text-slate-800 placeholder-slate-400 focus:border-primary dark:border-navy-450 dark:text-navy-100 dark:placeholder-navy-300 dark:focus:border-accent" />
            </label>
        </div>

        <!-- Table card -->
        <div class="mt-4 overflow-hidden rounded-lg border border-slate-150 dark:border-navy-600">
            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                <table class="is-zebra w-full text-left">
                    <thead>
                        <tr>
                            <th
                                class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                Image
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                Name
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                SKU
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                Category
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                Variations
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                Base Price
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                Stock (total)
                            </th>
                            <th
                                class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                                Status
                            </th>
                            <th
                                class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in products.data" :key="p.id">
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                                <div class="avatar size-10">
                                    <img v-if="p.primary_image" :src="`/storage/${p.primary_image.path}`" :alt="p.name"
                                        class="rounded-lg object-cover" />
                                    <div v-else
                                        class="is-initial rounded-lg bg-slate-150 text-slate-500 dark:bg-navy-500 dark:text-navy-100">
                                        <span class="text-xs-plus">{{ p.name.charAt(0) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <p class="font-medium text-slate-700 dark:text-navy-100">
                                    {{ p.name }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                                <p class="text-slate-500 dark:text-navy-300">
                                    {{ p.sku }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                                {{ p.category?.name ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                                {{ p.variations.length }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                                KES {{ p.price }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <span class="font-medium"
                                    :class="totalStock(p) === 0 ? 'text-error' : 'text-slate-700 dark:text-navy-100'">
                                    {{ totalStock(p) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <span v-if="p.is_active"
                                    class="badge h-6 rounded-full bg-success/10 px-2.5 text-success dark:bg-success/15">
                                    Active
                                </span>
                                <span v-else
                                    class="badge h-6 rounded-full bg-slate-150 px-2.5 text-slate-500 dark:bg-navy-500 dark:text-navy-300">
                                    Inactive
                                </span>
                            </td>
                            <td class="whitespace-nowrap rounded-r-lg px-4 py-3 text-right sm:px-5">
                                <Link :href="admin_products_edit.url(p.id)"
                                    class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                                    Edit
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="products.data.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-slate-500 dark:text-navy-300">
                                No products found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav v-if="products.links.length > 3"
                class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-150 p-4 dark:border-navy-600">
                <template v-for="(link, i) in products.links" :key="i">
                    <Link v-if="link.url" :href="link.url"
                        class="btn h-8 min-w-[2rem] rounded-lg px-3 text-xs-plus font-medium" :class="link.active
                                ? 'bg-primary text-white dark:bg-accent'
                                : 'border border-slate-300 text-slate-600 hover:bg-slate-100 dark:border-navy-450 dark:text-navy-200 dark:hover:bg-navy-600'
                            " v-html="link.label" />
                    <span v-else
                        class="btn h-8 min-w-[2rem] cursor-not-allowed rounded-lg border border-slate-200 px-3 text-xs-plus font-medium text-slate-400 dark:border-navy-600 dark:text-navy-500"
                        v-html="link.label" />
                </template>
            </nav>
        </div>
    </div>
</template>