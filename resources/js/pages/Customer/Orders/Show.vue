<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

interface OrderItem {
    id: string;
    product_name: string;
    sku: string;
    attributes: { name: string; value: string }[];
    unit_price: string;
    quantity: number;
    line_total: string;
    variation_id: string | null;
    variation_product_id: string | null;
    can_review: boolean;
}

interface Payment {
    provider: string;
    amount: string;
    status: string;
    provider_reference: string | null;
    paid_at: string | null;
}

interface Order {
    ulid: string;
    order_number: string;
    created_at: string;
    subtotal: string;
    shipping_fee: string;
    discount_total: string;
    coupon_code: string | null;
    grand_total: string;
    payment_status: string;
    fulfilment_status: string;
    shipping_address: { county: string; town: string; address: string; instructions?: string | null };
    paid_at: string | null;
    items: OrderItem[];
    payments: Payment[];
}

const props = defineProps<PageProps & { order: Order }>();

// Review modal
const reviewItem = ref<OrderItem | null>(null);
const reviewForm = useForm({ rating: 5, title: '', body: '' });

function openReview(item: OrderItem) {
    reviewItem.value = item;
    reviewForm.reset();
    reviewForm.clearErrors();
}

function closeReview() {
    reviewItem.value = null;
}

function submitReview() {
    if (!reviewItem.value) return;
    reviewForm.post(
        `/customer/orders/${props.order.ulid}/items/${reviewItem.value.id}/review`,
        {
            preserveScroll: true,
            onSuccess: () => { reviewItem.value = null; },
        },
    );
}

