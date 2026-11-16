<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import type { PageProps } from '@/types';
import Pagination from '@/components/Pagination.vue';

interface ProductRow {
    id: string;
    name: string;
    slug: string;
    price: string;
    sale_price: string | null;
    stock_quantity: number;
    type: 'simple' | 'variable';
    primary_image: { path: string } | null;
}

interface Filters {
    search?: string | null;
    sort?: string | null;
    categories?: string[];
    brand?: string | null;
    min_price?: string | number | null;
    max_price?: string | number | null;
    in_stock?: boolean | null;
}

const props = defineProps<PageProps & {
    products: { data: ProductRow[]; links: any[]; total?: number; from?: number; to?: number };
    categories: { id: string; name: string; slug: string; children: any[] }[];
    brands: { id: string; name: string; slug: string }[];
    filters: Filters;
}>();

const search = ref(props.filters.search ?? '');
const sort = ref(props.filters.sort ?? 'newest');
const minPrice = ref<string>(props.filters.min_price != null ? String(props.filters.min_price) : '');
const maxPrice = ref<string>(props.filters.max_price != null ? String(props.filters.max_price) : '');
const inStock = ref<boolean>(!!props.filters.in_stock);
const filtersOpen = ref(false);


/* ---------- Shared payload builder ---------- */

function buildPayload(overrides: Partial<Filters> = {}): Record<string, unknown> {
    const merged: Filters = { ...props.filters, ...overrides };
    const payload: Record<string, unknown> = {};

    if (merged.search) payload.search = merged.search;
    if (merged.sort && merged.sort !== 'newest') payload.sort = merged.sort;
    if (merged.categories && merged.categories.length) payload.categories = merged.categories;
    if (merged.brand) payload.brand = merged.brand;
    if (merged.min_price !== null && merged.min_price !== undefined && merged.min_price !== '')
        payload.min_price = merged.min_price;
    if (merged.max_price !== null && merged.max_price !== undefined && merged.max_price !== '')
        payload.max_price = merged.max_price;
    if (merged.in_stock) payload.in_stock = true;

    return payload;
}

