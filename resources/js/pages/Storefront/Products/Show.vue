<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { PageProps } from '@/types';

interface AttributeValue {
    id: string;
    value: string;
    attribute: { id: string; name: string };
}

interface Variation {
    id: string;
    sku: string;
    price: number | null;
    sale_price: number | null;
    current_price: number;
    current_price_formatted: string;
    original_price: number | null;
    original_price_formatted: string | null;
    discount_percent: number;
    stock_quantity: number;
    attribute_values: AttributeValue[];
}

interface ProductImage {
    id: string;
    path: string;
    is_primary: boolean;
}

interface ProductDetail {
    id: string;
    name: string;
    slug: string;
    sku: string;
    price: number;
    sale_price: number | null;
    current_price: number;
    current_price_formatted: string;
    original_price: number | null;
    original_price_formatted: string | null;
    discount_percent: number;
    has_sale: boolean;
    short_description: string | null;
    description: string | null;
    images: ProductImage[];
    category: { name: string; slug: string } | null;
    brand: { name: string } | null;
    variations: Variation[];
}

interface RelatedProduct {
    id: string;
    name: string;
    slug: string;
    price: number;
    price_formatted: string;
    primary_image: { path: string } | null;
}

interface ReviewRow {
    id: string;
    rating: number;
    title: string | null;
    body: string | null;
    user_name: string;
    verified: boolean;
    created_at: string;
}

interface Props extends PageProps {
    product: ProductDetail;
    related: RelatedProduct[];
    reviews: ReviewRow[];
    average_rating: number;
    review_count: number;
}

const props = defineProps<Props>();

/* ---------------- Images ---------------- */
const selectedImage = ref<string | null>(props.product.images[0]?.path ?? null);

/* ---------------- Variation selection ---------------- */
const selectedVariation = ref<Variation | null>(
    props.product.variations.length === 1
        ? props.product.variations[0]
        : (props.product.variations.find((v) => v.stock_quantity > 0) ?? null),
);

/* ---------------- Price (server-computed) ---------------- */
const effectivePrice = computed(() => {
    const v = selectedVariation.value;

    if (!v) {
        return {
            current: props.product.current_price_formatted,
            original: props.product.original_price_formatted,
            discountPercent: props.product.discount_percent,
        };
    }

    return {
        current: v.current_price_formatted,
        original: v.original_price_formatted,
        discountPercent: v.discount_percent,
    };
});

/* ---------------- Stock ---------------- */
const inStock = computed(() => (selectedVariation.value?.stock_quantity ?? 0) > 0);

/* ---------------- Attribute grid ---------------- */
const groupedAttributes = computed(() => {
    const groups: Record<string, { valueId: string; value: string }[]> = {};

    for (const variation of props.product.variations) {
        for (const av of variation.attribute_values) {
            const key = av.attribute.name;
            groups[key] ??= [];
            if (!groups[key].some((x) => x.valueId === av.id)) {
                groups[key].push({ valueId: av.id, value: av.value });
            }
        }
    }

    return groups;
});

function selectAttribute(attributeName: string, valueId: string) {
    const currentSelection = selectedVariation.value?.attribute_values ?? [];

    const matching = props.product.variations.filter((variation) => {
        const matchesTarget = variation.attribute_values.some(
            (av) => av.attribute.name === attributeName && av.id === valueId,
        );
        if (!matchesTarget) return false;

        return currentSelection
            .filter((av) => av.attribute.name !== attributeName)
            .every((av) =>
                variation.attribute_values.some(
                    (candidate) =>
                        candidate.attribute.name === av.attribute.name && candidate.id === av.id,
                ),
            );
    });

    const match =
        matching[0] ??
        props.product.variations.find((variation) =>
            variation.attribute_values.some(
                (av) => av.attribute.name === attributeName && av.id === valueId,
            ),
        );

    if (match) {
        selectedVariation.value = match;
    }
}

/* ---------------- Quantity ---------------- */
const qty = ref(1);

const maxQty = computed(() => {
    const stock = selectedVariation.value?.stock_quantity ?? 0;
    return Math.min(99, Math.max(1, stock));
});

const canAdd = computed(() => {
    if (!selectedVariation.value) return false;
    if (selectedVariation.value.stock_quantity <= 0) return false;
    if (qty.value < 1) return false;
    if (qty.value > selectedVariation.value.stock_quantity) return false;
    return true;
});

function addToCart() {
    if (!selectedVariation.value) return;

    router.post(
        '/cart/items',
        {
            variation_id: selectedVariation.value.id,
            quantity: qty.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                qty.value = 1;
            },
        },
    );
}

function isAttributeSelected(attributeName: string, valueId: string): boolean {
    return (
        selectedVariation.value?.attribute_values.some(
            (av) => av.attribute.name === attributeName && av.id === valueId,
        ) ?? false
    );
}
</script>