function badgeClass(status: string): string {
    switch (status) {
        case 'paid':
        case 'delivered':
        case 'completed':
            return 'bg-success/10 text-success dark:bg-success/20';
        case 'pending':
            return 'bg-warning/10 text-warning dark:bg-warning/20';
        case 'processing':
            return 'bg-info/10 text-info dark:bg-info/20';
        case 'failed':
        case 'cancelled':
            return 'bg-error/10 text-error dark:bg-error/20';
        default:
            return 'bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200';
    }
}
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />

    <div class="mx-auto max-w-4xl px-4 py-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-150 pb-6 dark:border-navy-600">
            <div>
                <div class="flex items-center space-x-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                        <Package class="size-5" />
                    </span>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">Order {{ order.order_number }}</h1>
                </div>
                <p class="text-sm text-slate-400 dark:text-navy-300 mt-1 flex items-center gap-1.5 pl-0.5">
                    <Clock class="size-3.5" /> Placed on {{ order.created_at }}
                </p>
            </div>
            <div>
                <Link
                    href="/customer/orders"
                    class="btn border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 focus:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 rounded-xl shadow-xs transition-all duration-200"
                >
                    ← Back to orders
                </Link>
            </div>
        </div>

        <!-- Status Badges -->
        <div class="mt-6 flex flex-wrap gap-3 text-sm">
            <div class="card flex items-center space-x-2 px-4 py-2.5 dark:bg-navy-750 border border-slate-150 dark:border-navy-600 rounded-xl shadow-xs">
                <span class="text-xs text-slate-400 dark:text-navy-300 font-medium">Payment:</span>
                <span class="badge px-3 py-1 rounded-full font-semibold capitalize text-xs shadow-xs" :class="badgeClass(order.payment_status)">
                    {{ order.payment_status }}
                </span>
            </div>
            <div class="card flex items-center space-x-2 px-4 py-2.5 dark:bg-navy-750 border border-slate-150 dark:border-navy-600 rounded-xl shadow-xs">
                <span class="text-xs text-slate-400 dark:text-navy-300 font-medium">Fulfilment:</span>
                <span class="badge px-3 py-1 rounded-full font-semibold capitalize text-xs shadow-xs" :class="badgeClass(order.fulfilment_status)">
                    {{ order.fulfilment_status }}
                </span>
            </div>
        </div>

        <!-- Order Grid: Items & Sidebar Summary/Address -->
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <!-- Items List (2 Columns on Large Screens) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="card p-5 dark:bg-navy-750 border border-slate-150 dark:border-navy-600 rounded-2xl shadow-xl">
                    <div class="flex items-center gap-2 border-b border-slate-150 pb-4 dark:border-navy-600">
                        <PackageCheck class="size-5 text-slate-400" />
                        <h2 class="text-base font-semibold text-slate-800 dark:text-navy-50">
                            Order Items ({{ order.items.length }})
                        </h2>
                    </div>
                    
                    <div class="divide-y divide-slate-150 dark:divide-navy-600 mt-4">
                        <div v-for="item in order.items" :key="item.id" class="py-4 first:pt-0 last:pb-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-navy-50">{{ item.product_name }}</p>
                                    <p class="font-mono text-xs text-slate-400 dark:text-navy-300">SKU: {{ item.sku }}</p>
                                    
                                    <div v-if="item.attributes.length" class="flex flex-wrap gap-1.5 pt-1">
                                        <span
                                            v-for="(a, i) in item.attributes"
                                            :key="i"
                                            class="badge bg-slate-150 text-slate-600 dark:bg-navy-600 dark:text-navy-200 px-2 py-0.5 rounded text-xs"
                                        >
                                            {{ a.name }}: {{ a.value }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-slate-500 dark:text-navy-300 pt-1">
                                        Qty: <span class="font-medium text-slate-700 dark:text-navy-100">{{ item.quantity }}</span> × <span class="font-mono">KES {{ item.unit_price }}</span>
                                    </p>
                                </div>
                                <div class="text-right space-y-2">
                                    <p class="text-base font-bold font-mono text-slate-800 dark:text-navy-50">KES {{ item.line_total }}</p>
                                    <button
                                        v-if="item.can_review"
                                        type="button"
                                        class="btn bg-primary/10 text-primary hover:bg-primary hover:text-white dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent px-3 py-1.5 text-xs font-medium rounded-lg transition-colors shadow-xs"
                                        @click="openReview(item)"
                                    >
                                        Write review
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payments Section -->
                <div v-if="order.payments.length" class="card p-5 dark:bg-navy-750 border border-slate-150 dark:border-navy-600 rounded-2xl shadow-xl">
                    <div class="flex items-center gap-2 border-b border-slate-150 pb-4 dark:border-navy-600">
                        <CreditCard class="size-5 text-slate-400" />
                        <h2 class="text-base font-semibold text-slate-800 dark:text-navy-50">
                            Payment History
                        </h2>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div v-for="(p, i) in order.payments" :key="i" class="flex items-center justify-between p-3.5 rounded-xl bg-slate-100/60 dark:bg-navy-900/50 border border-slate-150/50 dark:border-navy-600/50">
                            <div class="space-y-0.5">
                                <p class="text-sm font-medium text-slate-700 dark:text-navy-200 capitalize flex items-center gap-2">
                                    {{ p.provider }} · <span class="badge px-2 py-0.5 rounded text-2xs uppercase font-semibold bg-success/10 text-success dark:bg-success/20">{{ p.status }}</span>
                                </p>
                                <p v-if="p.provider_reference" class="font-mono text-xs text-slate-400 dark:text-navy-300">
                                    Receipt: {{ p.provider_reference }}
                                </p>
                                <p v-if="p.paid_at" class="text-xs text-slate-400 dark:text-navy-300">{{ p.paid_at }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold font-mono text-slate-800 dark:text-navy-50">KES {{ p.amount }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar: Summary & Delivery Address -->
            <div class="space-y-6">
                <!-- Order Summary Card -->
                <div class="card p-5 dark:bg-navy-750 border border-slate-150 dark:border-navy-600 rounded-2xl shadow-xl">
                    <h2 class="text-base font-semibold text-slate-800 dark:text-navy-50 border-b border-slate-150 pb-4 dark:border-navy-600">
                        Order Summary
                    </h2>
                    <dl class="space-y-3 text-sm mt-4 text-slate-600 dark:text-navy-200">
                        <div class="flex justify-between">
                            <dt class="text-slate-400 dark:text-navy-300">Subtotal</dt>
                            <dd class="font-medium font-mono text-slate-800 dark:text-navy-50">KES {{ order.subtotal }}</dd>
                        </div>
                        <div v-if="Number(order.discount_total) > 0" class="flex justify-between text-success">
                            <dt>Discount <span class="font-mono text-xs">({{ order.coupon_code }})</span></dt>
                            <dd class="font-mono">− KES {{ order.discount_total }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400 dark:text-navy-300">Shipping Fee</dt>
                            <dd class="font-medium font-mono text-slate-800 dark:text-navy-50">KES {{ order.shipping_fee }}</dd>
                        </div>
                        <div class="flex justify-between border-t border-slate-150 pt-4 text-base font-bold text-slate-800 dark:border-navy-600 dark:text-navy-50">
                            <dt>Grand Total</dt>
                            <dd class="font-mono text-primary dark:text-accent-light">KES {{ order.grand_total }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Delivery Address Card -->
                <div class="card p-5 dark:bg-navy-750 border border-slate-150 dark:border-navy-600 rounded-2xl shadow-xl">
                    <div class="flex items-center space-x-2 border-b border-slate-150 pb-4 dark:border-navy-600">
                        <MapPin class="size-5 text-primary dark:text-accent-light" />
                        <h2 class="text-base font-semibold text-slate-800 dark:text-navy-50">Delivery Address</h2>
                    </div>
                    <div class="mt-4 space-y-1.5 text-sm text-slate-600 dark:text-navy-200">
                        <p class="font-semibold text-slate-800 dark:text-navy-50">{{ order.shipping_address.county }} — {{ order.shipping_address.town }}</p>
                        <p class="text-slate-500 dark:text-navy-300">{{ order.shipping_address.address }}</p>
                        <p v-if="order.shipping_address.instructions" class="text-xs italic text-slate-400 dark:text-navy-300 pt-1">
                            Note: "{{ order.shipping_address.instructions }}"
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Modal Dialog -->
        <div
            v-if="reviewItem"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
            @click.self="closeReview"
        >
            <div class="card w-full max-w-lg p-6 dark:bg-navy-750 border border-slate-150 dark:border-navy-600 shadow-2xl rounded-2xl">
                <div class="flex items-center justify-between border-b border-slate-150 pb-4 dark:border-navy-600">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-navy-50">Review Product</h2>
                    <button type="button" class="text-slate-400 hover:text-slate-700 dark:hover:text-navy-100" @click="closeReview">
                        ✕
                    </button>
                </div>

                <p class="text-xs font-semibold text-slate-600 dark:text-navy-200 mt-3">{{ reviewItem.product_name }}</p>

                <form class="mt-4 space-y-4" @submit.prevent="submitReview">
                    <div>
                        <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Rating</label>
                        <div class="mt-1 flex gap-1.5">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                class="text-2xl transition-transform hover:scale-110"
                                :class="star <= reviewForm.rating ? 'text-warning' : 'text-slate-300 dark:text-navy-500'"
                                @click="reviewForm.rating = star"
                            >
                                ★
                            </button>
                        </div>
                        <p v-if="reviewForm.errors.rating" class="mt-1 text-xs text-error">{{ reviewForm.errors.rating }}</p>
                    </div>

                    <div>
                        <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Title (optional)</label>
                        <input
                            v-model="reviewForm.title"
                            placeholder="Summary of your experience"
                            class="form-input mt-1.5 w-full rounded-xl bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100 border border-transparent"
                        />
                    </div>

                    <div>
                        <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Your Review</label>
                        <textarea
                            v-model="reviewForm.body"
                            rows="4"
                            placeholder="Write details about your product quality and satisfaction..."
                            class="form-textarea mt-1.5 w-full rounded-xl bg-slate-150 p-3 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100 border border-transparent"
                        />
                    </div>

                    <p class="text-xs text-slate-400 dark:text-navy-300 italic">
                        * Reviews will be published publicly after admin approval.
                    </p>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-150 dark:border-navy-600">
                        <button
                            type="button"
                            class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 rounded-xl px-4 py-2"
                            @click="closeReview"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="reviewForm.processing"
                            class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus disabled:opacity-50 rounded-xl px-5 py-2 shadow-lg"
                        >
                            {{ reviewForm.processing ? 'Submitting…' : 'Submit review' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>