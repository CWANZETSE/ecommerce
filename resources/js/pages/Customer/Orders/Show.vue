<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import type { PageProps } from '@/types';
import {
    ShoppingBag,
    ShieldCheck,
    Package,
    Truck,
    CheckCircle2,
    X,
    ArrowLeft,
    CreditCard,
    MapPin,
    MoreHorizontal,
    Clock,
    Star,
    PackageCheck,
} from '@lucide/vue';

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

/* ---------------- Review modal ---------------- */
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
        { preserveScroll: true, onSuccess: () => { reviewItem.value = null; } },
    );
}

/* ---------------- Status helpers ---------------- */
function badgeClass(status: string): string {
    switch (status) {
        case 'paid':
        case 'delivered':
        case 'completed':
            return 'badge space-x-2 rounded-full bg-success/10 text-success dark:bg-success/15 font-semibold';
        case 'pending':
            return 'badge space-x-2 rounded-full bg-warning/10 text-warning dark:bg-warning/15 font-semibold';
        case 'processing':
        case 'shipped':
            return 'badge space-x-2 rounded-full bg-info/10 text-info dark:bg-info/15 font-semibold';
        case 'failed':
        case 'cancelled':
            return 'badge space-x-2 rounded-full bg-error/10 text-error dark:bg-error/15 font-semibold';
        default:
            return 'bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200';
    }
}

/* ---------------- Lifecycle model ---------------- */
type StageTone = 'success' | 'info' | 'secondary' | 'warning' | 'error';

interface Stage {
    key: string;
    label: string;
    description: string;
    icon: any;
    tone: StageTone;
    timestamp: string | null;
    meta?: string | null;
}

const isCancelled = computed(() => {
    const f = props.order.fulfilment_status.toLowerCase();
    const p = props.order.payment_status.toLowerCase();
    return f === 'cancelled' || p === 'cancelled';
});

const reachedIndex = computed<number>(() => {
    if (isCancelled.value) {
        const f = props.order.fulfilment_status.toLowerCase();
        const p = props.order.payment_status.toLowerCase();
        if (f === 'cancelled' && p === 'paid') return 1;
        return 0;
    }
    const f = props.order.fulfilment_status.toLowerCase();
    const p = props.order.payment_status.toLowerCase();
    if (f === 'delivered' || f === 'completed') return 4;
    if (f === 'shipped') return 3;
    if (f === 'processing') return 2;
    if (p === 'paid') return 1;
    return 0;
});

const primaryPayment = computed<Payment | undefined>(() =>
    props.order.payments.find(p => p.status.toLowerCase() === 'success' || p.status.toLowerCase() === 'paid')
    ?? props.order.payments[0],
);

const stages = computed<Stage[]>(() => {
    const paidAt = props.order.paid_at
        ?? props.order.payments.find(p => p.paid_at)?.paid_at
        ?? null;

    return [
        {
            key: 'placed',
            label: 'Order Placed',
            description: 'We received your order and logged it into our system.',
            icon: ShoppingBag,
            tone: 'success',
            timestamp: props.order.created_at,
            meta: `Order ${props.order.order_number}`,
        },
        {
            key: 'paid',
            label: 'Payment Verified',
            description: props.order.payment_status === 'paid'
                ? 'Payment confirmed. Your order is now queued for fulfilment.'
                : 'Awaiting payment confirmation from the provider.',
            icon: ShieldCheck,
            tone: 'success',
            timestamp: paidAt,
            meta: primaryPayment.value
                ? `${primaryPayment.value.provider}${primaryPayment.value.provider_reference ? ' · ' + primaryPayment.value.provider_reference : ''}`
                : null,
        },
        {
            key: 'processing',
            label: 'Processing',
            description: 'Your items are being picked and packed in the warehouse.',
            icon: Package,
            tone: 'info',
            timestamp: null,
            meta: null,
        },
        {
            key: 'shipped',
            label: 'Shipped',
            description: 'Your parcel has left our warehouse and is on its way.',
            icon: Truck,
            tone: 'info',
            timestamp: null,
            meta: null,
        },
        {
            key: 'delivered',
            label: 'Delivered',
            description: 'Order delivered successfully. Thank you for shopping with us!',
            icon: CheckCircle2,
            tone: 'success',
            timestamp: null,
            meta: null,
        },
    ];
});