<template>
    <Head :title="product.name" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex items-center space-x-2 text-xs text-slate-400 dark:text-navy-300">
            <Link href="/customer/products" class="transition-colors hover:text-primary dark:hover:text-accent-light">Shop</Link>
            <template v-if="product.category">
                <span class="text-slate-300 dark:text-navy-500">/</span>
                <Link
                    :href="`/customer/products?category=${product.category.slug}`"
                    class="transition-colors hover:text-primary dark:hover:text-accent-light"
                >
                    {{ product.category.name }}
                </Link>
            </template>
            <span class="text-slate-300 dark:text-navy-500">/</span>
            <span class="truncate font-medium text-slate-700 dark:text-navy-100">{{ product.name }}</span>
        </nav>

        <!-- Main grid -->
        <div class="mt-6 grid gap-8 lg:grid-cols-2 lg:gap-12">
            <!-- Gallery -->
            <div class="lg:sticky lg:top-6 lg:self-start">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-navy-600 dark:bg-navy-750">
                    <div class="aspect-square overflow-hidden rounded-xl bg-slate-100 dark:bg-navy-900">
                        <img
                            v-if="selectedImage"
                            :src="`/storage/${selectedImage}`"
                            :alt="product.name"
                            class="h-full w-full object-cover object-center transition-transform duration-500"
                        />
                    </div>
                </div>

                <div v-if="product.images.length > 1" class="mt-3 grid grid-cols-5 gap-2">
                    <button
                        v-for="image in product.images"
                        :key="image.id"
                        type="button"
                        class="aspect-square overflow-hidden rounded-xl border-2 bg-slate-100 transition-all dark:bg-navy-900"
                        :class="
                            selectedImage === image.path
                                ? 'border-primary shadow-md dark:border-accent'
                                : 'border-transparent opacity-60 hover:opacity-100'
                        "
                        @click="selectedImage = image.path"
                    >
                        <img
                            :src="`/storage/${image.path}`"
                            :alt="product.name"
                            class="h-full w-full object-cover object-center"
                        />
                    </button>
                </div>
            </div>

            <!-- Info -->
            <div class="flex flex-col">
                <!-- Meta row -->
                <div class="flex items-center justify-between gap-3">
                    <span
                        v-if="product.brand"
                        class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15"
                    >
                        {{ product.brand.name }}
                    </span>
                    <span class="font-mono text-xs text-slate-400 dark:text-navy-300">SKU: {{ product.sku }}</span>
                </div>

                <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-navy-50">
                    {{ product.name }}
                </h1>

                <!-- Rating summary -->
                <div v-if="review_count > 0" class="mt-3 flex items-center gap-2 text-sm">
                    <span class="text-warning text-base">
                        {{ '★'.repeat(Math.round(average_rating)) }}{{ '☆'.repeat(5 - Math.round(average_rating)) }}
                    </span>
                    <span class="text-slate-500 dark:text-navy-300">
                        {{ average_rating }} · {{ review_count }} review{{ review_count === 1 ? '' : 's' }}
                    </span>
                </div>

                <!-- Price block -->
<div class="mt-5 flex flex-wrap items-baseline gap-3">
    <span class="text-3xl font-bold tracking-tight text-slate-900 dark:text-navy-50">
        KES {{ effectivePrice.current }}
    </span>
    <span
        v-if="effectivePrice.original"
        class="text-base font-normal text-slate-400 line-through dark:text-navy-300"
    >
        KES {{ effectivePrice.original }}
    </span>
    <span
        v-if="effectivePrice.original && effectivePrice.discountPercent > 0"
        class="rounded-full bg-error/10 px-2 py-0.5 text-xs font-semibold text-error dark:bg-error/15"
    >
        Save {{ effectivePrice.discountPercent }}%
    </span>