function visit(overrides: Partial<Filters> = {}) {
    router.get('/customer/products', buildPayload(overrides), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

/* ---------- Categories (multi-select) ---------- */

const activeCategories = computed<string[]>(() => {
    const raw = props.filters.categories;
    if (!raw) return [];
    return Array.isArray(raw) ? raw : [raw];
});

function isCategoryActive(slug: string): boolean {
    return activeCategories.value.includes(slug);
}

function toggleCategory(slug: string) {
    const next = new Set(activeCategories.value);
    if (next.has(slug)) next.delete(slug);
    else next.add(slug);
    visit({ categories: Array.from(next) });
}

/* ---------- Brand (single) ---------- */

function toggleBrand(slug: string) {
    visit({ brand: props.filters.brand === slug ? null : slug });
}

/* ---------- Price range ---------- */

function applyPrice() {
    visit({
        min_price: minPrice.value === '' ? null : minPrice.value,
        max_price: maxPrice.value === '' ? null : maxPrice.value,
    });
}

/* ---------- Stock ---------- */

function toggleInStock() {
    inStock.value = !inStock.value;
    visit({ in_stock: inStock.value });
}

/* ---------- Search + Sort ---------- */

watch([search, sort], () => {
    visit({
        search: search.value || null,
        sort: sort.value || 'newest',
    });
});

/* ---------- Clear ---------- */

function clearFilters() {
    search.value = '';
    sort.value = 'newest';
    minPrice.value = '';
    maxPrice.value = '';
    inStock.value = false;

    router.get('/customer/products', {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

/* ---------- Counts ---------- */

const activeFiltersCount = computed(() => {
    let c = 0;
    if (props.filters.search) c++;
    if (props.filters.sort && props.filters.sort !== 'newest') c++;
    if (props.filters.brand) c++;
    if (props.filters.min_price !== null && props.filters.min_price !== undefined && props.filters.min_price !== '') c++;
    if (props.filters.max_price !== null && props.filters.max_price !== undefined && props.filters.max_price !== '') c++;
    if (props.filters.in_stock) c++;
    c += activeCategories.value.length;
    return c;
});

const activeBrandName = computed(() => {
    const slug = props.filters.brand;
    if (!slug) return null;
    return props.brands.find(b => b.slug === slug)?.name ?? slug;
});

const formatMoney = (amount) =>
    new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount || 0);
</script>

<template>
    <Head title="Shop Products" />

    <div class="w-full">
        <!-- Mobile Filter Toggle -->
        <button
            type="button"
            class="btn mb-4 h-9 w-full rounded-lg bg-slate-150 text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90 lg:hidden"
            @click="filtersOpen = !filtersOpen"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span>{{ filtersOpen ? 'Hide Filters' : 'Filter & Refine' }}</span>
        </button>

        <!-- LAYOUT WRAPPER (12-col grid like sample) -->
        <div class="grid grid-cols-12 gap-4 sm:gap-5 lg:gap-6">
            <!-- RIGHT MAIN CONTENT -->
            <div class="col-span-12 sm:order-last sm:col-span-7 sm:ml-4 lg:col-span-8 xl:col-span-9">
                <!-- Toolbar -->
                <div class="flex items-center justify-between space-x-3 sm:space-x-5">
                    <div class="flex w-full max-w-lg">
                        <label class="relative flex w-full">
                            <input
                                v-model="search"
                                class="form-input peer h-9 w-full rounded-lg bg-white px-3 py-2 shadow-soft ring-primary/50 placeholder:text-slate-400 focus:ring-3 dark:bg-navy-700 dark:shadow-none dark:ring-accent/50 dark:placeholder:text-navy-300 lg:pl-9"
                                placeholder="Search for products..."
                                type="search"
                            />
                            <span class="pointer-events-none absolute hidden h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent lg:flex">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                        </label>
                    </div>

                    <!-- View + Sort -->
                    <div class="flex">
                        <div class="hidden items-center gap-1 sm:flex">
                            <button class="btn size-8 shrink-0 rounded-full p-0 text-slate-700 hover:bg-slate-300/20 hover:text-primary focus:bg-slate-300/20 focus:text-primary active:bg-slate-300/25 dark:text-navy-100 dark:hover:bg-navy-300/20 dark:hover:text-accent dark:focus:bg-navy-300/20 dark:focus:text-accent dark:active:bg-navy-300/25">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                            <button class="btn size-8 shrink-0 rounded-full p-0 text-slate-700 hover:bg-slate-300/20 hover:text-primary focus:bg-slate-300/20 focus:text-primary active:bg-slate-300/25 dark:text-navy-100 dark:hover:bg-navy-300/20 dark:hover:text-accent dark:focus:bg-navy-300/20 dark:focus:text-accent dark:active:bg-navy-300/25">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </button>
                            <button class="btn size-8 shrink-0 rounded-full bg-primary/10 p-0 text-primary dark:bg-accent/20 dark:text-accent-light">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Result count + sort + show -->
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-base font-medium text-slate-700 dark:text-navy-100">
                        Products found: {{ products.total || 0 }}
                    </p>
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500 dark:text-navy-300">Sort :</span>
                            <select v-model="sort" class="form-select h-8 cursor-pointer rounded-lg border-none bg-slate-150 px-3 py-0 pl-3 pr-7 text-slate-800 focus:ring-0 dark:bg-navy-700 dark:text-navy-50">
                                <option value="newest">Latest</option>
                                <option value="price_asc">Price: Low → High</option>
                                <option value="price_desc">Price: High → Low</option>
                                <option value="name_asc">Name (A-Z)</option>
                            </select>
                        </div>
                        <div class="hidden items-center gap-2 sm:flex">
                            <span class="text-slate-500 dark:text-navy-300">Show :</span>
                            <select class="form-select h-8 cursor-pointer rounded-lg border-none bg-slate-150 px-3 py-0 pl-3 pr-7 text-slate-800 focus:ring-0 dark:bg-navy-700 dark:text-navy-50">
                                <option>16 Items</option>
                                <option>32 Items</option>
                                <option>64 Items</option>
                            </select>
                        </div>
                    </div>
                </div>

               

                <!-- Active filter chips -->
                <div v-if="activeFiltersCount > 0" class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active:</span>

                    <span
                        v-for="slug in activeCategories"
                        :key="'cat-' + slug"
                        class="inline-flex items-center gap-1 rounded-full bg-slate-150 px-3 py-1 text-xs text-slate-700 dark:bg-navy-700 dark:text-navy-100"
                    >
                        <button @click="toggleCategory(slug)" class="text-slate-500 hover:text-slate-800 dark:text-navy-300 dark:hover:text-navy-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        {{ categories.find(c => c.slug === slug)?.name ?? slug }}
                    </span>

                    <span v-if="activeBrandName" class="inline-flex items-center gap-1 rounded-full bg-slate-150 px-3 py-1 text-xs text-slate-700 dark:bg-navy-700 dark:text-navy-100">
                        <button @click="toggleBrand(props.filters.brand!)" class="text-slate-500 hover:text-slate-800 dark:text-navy-300 dark:hover:text-navy-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        Brand: {{ activeBrandName }}
                    </span>

                    <span
                        v-if="props.filters.min_price || props.filters.max_price"
                        class="inline-flex items-center gap-1 rounded-full bg-slate-150 px-3 py-1 text-xs text-slate-700 dark:bg-navy-700 dark:text-navy-100"
                    >
                        <button @click="minPrice = ''; maxPrice = ''; applyPrice()" class="text-slate-500 hover:text-slate-800 dark:text-navy-300 dark:hover:text-navy-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        Price: {{ formatMoney(props.filters.min_price || '0') }} – {{ formatMoney(props.filters.max_price || '∞' )}}
                    </span>

                    <span v-if="props.filters.in_stock" class="inline-flex items-center gap-1 rounded-full bg-slate-150 px-3 py-1 text-xs text-slate-700 dark:bg-navy-700 dark:text-navy-100">
                        <button @click="toggleInStock" class="text-slate-500 hover:text-slate-800 dark:text-navy-300 dark:hover:text-navy-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        In stock
                    </span>

                    <span v-if="search" class="inline-flex items-center gap-1 rounded-full bg-slate-150 px-3 py-1 text-xs text-slate-700 dark:bg-navy-700 dark:text-navy-100">
                        <button @click="search = ''" class="text-slate-500 hover:text-slate-800 dark:text-navy-300 dark:hover:text-navy-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        Search: {{ search }}
                    </span>

                    <button
                        type="button"
                        class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15"
                        @click="clearFilters"
                    >
                        Clear All
                    </button>
                </div>

                <!-- PRODUCT GRID -->
                <div v-if="products.data.length" class="mt-4 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-2 lg:gap-5 xl:grid-cols-3">
                    <div v-for="p in products.data" :key="p.id" class="card p-3 sm:p-4 group flex flex-col justify-between transition-shadow hover:shadow-lg">
                        <div>
                            <Link :href="`/customer/products/${p.slug}`" class="relative block aspect-square overflow-hidden rounded-lg bg-slate-150 dark:bg-navy-800">
                                <img
                                    v-if="p.primary_image"
                                    :src="`/storage/${p.primary_image.path}`"
                                    :alt="p.name"
                                    class="h-full w-full object-cover object-center mix-blend-multiply transition-transform duration-500 group-hover:scale-105 dark:mix-blend-normal"
                                />
                                <div class="absolute left-2 top-2 flex flex-col gap-1">
                                    <span v-if="p.sale_price" class="badge bg-error/10 text-error dark:bg-error/15">Sale</span>
                                    <span v-if="p.stock_quantity === 0 && p.type === 'simple'" class="badge bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-100">Out</span>
                                    <span v-else class="badge bg-success/10 text-success dark:bg-success/15">New</span>
                                </div>
                            </Link>

                            <div class="mt-3">
                                <span class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15">
                                   {{ p?.category?.name }}
                                </span>
                                <Link :href="`/customer/products/${p.slug}`" class="mt-1 block line-clamp-2 text-xs font-medium leading-tight text-slate-700 hover:text-primary focus:text-primary dark:text-navy-100 dark:hover:text-accent-light sm:text-sm">
                                    {{ p.name }}
                                </Link>
                                <div class="mt-2 flex items-center gap-1.5">
                                    <div class="flex text-amber-400">
                                        <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3 sm:size-3.5">
                                            <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-2">
                            <div class="flex flex-col sm:flex-row sm:items-baseline sm:gap-2">
                                <span v-if="p.sale_price" class="text-sm font-bold text-slate-800 dark:text-navy-50 sm:text-base">
                                    KES {{ formatMoney(p.sale_price) }}
                                </span>
                                <span :class="p.sale_price ? 'text-[10px] text-slate-400 line-through dark:text-navy-400 sm:text-xs' : 'text-sm font-bold text-slate-800 dark:text-navy-50 sm:text-base'">
                                    KES {{ formatMoney(p.price) }}
                                </span>
                            </div>
                            <Link
                                :href="`/customer/products/${p.slug}`"
                                class="btn size-8 shrink-0 rounded-full bg-primary/10 p-0 text-primary hover:bg-primary hover:text-white focus:bg-primary focus:text-white dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent dark:hover:text-white"
                                aria-label="View product"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="card mt-6 px-6 py-16 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 size-16 text-slate-200 dark:text-navy-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="text-lg font-medium text-slate-800 dark:text-navy-100">No products found</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-navy-300">Try adjusting your filters or search terms.</p>
                </div>

                <!-- Pagination -->
                <nav v-if="products.links.length > 3" class="mt-8 flex items-center justify-between">
                    <Pagination :links="products.links" class="mt-8 flex-wrap justify-center space-x-1.5" />
                </nav>
            </div>

            <!-- LEFT SIDEBAR (filters) -->
            <aside
                class="col-span-12 sm:col-span-5 lg:col-span-4 xl:col-span-3"
                :class="filtersOpen ? 'block' : 'hidden sm:block'"
            >
                <!-- Categories -->
                <div class="card p-4 sm:p-5">
                    <h3 class="text-sm-plus font-medium text-slate-700 dark:text-navy-100">
                        Categories
                    </h3>
                    <ul class="mt-3 space-y-2.5">
                        <li v-for="c in categories" :key="c.id">
                            <label class="group flex cursor-pointer items-center justify-between text-sm-plus text-slate-600 hover:text-primary dark:text-navy-200 dark:hover:text-accent-light">
                                <span class="inline-flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        :checked="isCategoryActive(c.slug)"
                                        class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        @change="toggleCategory(c.slug)"
                                    />
                                    <span>{{ c.name }}</span>
                                </span>
                            </label>
                        </li>
                    </ul>
                </div>

                <!-- Brands -->
                <div class="card mt-4 p-4 sm:p-5">
                    <h3 class="text-sm-plus font-medium text-slate-700 dark:text-navy-100">
                        Brands
                    </h3>
                    <ul class="mt-3 space-y-2.5">
                        <li v-for="b in brands" :key="b.id">
                            <label class="group flex cursor-pointer items-center justify-between text-sm-plus text-slate-600 hover:text-primary dark:text-navy-200 dark:hover:text-accent-light">
                                <span class="inline-flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        name="brand"
                                        :checked="props.filters.brand === b.slug"
                                        class="form-radio is-basic size-5 rounded-full border-slate-400/70 bg-white checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:bg-navy-700 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                        @change="toggleBrand(b.slug)"
                                        @click="props.filters.brand === b.slug && toggleBrand(b.slug)"
                                    />
                                    <span>{{ b.name }}</span>
                                </span>
                            </label>
                        </li>
                    </ul>
                </div>

                <!-- Price & Stock -->
                <div class="card mt-4 p-4 sm:p-5">
                    <h3 class="text-sm-plus font-medium text-slate-700 dark:text-navy-100">
                        Filter by price
                    </h3>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <input
                                v-model="minPrice"
                                type="number"
                                min="0"
                                placeholder="Min"
                                class="form-input h-9 w-full rounded-lg border border-slate-300 bg-transparent px-2.5 py-1 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                @keydown.enter="applyPrice"
                                @blur="applyPrice"
                            />
                            <span class="text-slate-400">—</span>
                            <input
                                v-model="maxPrice"
                                type="number"
                                min="0"
                                placeholder="Max"
                                class="form-input h-9 w-full rounded-lg border border-slate-300 bg-transparent px-2.5 py-1 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                @keydown.enter="applyPrice"
                                @blur="applyPrice"
                            />
                        </div>
                        <button
                            type="button"
                            class="btn h-9 w-full rounded-lg bg-primary/10 text-xs-plus font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                            @click="applyPrice"
                        >
                            Apply price
                        </button>

                        <label class="mt-1 flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm-plus text-slate-700 hover:bg-slate-50 dark:border-navy-600 dark:text-navy-100 dark:hover:bg-navy-900">
                            <span>In stock only</span>
                            <input
                                type="checkbox"
                                :checked="inStock"
                                class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                @change="toggleInStock"
                            />
                        </label>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>