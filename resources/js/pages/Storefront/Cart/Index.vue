<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import EmptySVG from '@/components/SVG/emptySVG.vue';
import { customer_products_index } from '@/routes';

interface CartLine {
    id: string;
    quantity: number;
    variation_id: string;
    sku: string;
    unit_price: string;
    line_total: string;
    stock_quantity: number;
    product: { id: string; name: string; slug: string };
    attributes: { name: string; value: string }[];
}

interface CartPayload {
    id: string;
    items: CartLine[];
    item_count: number;
    subtotal: string;
    discount: string;
    coupon_code: string | null;
    coupon_error: string | null;
    currency: string;
}

const props = defineProps<PageProps & { cart: CartPayload | null }>();

const isEmpty = computed(() => !props.cart || props.cart.items.length === 0);

const discountAmount = computed(() =>
    Number(props.cart?.discount ?? 0),
);

const total = computed(() => {
    if (!props.cart) return '0.00';
    return (Number(props.cart.subtotal) - discountAmount.value).toFixed(2);
});

/* -------- Coupon -------- */
const couponForm = useForm({ code: '' });

function applyCoupon() {
    if (!couponForm.code.trim()) return;
    couponForm.post('/cart/coupon', {
        preserveScroll: true,
        onSuccess: () => couponForm.reset('code'),
    });
}

function removeCoupon() {
    router.delete('/cart/coupon', { preserveScroll: true });
}

/* -------- Line actions -------- */
function updateQty(line: CartLine, value: string) {
    const quantity = Number(value);
    if (!Number.isFinite(quantity) || quantity < 1) return;
    if (quantity === line.quantity) return;
    router.patch(`/cart/items/${line.id}`, { quantity }, { preserveScroll: true });
}

function removeLine(line: CartLine) {
    // @ts-expect-error alertify is loaded globally via the layout
    alertify.confirm(
        'Remove item',
        `Remove "${line.product.name}" from your cart?`,
        () => {
            router.delete(`/cart/items/${line.id}`, { preserveScroll: true });
        },
        () => {
            // user cancelled — no-op
        },
    ).set('labels', { ok: 'Remove', cancel: 'Cancel' });
}

const formatMoney = (amount) =>
    new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount || 0);
</script>