</div>

                <!-- Stock -->
                <div class="mt-3">
                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                        :class="
                            inStock
                                ? 'bg-success/10 text-success dark:bg-success/15'
                                : 'bg-error/10 text-error dark:bg-error/15'
                        "
                    >
                        <span class="mr-1.5 size-1.5 rounded-full" :class="inStock ? 'bg-success' : 'bg-error'"></span>
                        {{ inStock ? 'In stock' : 'Out of stock' }}
                    </span>
                </div>

                <p v-if="product.short_description" class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-navy-200">
                    {{ product.short_description }}
                </p>

                <!-- Attributes -->
                <div v-if="Object.keys(groupedAttributes).length" class="mt-6 space-y-4">
                    <div v-for="(values, attributeName) in groupedAttributes" :key="attributeName">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-navy-300">
                            {{ attributeName }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="value in values"
                                :key="value.valueId"
                                type="button"
                                class="rounded-lg border px-3.5 py-2 text-xs font-medium transition-all"
                                :class="
                                    isAttributeSelected(attributeName, value.valueId)
                                        ? 'border-primary/10 bg-primary/10 text-primary shadow-sm dark:border-accent dark:bg-accent'
                                        : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 dark:border-navy-600 dark:bg-navy-800 dark:text-navy-100 dark:hover:border-navy-500 dark:hover:bg-navy-700'
                                "
                                @click="selectAttribute(attributeName, value.valueId)"
                            >
                                {{ value.value }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Buy box -->
                <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 dark:border-navy-600 dark:bg-navy-900/40 sm:p-5">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center rounded-lg border border-slate-200 bg-white dark:border-navy-600 dark:bg-navy-800">
                            <button
                                type="button"
                                class="flex size-9 items-center justify-center rounded-l-lg text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-800 disabled:opacity-40 dark:text-navy-300 dark:hover:bg-navy-700 dark:hover:text-navy-50"
                                :disabled="qty <= 1 || !inStock"
                                @click="qty = Math.max(1, qty - 1)"
                            >
                                −
                            </button>
                            <input
                                v-model.number="qty"
                                type="number"
                                min="1"
                                :max="maxQty"
                                :disabled="!selectedVariation || !inStock"
                                class="w-14 border-0 bg-transparent py-2 text-center text-sm font-medium text-slate-800 focus:ring-0 dark:text-navy-50"
                            />
                            <button
                                type="button"
                                class="flex size-9 items-center justify-center rounded-r-lg text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-800 disabled:opacity-40 dark:text-navy-300 dark:hover:bg-navy-700 dark:hover:text-navy-50"
                                :disabled="qty >= maxQty || !inStock"
                                @click="qty = Math.min(maxQty, qty + 1)"
                            >
                                +
                            </button>
                        </div>
                        <span v-if="selectedVariation" class="text-xs text-slate-500 dark:text-navy-300">
                            {{ selectedVariation.stock_quantity }} available
                        </span>
                    </div>

                    <button
                        type="button"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg bg-primary py-3.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-focus focus:outline-none focus:ring-2 focus:ring-primary/40 active:bg-primary-focus/90 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-accent dark:hover:bg-accent-focus"
                        :disabled="!canAdd"
                        @click="addToCart"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        {{
                            !selectedVariation
                                ? 'Select options'
                                : !inStock
                                    ? 'Out of stock'
                                    : 'Add to Cart'
                        }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div v-if="product.description" class="mt-12 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-navy-600 dark:bg-navy-750 sm:p-8">
            <h2 class="border-b border-slate-200 pb-4 text-base font-semibold text-slate-900 dark:border-navy-600 dark:text-navy-50">
                Product Description
            </h2>
            <div class="prose prose-sm mt-5 max-w-none leading-relaxed text-slate-600 dark:prose-invert dark:text-navy-200" v-html="product.description" />
        </div>

        <!-- Reviews -->
        <section v-if="reviews.length" class="mt-12">
            <div class="flex flex-col gap-4 border-b border-slate-200 pb-4 dark:border-navy-600 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-navy-50">Customer Reviews</h2>
                    <p class="mt-0.5 text-sm text-slate-400 dark:text-navy-300">Verified feedback from buyers</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-warning text-lg">
                        {{ '★'.repeat(Math.round(average_rating)) }}{{ '☆'.repeat(5 - Math.round(average_rating)) }}
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                        {{ average_rating }} out of 5 ({{ review_count }} review{{ review_count === 1 ? '' : 's' }})
                    </span>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div
                    v-for="review in reviews"
                    :key="review.id"
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-navy-600 dark:bg-navy-750"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-navy-50">
                                {{ review.user_name }}
                            </p>
                            <span
                                v-if="review.verified"
                                class="mt-1 inline-block rounded-full bg-success/10 px-2 py-0.5 text-[10px] font-medium text-success dark:bg-success/15"
                            >
                                Verified Purchase
                            </span>
                        </div>
                        <p class="shrink-0 text-xs text-slate-400 dark:text-navy-300">{{ review.created_at }}</p>
                    </div>
                    <div class="mt-2 text-warning text-sm">
                        {{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}
                    </div>
                    <p v-if="review.title" class="mt-2 text-sm font-semibold text-slate-700 dark:text-navy-100">
                        {{ review.title }}
                    </p>
                    <p v-if="review.body" class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-navy-300">
                        {{ review.body }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Related -->
        <section v-if="related.length" class="mt-12">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-navy-50">Related Products</h2>
                <Link
                    href="/customer/products"
                    class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15"
                >
                    View all →
                </Link>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="item in related"
                    :key="item.id"
                    :href="`/customer/products/${item.slug}`"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg dark:border-navy-600 dark:bg-navy-750"
                >
                    <div class="aspect-square overflow-hidden bg-slate-100 dark:bg-navy-900">
                        <img
                            v-if="item.primary_image"
                            :src="`/storage/${item.primary_image.path}`"
                            :alt="item.name"
                            class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105"
                        />
                    </div>
                    <div class="flex flex-1 flex-col justify-between p-4">
                        <p class="line-clamp-2 text-sm font-medium text-slate-800 transition-colors group-hover:text-primary dark:text-navy-100 dark:group-hover:text-accent-light">
                            {{ item.name }}
                        </p>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-base font-bold text-slate-900 dark:text-navy-50">KES {{ item.price }}</span>
                            <span class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15">
                                View →
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </section>
    </div>
</template>