const tonePoint: Record<StageTone, string> = {
    success: 'border-current bg-white text-success dark:bg-navy-700 dark:text-success-light',
    info: 'border-current bg-white text-info dark:bg-navy-700 dark:text-info-light',
    secondary: 'border-current bg-white text-secondary dark:bg-navy-700 dark:text-secondary-light',
    warning: 'border-current bg-white text-warning dark:bg-navy-700 dark:text-warning-light',
    error: 'border-current bg-white text-error dark:bg-navy-700 dark:text-error-light',
};

type StageState = 'done' | 'current' | 'future' | 'cancelled';

const stageStates = computed<StageState[]>(() => {
    const states: StageState[] = [];
    const reached = reachedIndex.value;
    for (let i = 0; i < stages.value.length; i++) {
        if (isCancelled.value) {
            if (i < reached) states.push('done');
            else if (i === reached) states.push('cancelled');
            else states.push('future');
        } else {
            if (i < reached) states.push('done');
            else if (i === reached) states.push('current');
            else states.push('future');
        }
    }
    return states;
});

const showCancelNode = computed(() => isCancelled.value);
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        <!-- ================= Header ================= -->
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 dark:border-navy-600 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary shadow-xs dark:bg-accent-light/10 dark:text-accent-light">
                        <ShoppingBag class="size-6" />
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-navy-50">
                                {{ order.order_number }}
                            </h1>
                            <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                <ShieldCheck class="size-3.5" />
                                Verified Secure Order
                            </span>
                        </div>
                        <p class="mt-0.5 flex items-center gap-1.5 text-xs font-medium text-slate-400 dark:text-navy-300">
                            <Clock class="size-3.5" />
                            Placed on {{ order.created_at }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link href="/customer/orders"
                    class="btn inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-xs transition-all hover:bg-slate-200 dark:bg-navy-700 dark:text-navy-100 dark:hover:bg-navy-600">
                    <ArrowLeft class="size-4" />
                    Back to orders
                </Link>
            </div>
        </div>

        <!-- ================= Unified Top Card (50/50 Split on LG) ================= -->
        <div class="card mt-6 overflow-hidden rounded-2xl border border-slate-200/80 shadow-sm dark:border-navy-600 dark:bg-navy-750">
            <div class="grid grid-cols-1 lg:grid-cols-2">

                <!-- Left: Live Lifecycle Status (1/2 width on lg) -->
                <div class="border-b border-slate-150 dark:border-navy-600 lg:border-b-0 lg:border-r">
                    <div class="flex h-full flex-col pb-4">
                        <!-- Timeline Header -->
                        <div class="flex items-center justify-between px-4 pt-5 pb-2 sm:px-5">
                            <h2 class="font-medium tracking-wide text-slate-700 dark:text-navy-100">
                                Live Lifecycle Status
                            </h2>
                            <div class="flex items-center gap-2">
                                <div class="inline-flex">
                                    <button class="btn -mr-1.5 size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                        <MoreHorizontal class="size-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Status chips -->
                        <div class="flex flex-wrap items-center gap-2 px-4 pb-3 sm:px-5">
                            <div class="flex items-center space-x-2 rounded-lg border border-slate-200/60 bg-slate-50 px-3 py-1.5 text-xs dark:border-navy-600 dark:bg-navy-900">
                                <span class="font-medium text-slate-400">Payment:</span>
                                <span :class="badgeClass(order.payment_status)">{{ order.payment_status }}</span>
                            </div>
                            <div class="flex items-center space-x-2 rounded-lg border border-slate-200/60 bg-slate-50 px-3 py-1.5 text-xs dark:border-navy-600 dark:bg-navy-900">
                                <span class="font-medium text-slate-400">Fulfilment:</span>
                                <span :class="badgeClass(order.fulfilment_status)">{{ order.fulfilment_status }}</span>
                            </div>
                        </div>

                        <!-- Vertical timeline -->
                        <ol class="timeline line-space flex-1 px-4 [--size:1.5rem] sm:px-5">
                            <li
                                v-for="(stage, i) in stages"
                                :key="stage.key"
                                class="timeline-item"
                            >
                                <!-- Point -->
                                <div class="relative">
                                    <div
                                        class="timeline-item-point flex items-center justify-center rounded-full border"
                                        :class="stageStates[i] === 'future'
                                            ? 'border-slate-300 bg-white text-slate-300 dark:border-navy-600 dark:bg-navy-700 dark:text-navy-600'
                                            : tonePoint[stage.tone]"
                                    >
                                        <component :is="stage.icon" class="size-3" />
                                    </div>

                                    <!-- Corner ping indicator on current stage -->
                                    <span
                                        v-if="stageStates[i] === 'current'"
                                        class="absolute -top-px -right-px flex size-3 items-center justify-center"
                                    >
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-secondary opacity-80"></span>
                                        <span class="inline-flex size-2 rounded-full bg-secondary"></span>
                                    </span>
                                </div>

                                <!-- Content -->
                                <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                    <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                        <p class="flex items-center gap-2 pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0">
                                            {{ stage.label }}

                                            <span
                                                v-if="stageStates[i] === 'current'"
                                                class="inline-flex items-center rounded-full bg-secondary/10 px-2 py-0.5 text-2xs font-bold uppercase tracking-wide text-secondary dark:bg-secondary-light/15 dark:text-secondary-light"
                                            >
                                                Current
                                            </span>
                                            <span
                                                v-else-if="stageStates[i] === 'done'"
                                                class="inline-flex items-center rounded-full bg-success/10 px-2 py-0.5 text-2xs font-bold uppercase tracking-wide text-success dark:bg-success/15"
                                            >
                                                Done
                                            </span>
                                            <span
                                                v-else-if="stageStates[i] === 'cancelled'"
                                                class="inline-flex items-center rounded-full bg-error/10 px-2 py-0.5 text-2xs font-bold uppercase tracking-wide text-error dark:bg-error/15"
                                            >
                                                Cancelled here
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center rounded-full bg-slate-150 px-2 py-0.5 text-2xs font-bold uppercase tracking-wide text-slate-500 dark:bg-navy-600 dark:text-navy-300"
                                            >
                                                Pending
                                            </span>
                                        </p>

                                        <span v-if="stage.timestamp" class="text-xs text-slate-400 dark:text-navy-300">
                                            {{ stage.timestamp }}
                                        </span>
                                        <span v-else-if="stageStates[i] === 'future'" class="text-xs text-slate-300 dark:text-navy-500">
                                            Not yet
                                        </span>
                                    </div>

                                    <p
                                        class="py-1"
                                        :class="stageStates[i] === 'future' ? 'text-slate-400 dark:text-navy-500' : 'text-slate-500 dark:text-navy-200'"
                                    >
                                        {{ stage.description }}
                                    </p>

                                    <div v-if="stage.meta" class="pt-1">
                                        <span class="tag rounded-full border border-secondary/30 bg-secondary/10 text-secondary dark:border-secondary-light/30 dark:bg-secondary-light/10 dark:text-secondary-light">
                                            {{ stage.meta }}
                                        </span>
                                    </div>
                                </div>
                            </li>

                            <!-- Terminal: Cancelled -->
                            <li v-if="showCancelNode" class="timeline-item">
                                <div class="timeline-item-point flex items-center justify-center rounded-full border border-current bg-white text-error dark:bg-navy-700 dark:text-error-light">
                                    <X class="size-3" />
                                </div>
                                <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                    <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                        <p class="flex items-center gap-2 pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0">
                                            Order Cancelled
                                            <span class="inline-flex items-center rounded-full bg-error/10 px-2 py-0.5 text-2xs font-bold uppercase tracking-wide text-error dark:bg-error/15">
                                                Terminal
                                            </span>
                                        </p>
                                    </div>
                                    <p class="py-1 text-slate-500 dark:text-navy-200">
                                        This order was cancelled before completion. If payment was captured, a refund will be processed automatically.
                                    </p>
                                    <div class="pt-1">
                                        <span class="tag rounded-full border border-error/30 bg-error/10 text-error">
                                            Flow terminated
                                        </span>
                                    </div>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Right Column (1/2 width on lg): Order Summary (top) & Delivery Address (bottom) -->
                <div class="flex flex-col">
                    <!-- Right Top: Order Summary -->
                    <div class="border-b border-slate-150 p-6 dark:border-navy-600">
                        <h2 class="border-b border-slate-150 pb-4 text-base font-bold text-slate-900 dark:border-navy-600 dark:text-navy-50">
                            Order Summary
                        </h2>
                        <table class="mt-3 w-full divide-y divide-slate-150 text-sm text-slate-600 dark:divide-navy-600 dark:text-navy-200">
                            <tbody>
                                <tr>
                                    <td class="py-3 text-slate-400 dark:text-navy-300">Subtotal</td>
                                    <td class="py-3 text-right  font-semibold text-slate-800 dark:text-navy-50">KES {{ order.subtotal }}</td>
                                </tr>
                                <tr v-if="Number(order.discount_total) > 0">
                                    <td class="py-3 font-medium text-success">
                                        Discount
                                        <span class="rounded bg-success/10 px-1 py-0.5  text-2xs">({{ order.coupon_code }})</span>
                                    </td>
                                    <td class="py-3 text-right  text-success">− KES {{ order.discount_total }}</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-slate-400 dark:text-navy-300">Shipping Fee</td>
                                    <td class="py-3 text-right  font-semibold text-slate-800 dark:text-navy-50">KES {{ order.shipping_fee }}</td>
                                </tr>
                                <tr class="border-t-2 border-slate-200 text-base font-black text-slate-900 dark:border-navy-600 dark:text-navy-50">
                                    <td class="py-4">Grand Total</td>
                                    <td class="py-4 text-right  text-lg text-primary dark:text-accent-light">KES {{ order.grand_total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Right Bottom: Delivery Address -->
                    <div class="flex-1 p-6">
                        <div class="flex items-center space-x-2.5 border-b border-slate-150 pb-4 dark:border-navy-600">
                            <div class="rounded-lg bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                <MapPin class="size-5" />
                            </div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-navy-50">Delivery Address Details</h2>
                        </div>

                        <table class="mt-3 w-full divide-y divide-slate-150 text-sm text-slate-600 dark:divide-navy-600 dark:text-navy-200">
                            <tbody>
                                <tr>
                                    <td class="py-3 font-medium text-slate-400 dark:text-navy-300">Destination</td>
                                    <td class="py-3 text-right font-bold text-slate-900 dark:text-navy-50">
                                        {{ order.shipping_address.county }} — {{ order.shipping_address.town }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-medium text-slate-400 dark:text-navy-300">Street Address</td>
                                    <td class="py-3 text-right text-slate-700 dark:text-navy-100">{{ order.shipping_address.address }}</td>
                                </tr>
                                <tr v-if="order.shipping_address.instructions">
                                    <td class="py-3 font-medium text-slate-400 dark:text-navy-300">Instructions</td>
                                    <td class="py-3 text-right text-xs italic text-slate-500 dark:text-navy-300">"{{ order.shipping_address.instructions }}"</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= Full-width: Order Items + Payment Transactions ================= -->
        <div class="mt-6 space-y-6">

            <!-- Order Items Table (full width) -->
            <div class="card rounded-2xl border border-slate-200/80 p-6 shadow-sm dark:border-navy-600 dark:bg-navy-750">
                <div class="flex items-center justify-between border-b border-slate-150 pb-4 dark:border-navy-600">
                    <div class="flex items-center gap-2.5">
                        <div class="rounded-lg bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            <PackageCheck class="size-5" />
                        </div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-navy-50">
                            Order Items <span class="ml-1 text-xs font-normal text-slate-400">({{ order.items.length }})</span>
                        </h2>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-200 text-2xs uppercase tracking-wider text-slate-400 dark:border-navy-600 dark:text-navy-300">
                                <th class="px-3 py-3 font-semibold">Item &amp; Attributes</th>
                                <th class="px-3 py-3 text-center font-semibold">Qty</th>
                                <th class="px-3 py-3 text-right font-semibold">Unit Price</th>
                                <th class="px-3 py-3 text-right font-semibold">Total</th>
                                <th class="px-3 py-3 text-center font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-150 text-sm dark:divide-navy-600">
                            <tr v-for="item in order.items" :key="item.id" class="transition-colors hover:bg-slate-50/60 dark:hover:bg-navy-900/30">
                                <td class="px-3 py-4">
                                    <p class="font-bold text-slate-900 dark:text-navy-50">{{ item.product_name }}</p>
                                    <p class="mt-0.5  text-2xs text-slate-400">SKU: {{ item.sku }}</p>
                                    <div v-if="item.attributes.length" class="mt-1.5 flex flex-wrap gap-1">
                                        <span v-for="(a, ai) in item.attributes" :key="ai"
                                            class="badge rounded bg-slate-150 px-2 py-0.5 text-2xs font-medium text-slate-600 dark:bg-navy-600 dark:text-navy-200">
                                            {{ a.name }}: {{ a.value }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-4 text-center font-bold text-slate-700 dark:text-navy-100">
                                    {{ item.quantity }}
                                </td>
                                <td class="px-3 py-4 text-right  font-medium text-slate-600 dark:text-navy-200">
                                    KES {{ item.unit_price }}
                                </td>
                                <td class="px-3 py-4 text-right  font-black text-slate-900 dark:text-navy-50">
                                    KES {{ item.line_total }}
                                </td>
                                <td class="px-3 py-4 text-center">
                                    <button v-if="item.can_review" type="button"
                                        class="btn rounded-xl bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary shadow-xs transition-all hover:bg-primary hover:text-white dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent"
                                        @click="openReview(item)">
                                        Review
                                    </button>
                                    <span v-else class="text-2xs text-slate-400">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Transactions Table (full width) -->
            <div v-if="order.payments.length"
                class="card rounded-2xl border border-slate-200/80 p-6 shadow-sm dark:border-navy-600 dark:bg-navy-750">
                <div class="flex items-center gap-2.5 border-b border-slate-150 pb-4 dark:border-navy-600">
                    <div class="rounded-lg bg-success/10 p-2 text-success">
                        <CreditCard class="size-5" />
                    </div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-navy-50">Payment Transactions</h2>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-200 text-2xs uppercase tracking-wider text-slate-400 dark:border-navy-600 dark:text-navy-300">
                                <th class="px-3 py-3 font-semibold">Provider / Ref</th>
                                <th class="px-3 py-3 font-semibold">Status</th>
                                <th class="px-3 py-3 font-semibold">Timestamp</th>
                                <th class="px-3 py-3 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-150 text-sm dark:divide-navy-600">
                            <tr v-for="(p, i) in order.payments" :key="i" class="transition-colors hover:bg-slate-50/60 dark:hover:bg-navy-900/30">
                                <td class="px-3 py-3.5">
                                    <p class="font-bold capitalize text-slate-800 dark:text-navy-100">{{ p.provider }}</p>
                                    <p v-if="p.provider_reference" class=" text-2xs text-slate-400">Ref: {{ p.provider_reference }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="badge rounded bg-success/10 px-2.5 py-0.5 text-2xs font-bold uppercase text-success dark:bg-success/20">{{ p.status }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-xs text-slate-500 dark:text-navy-300">
                                    {{ p.paid_at || '—' }}
                                </td>
                                <td class="px-3 py-3.5 text-right  font-bold text-slate-900 dark:text-navy-50">
                                    KES {{ p.amount }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= Review Modal ================= -->
        <div v-if="reviewItem"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs transition-opacity"
            @click.self="closeReview">
            <div class="card w-full max-w-lg animate-in rounded-2xl border border-slate-200 p-6 shadow-2xl fade-in zoom-in-95 duration-200 dark:border-navy-600 dark:bg-navy-750">
                <div class="flex items-center justify-between border-b border-slate-150 pb-4 dark:border-navy-600">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-navy-50">
                        <Star class="size-5 text-warning" />
                        Rate &amp; Review Product
                    </h2>
                    <button type="button" class="flex size-8 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-navy-700 dark:hover:text-navy-100"
                        @click="closeReview">
                        <X class="size-4" />
                    </button>
                </div>

                <div class="mt-4 rounded-xl border border-slate-150 bg-slate-50 p-3 dark:border-navy-600 dark:bg-navy-900">
                    <p class="text-xs font-bold text-slate-800 dark:text-navy-100">{{ reviewItem.product_name }}</p>
                    <p class="mt-0.5  text-2xs text-slate-400">SKU: {{ reviewItem.sku }}</p>
                </div>

                <form class="mt-4 space-y-4" @submit.prevent="submitReview">
                    <div>
                        <label class="text-xs font-bold text-slate-700 dark:text-navy-200">Overall Rating</label>
                        <div class="mt-1.5 flex gap-2">
                            <button v-for="star in 5" :key="star" type="button"
                                class="transition-transform hover:scale-125 focus:outline-hidden"
                                @click="reviewForm.rating = star">
                                <Star
                                    class="size-8"
                                    :class="star <= reviewForm.rating ? 'fill-warning text-warning drop-shadow-xs' : 'text-slate-300 dark:text-navy-500'"
                                />
                            </button>
                        </div>
                        <p v-if="reviewForm.errors.rating" class="mt-1 text-xs font-medium text-error">{{ reviewForm.errors.rating }}</p>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-700 dark:text-navy-200">Review Headline</label>
                        <input v-model="reviewForm.title" placeholder="e.g. Excellent quality, fast service!"
                            class="form-input mt-1.5 w-full rounded-xl border border-transparent bg-slate-100 px-4 py-2.5 placeholder:text-slate-400 ring-primary/50 hover:bg-slate-150 focus:ring-2 dark:bg-navy-900 dark:text-navy-100" />
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-700 dark:text-navy-200">Detailed Feedback</label>
                        <textarea v-model="reviewForm.body" rows="4"
                            placeholder="Share your experience regarding product quality, usability, or delivery..."
                            class="form-textarea mt-1.5 w-full rounded-xl border border-transparent bg-slate-100 p-4 placeholder:text-slate-400 ring-primary/50 hover:bg-slate-150 focus:ring-2 dark:bg-navy-900 dark:text-navy-100" />
                    </div>

                    <div class="rounded-xl bg-warning/10 p-3 text-xs font-medium text-warning dark:bg-warning/15">
                        💡 Reviews are verified and published publicly after admin approval.
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-150 pt-4 dark:border-navy-600">
                        <button type="button"
                            class="btn rounded-xl border border-slate-300 px-4 py-2.5 font-semibold text-slate-700 transition-colors hover:bg-slate-100 dark:border-navy-500 dark:text-navy-200 dark:hover:bg-navy-600"
                            @click="closeReview">
                            Cancel
                        </button>
                        <button type="submit" :disabled="reviewForm.processing"
                            class="btn rounded-xl bg-primary px-6 py-2.5 font-semibold text-white shadow-lg transition-all hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 disabled:opacity-50 dark:bg-accent dark:hover:bg-accent-focus">
                            {{ reviewForm.processing ? 'Submitting…' : 'Submit Review' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>