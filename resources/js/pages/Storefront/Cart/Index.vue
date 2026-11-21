
<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import EmptySVG from '@/components/SVG/emptySVG.vue';
import CustomConfirmDialog from '@/components/CustomConfirmDialog.vue';
import { customer_checkout_index, customer_products_index } from '@/routes';

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

    router.patch(
        `/cart/items/${line.id}`,
        { quantity },
        { preserveScroll: true },
    );
}

/* -------- Remove confirmation -------- */
const showRemoveDialog = ref(false);
const selectedLine = ref<CartLine | null>(null);
const removingLine = ref(false);

function removeLine(line: CartLine) {
    selectedLine.value = line;
    showRemoveDialog.value = true;
}

function confirmRemoveLine() {
    if (!selectedLine.value || removingLine.value) return;

    removingLine.value = true;

    router.delete(`/cart/items/${selectedLine.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            showRemoveDialog.value = false;
            selectedLine.value = null;
            removingLine.value = false;
        },
    });
}

function formatMoney(amount: string | number) {
    return new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(amount) || 0);
}
</script>

<template>
    <Head title="Your Cart" />

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-center sm:justify-between dark:border-navy-600"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-navy-50">
                    Your Shopping Cart
                </h1>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-navy-300">
                    Review items, apply discount codes, and proceed to checkout.
                </p>
            </div>

            <Link
                v-if="!isEmpty"
                href="/customer/products"
                class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="size-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    />
                </svg>
                Continue shopping
            </Link>
        </div>

        <!-- Empty State -->
        <div
            v-if="isEmpty"
            class="mt-8 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-20 text-center shadow-sm dark:border-navy-600 dark:bg-navy-750"
        >
            <div class="rounded-full bg-slate-50 p-6 dark:bg-navy-800">
                <EmptySVG class="size-32" />
            </div>

            <p class="mt-6 text-lg font-semibold text-slate-800 dark:text-navy-100">
                Your cart is empty
            </p>

            <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-navy-300">
                Explore our catalog and add items to your cart to get started.
            </p>

            <Link
                :href="customer_products_index.url()"
                class="btn mt-3 bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
            >
                <span>Browse products</span>

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="size-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M14 5l7 7m0 0l-7 7m7-7H3"
                    />
                </svg>
            </Link>
        </div>

        <!-- Cart Layout -->
        <div v-else class="mt-8 grid gap-6 lg:grid-cols-12">
            <!-- Cart Items -->
            <section class="min-w-0 lg:col-span-8">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-navy-600 dark:bg-navy-750"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-navy-600"
                    >
                        <h2 class="text-base font-semibold text-slate-900 dark:text-navy-50">
                            Cart Items
                        </h2>

                        <span
                            class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-navy-800 dark:text-navy-200"
                        >
                            {{ props.cart!.item_count }}
                            {{ props.cart!.item_count === 1 ? 'item' : 'items' }}
                        </span>
                    </div>

                    <!-- Horizontally scrollable table on narrow viewports -->
                    <div class="is-scrollbar-x overflow-x-auto">
                        <div class="w-full overflow-x-auto rounded-xl border border-slate-100 dark:border-navy-600">
                            <table class="w-full min-w-[650px] table-fixed border-collapse text-left text-sm dark:border-navy-600">
                                <thead>
                                    <tr class="bg-primary/5 text-2xs uppercase tracking-wider text-primary/60 dark:border-navy-600">
                                        <th class="w-[40%] border-b border-r border-slate-100 px-4 py-3.5 font-semibold dark:border-navy-600 sm:px-5">
                                            Product
                                        </th>

                                        <th class="w-[20%] border-b border-r border-slate-100 px-4 py-3.5 font-semibold dark:border-navy-600 sm:px-5">
                                            Qty
                                        </th>

                                        <th class="w-[20%] border-b border-r border-slate-100 px-4 py-3.5 text-right font-semibold dark:border-navy-600 sm:px-5">
                                            Price
                                        </th>

                                        <th class="w-[20%] border-b border-slate-100 px-4 py-3.5 text-right font-semibold dark:border-navy-600 sm:px-5">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="line in props.cart!.items"
                                        :key="line.id"
                                        class="transition-colors hover:bg-primary/5 dark:border-navy-600"
                                    >
                                        <!-- Product -->
                                        <td class="border-r border-slate-100 px-4 py-4 dark:border-navy-600 sm:px-5">
                                            <div class="min-w-0 space-y-1.5">
                                                <Link
                                                    :href="`/customer/products/${line.product.slug}`"
                                                    class="block truncate text-sm font-semibold text-primary transition-colors hover:text-primary/70"
                                                >
                                                    {{ line.product.name }}
                                                </Link>

                                                <p class="truncate font-mono text-xs text-primary/50">
                                                    SKU: {{ line.sku }}
                                                </p>

                                                <div
                                                    v-if="line.attributes.length"
                                                    class="flex flex-wrap gap-1.5 pt-1"
                                                >
                                                    <span
                                                        v-for="(attr, i) in line.attributes"
                                                        :key="i"
                                                        class="badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light"
                                                    >
                                                        {{ attr.name }}: {{ attr.value }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Quantity -->
                                        <td class="border-r border-slate-100 px-4 py-4 dark:border-navy-600 sm:px-5">
                                            <input
                                                :value="line.quantity"
                                                type="number"
                                                min="1"
                                                :max="Math.min(99, line.stock_quantity)"
                                                class="form-input w-16 max-w-full rounded-lg border border-slate-100 bg-transparent px-2 py-1.5 text-center text-sm text-primary transition-colors hover:border-primary/30 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-navy-600"
                                                @change="(e) => updateQty(line, (e.target as HTMLInputElement).value)"
                                            />
                                        </td>

                                        <!-- Price -->
                                        <td class="border-r border-slate-100 px-4 py-4 text-right dark:border-navy-600 sm:px-5">
                                            <div class="whitespace-nowrap">
                                                <p class="text-base font-bold text-primary">
                                                    KES {{ formatMoney(line.line_total) }}
                                                </p>

                                                <p class="text-xs text-primary/50">
                                                    @ KES {{ formatMoney(line.unit_price) }}
                                                </p>
                                            </div>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-4 py-4 text-right dark:border-navy-600 sm:px-5">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-lg border border-secondary/30 bg-secondary/10 px-3 py-2 text-xs font-semibold text-secondary transition-colors hover:bg-secondary/20 focus:outline-none focus:ring-2 focus:ring-secondary/20 active:bg-secondary/25 dark:border-secondary-light/30 dark:bg-secondary-light/10 dark:text-secondary-light dark:hover:bg-secondary-light/20"
                                                @click="removeLine(line)"
                                            >
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Summary -->
            <aside class="min-w-0 lg:col-span-4">
                <div class="sticky top-6 space-y-4">
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-navy-600 dark:bg-navy-750"
                    >
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-navy-600">
                            <h2 class="text-base font-semibold text-slate-900 dark:text-navy-50">
                                Order Summary
                            </h2>
                        </div>

                        <dl class="space-y-3 p-5 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500 dark:text-navy-300">
                                    Total Items
                                </dt>

                                <dd class="font-medium text-slate-900 dark:text-navy-50">
                                    {{ props.cart!.item_count }}
                                </dd>
                            </div>

                            <div class="flex items-center justify-between">
                                <dt class="text-slate-500 dark:text-navy-300">
                                    Subtotal
                                </dt>

                                <dd class="font-medium text-slate-900 dark:text-navy-50">
                                    KES {{ formatMoney(props.cart!.subtotal) }}
                                </dd>
                            </div>

                            <div
                                v-if="discountAmount > 0"
                                class="flex items-center justify-between text-success"
                            >
                                <dt>
                                    Discount

                                    <span class="ml-1 font-mono text-xs text-slate-400 dark:text-navy-300">
                                        ({{ props.cart!.coupon_code }})
                                    </span>
                                </dt>

                                <dd class="font-medium">
                                    − KES {{ formatMoney(props.cart!.discount) }}
                                </dd>
                            </div>

                            <div
                                class="!mt-4 flex items-center justify-between border-t border-slate-200 pt-4 text-base dark:border-navy-600"
                            >
                                <dt class="font-semibold text-slate-900 dark:text-navy-50">
                                    Total
                                </dt>

                                <dd class="font-bold text-slate-900 dark:text-navy-50">
                                    KES {{ formatMoney(total) }}
                                </dd>
                            </div>

                            <p class="text-xs text-slate-400 dark:text-navy-300">
                                * Shipping charges will be calculated at checkout.
                            </p>
                        </dl>

                        <!-- Coupon -->
                        <div class="border-t border-slate-200 p-5 dark:border-navy-600">
                            <template v-if="props.cart!.coupon_code">
                                <div class="flex items-center justify-between rounded-lg bg-success/10 px-3.5 py-2.5">
                                    <span class="font-mono text-sm font-bold uppercase text-success">
                                        {{ props.cart!.coupon_code }}
                                    </span>

                                    <button
                                        type="button"
                                        class="text-xs font-semibold text-error hover:underline"
                                        @click="removeCoupon"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </template>

                            <template v-else>
                                <form class="flex gap-2" @submit.prevent="applyCoupon">
                                    <input
                                        v-model="couponForm.code"
                                        placeholder="Coupon code"
                                        class="form-input w-full min-w-0 rounded-lg border border-slate-300 bg-transparent px-3 py-2 uppercase placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                        autocomplete="off"
                                    />

                                    <button
                                        type="submit"
                                        :disabled="couponForm.processing || !couponForm.code"
                                        class="btn shrink-0 bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                                    >
                                        {{ couponForm.processing ? '…' : 'Apply' }}
                                    </button>
                                </form>

                                <p
                                    v-if="couponForm.errors.code"
                                    class="mt-1.5 text-xs text-error"
                                >
                                    {{ couponForm.errors.code }}
                                </p>
                            </template>
                        </div>

                        <div class="px-5 pb-5">
                            <Link
                                :href="customer_checkout_index.url()"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary py-3 text-center text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus"
                            >
                                <span>Proceed to Checkout</span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="size-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3"
                                    />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <!-- Remove Item Confirmation -->
    <CustomConfirmDialog
        v-model="showRemoveDialog"
        title="Remove item"
        :description="selectedLine
            ? `Are you sure you want to remove '${selectedLine.product.name}' from your cart?`
            : 'Are you sure you want to remove this item?'"
        confirm-text="Remove"
        cancel-text="Cancel"
        variant="danger"
        :loading="removingLine"
        @confirm="confirmRemoveLine"
    />
</template>