<template>

    <Head title="Your Cart" />

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-center sm:justify-between dark:border-navy-600">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-navy-50">
                    Your Shopping Cart
                </h1>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-navy-300">
                    Review items, apply discount codes, and proceed to checkout.
                </p>
            </div>
            <Link v-if="!isEmpty" href="/customer/products"
                class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Continue shopping
            </Link>
        </div>

        <!-- Empty State -->
        <div v-if="isEmpty"
            class="mt-8 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-20 text-center shadow-sm dark:border-navy-600 dark:bg-navy-750">
            <div class="rounded-full bg-slate-50 p-6 dark:bg-navy-800">
                <EmptySVG class="size-32" />
            </div>
            <p class="mt-6 text-lg font-semibold text-slate-800 dark:text-navy-100">Your cart is empty</p>
            <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-navy-300">
                Explore our catalog and add items to your cart to get started.
            </p>
            <Link :href="customer_products_index.url()"
                class="mt-8 inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-focus focus:outline-none focus:ring-2 focus:ring-primary/40 dark:bg-accent dark:hover:bg-accent-focus">
                <span>Browse products</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </Link>
        </div>

        <!-- Cart Layout -->
        <div v-else class="mt-8 grid gap-6 lg:grid-cols-12">
            <!-- Cart Items -->
            <section class="lg:col-span-8">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-navy-600 dark:bg-navy-750">
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-navy-600">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-navy-50">
                            Cart Items
                        </h2>
                        <span
                            class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-navy-800 dark:text-navy-200">
                            {{ props.cart!.item_count }} {{ props.cart!.item_count === 1 ? 'item' : 'items' }}
                        </span>
                    </div>

                    <ul class="divide-y divide-slate-100 dark:divide-navy-600">
                        <li v-for="line in props.cart!.items" :key="line.id"
                            class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                            <!-- Product Info -->
                            <div class="min-w-0 flex-1 space-y-1.5">
                                <Link :href="`/customer/products/${line.product.slug}`"
                                    class="block truncate text-sm font-semibold text-slate-900 transition-colors hover:text-primary dark:text-navy-50 dark:hover:text-accent-light">
                                    {{ line.product.name }}
                                </Link>
                                <p class="font-mono text-xs text-slate-400 dark:text-navy-300">
                                    SKU: {{ line.sku }}
                                </p>
                                <div v-if="line.attributes.length" class="flex flex-wrap gap-1.5 pt-1">
                                    <span v-for="(attr, i) in line.attributes" :key="i"
                                        class="btn py-1 bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 hover:shadow-lg hover:shadow-slate-200/50 focus:bg-slate-200 focus:shadow-lg focus:shadow-slate-200/50 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:hover:shadow-navy-450/50 dark:focus:bg-navy-450 dark:focus:shadow-navy-450/50 dark:active:bg-navy-450/90">
                                        {{ attr.name }}: {{ attr.value }}
                                    </span>
                                </div>
                            </div>

                            <!-- Quantity -->
                            <div class="flex items-center gap-2 sm:shrink-0">
                                <label class="text-xs font-medium text-slate-500 dark:text-navy-300">Qty</label>
                                <input :value="line.quantity" type="number" min="1"
                                    :max="Math.min(99, line.stock_quantity)"
                                    class="form-input w-16 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-center text-sm text-slate-800 transition-colors hover:border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-navy-600 dark:bg-navy-900 dark:text-navy-100 dark:hover:border-navy-500 dark:focus:border-accent dark:focus:ring-accent/20"
                                    @change="(e) => updateQty(line, (e.target as HTMLInputElement).value)" />
                            </div>

                            <!-- Price & Remove -->
                            <div
                                class="flex items-center justify-between gap-4 sm:w-40 sm:flex-col sm:items-end sm:gap-1">
                                <div class="text-left sm:text-right">
                                    <p class="text-base font-bold text-slate-900 dark:text-navy-50">
                                        KES {{ formatMoney(line.line_total) }}
                                    </p>
                                    <p class="text-xs text-slate-400 dark:text-navy-300">
                                        @ KES {{ formatMoney(line.unit_price) }}
                                    </p>
                                </div>
                                <button type="button"
                                    class="btn border border-secondary/30 py-1 bg-secondary/10 font-medium text-secondary hover:bg-secondary/20 focus:bg-secondary/20 active:bg-secondary/25 dark:border-secondary-light/30 dark:bg-secondary-light/10 dark:text-secondary-light dark:hover:bg-secondary-light/20 dark:focus:bg-secondary-light/20 dark:active:bg-secondary-light/25"
                                    @click="removeLine(line)">
                                    Remove
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Summary -->
            <aside class="lg:col-span-4">
                <div class="sticky top-6 space-y-4">
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-navy-600 dark:bg-navy-750">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-navy-600">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-navy-50">
                                Order Summary
                            </h2>
                        </div>

                        <dl class="space-y-3 p-5 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500 dark:text-navy-300">Total Items</dt>
                                <dd class="font-medium text-slate-900 dark:text-navy-50">{{ props.cart!.item_count }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500 dark:text-navy-300">Subtotal</dt>
                                <dd class="font-medium text-slate-900 dark:text-navy-50">KES {{ formatMoney(props.cart!.subtotal) }}
                                </dd>
                            </div>
                            <div v-if="discountAmount > 0" class="flex items-center justify-between text-success">
                                <dt>
                                    Discount
                                    <span class="ml-1 font-mono text-xs text-slate-400 dark:text-navy-300">({{
                                        props.cart!.coupon_code }})</span>
                                </dt>
                                <dd class="font-medium">− KES {{ formatMoney(props.cart!.discount) }}</dd>
                            </div>
                            <div
                                class="!mt-4 flex items-center justify-between border-t border-slate-200 pt-4 text-base dark:border-navy-600">
                                <dt class="font-semibold text-slate-900 dark:text-navy-50">Total</dt>
                                <dd class="font-bold text-slate-900 dark:text-navy-50">KES {{ formatMoney(total) }}</dd>
                            </div>
                            <p class="text-xs text-slate-400 dark:text-navy-300">
                                * Shipping charges will be calculated at checkout.
                            </p>
                        </dl>

                        <!-- Coupon -->
                        <div class="border-t border-slate-200 p-5 dark:border-navy-600">
                            <template v-if="props.cart!.coupon_code">
                                <div class="flex items-center justify-between rounded-lg bg-success/10 px-3.5 py-2.5">
                                    <span class="font-mono text-sm font-bold uppercase text-success">{{
                                        props.cart!.coupon_code }}</span>
                                    <button type="button" class="text-xs font-semibold text-error hover:underline"
                                        @click="removeCoupon">
                                        Remove
                                    </button>
                                </div>
                            </template>
                            <template v-else>
                                <form class="flex gap-2" @submit.prevent="applyCoupon">
                                    <input v-model="couponForm.code" placeholder="Coupon code"
                                        class="form-input w-full rounded-lg border border-slate-300 uppercase bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        autocomplete="off" />
                                    <button type="submit" :disabled="couponForm.processing || !couponForm.code"
                                        class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                                        {{ couponForm.processing ? '…' : 'Apply' }}
                                    </button>
                                </form>
                                <p v-if="couponForm.errors.code" class="mt-1.5 text-xs text-error">
                                    {{ couponForm.errors.code }}
                                </p>
                            </template>
                        </div>

                        <div class="px-5 pb-5">
                            <Link href="/checkout"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary py-3 text-center text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus">
                                <span>Proceed to Checkout</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>