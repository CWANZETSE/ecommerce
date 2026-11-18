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
    shipping_address: {
        county: string;
        town: string;
        address: string;
        instructions?: string | null;
    };
    paid_at: string | null;
    items: OrderItem[];
    payments: Payment[];
}

const props = defineProps<PageProps & { order: Order }>();

/* ---------------- Normalised statuses ---------------- */

const paymentStatus = computed(() =>
    String(props.order.payment_status ?? '').toLowerCase(),
);

const fulfilmentStatus = computed(() =>
    String(props.order.fulfilment_status ?? '').toLowerCase(),
);

/* ---------------- Review modal ---------------- */

const reviewItem = ref<OrderItem | null>(null);
const reviewForm = useForm({ rating: 5, title: '', body: '' });

function openReview(item: OrderItem) {
    reviewItem.value = item;
    reviewForm.reset();
    reviewForm.clearErrors();
}

function closeReview() {
    if (reviewForm.processing) return;

    reviewItem.value = null;
}

function submitReview() {
    if (!reviewItem.value) return;

    reviewForm.post(
        `/customer/orders/${props.order.ulid}/items/${reviewItem.value.id}/review`,
        {
            preserveScroll: true,
            onSuccess: () => {
                reviewItem.value = null;
            },
        },
    );
}

/* ---------------- Status helpers ---------------- */

function badgeClass(status: string): string {
    switch (String(status ?? '').toLowerCase()) {
        case 'paid':
        case 'delivered':
        case 'completed':
        case 'success':
            return 'badge space-x-2.5 rounded-full bg-success/10 text-success dark:bg-success/15';

        case 'pending':
            return 'badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15';

        case 'processing':
        case 'dispatched':
            return 'badge space-x-2.5 rounded-full bg-info/10 text-info dark:bg-info/15';

        case 'failed':
        case 'cancelled':
            return 'badge space-x-2.5 rounded-full bg-error/10 text-error dark:bg-error/15';

        default:
            return 'badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-primary/15';
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

/*
|--------------------------------------------------------------------------
| Cancellation and payment failure
|--------------------------------------------------------------------------
*/

const isCancelled = computed(() =>
    paymentStatus.value === 'cancelled' ||
    fulfilmentStatus.value === 'cancelled',
);

const isPaymentFailed = computed(() =>
    paymentStatus.value === 'failed',
);

/*
|--------------------------------------------------------------------------
| Current lifecycle position
|--------------------------------------------------------------------------
|
| Fulfilment:
| pending -> processing -> dispatched -> delivered
|
| Payment:
| pending -> processing -> paid
|
| A paid order with pending fulfilment has reached the payment stage.
*/

const reachedIndex = computed<number>(() => {
    const f = fulfilmentStatus.value;
    const p = paymentStatus.value;

    // Preserve the last meaningful stage when an order is cancelled.
    if (isCancelled.value) {
        if (p === 'paid') return 1;
        return 0;
    }

    // Failed payment cannot progress into fulfilment.
    if (isPaymentFailed.value) {
        return 0;
    }

    switch (f) {
        case 'delivered':
            return 4;

        case 'dispatched':
            return 3;

        case 'processing':
            return 2;

        case 'pending':
        default:
            return p === 'paid' ? 1 : 0;
    }
});

/*
|--------------------------------------------------------------------------
| Primary payment
|--------------------------------------------------------------------------
*/

const primaryPayment = computed<Payment | undefined>(() =>
    props.order.payments.find(p =>
        ['success', 'paid'].includes(
            String(p.status ?? '').toLowerCase(),
        ),
    ) ?? props.order.payments[0],
);

/*
|--------------------------------------------------------------------------
| Timeline stages
|--------------------------------------------------------------------------
*/

const stages = computed<Stage[]>(() => {
    const paidAt =
        props.order.paid_at ??
        props.order.payments.find(p => p.paid_at)?.paid_at ??
        null;

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
            description:
                paymentStatus.value === 'paid'
                    ? 'Payment confirmed. Your order is now queued for fulfilment.'
                    : paymentStatus.value === 'processing'
                        ? 'Your payment is currently being processed.'
                        : paymentStatus.value === 'failed'
                            ? 'Payment could not be completed. Please contact support if you need assistance.'
                            : paymentStatus.value === 'cancelled'
                                ? 'The payment was cancelled.'
                                : 'Awaiting payment confirmation from the provider.',
            icon: ShieldCheck,
            tone: 'success',
            timestamp: paidAt,
            meta: primaryPayment.value
                ? `${primaryPayment.value.provider}${primaryPayment.value.provider_reference
                    ? ' · ' + primaryPayment.value.provider_reference
                    : ''
                }`
                : null,
        },
        {
            key: 'processing',
            label: 'Processing',
            description:
                ['processing', 'dispatched', 'delivered'].includes(fulfilmentStatus.value)
                    ? 'Your items are being picked and packed in the warehouse.'
                    : 'Your order will be prepared after payment confirmation.',
            icon: Package,
            tone: 'info',
            timestamp: null,
            meta: null,
        },
        {
            key: 'dispatched',
            label: 'Dispatched',
            description:
                ['dispatched', 'delivered'].includes(fulfilmentStatus.value)
                    ? 'Your parcel has left our warehouse and is on its way.'
                    : 'Your order has not yet been dispatched.',
            icon: Truck,
            tone: 'info',
            timestamp: null,
            meta: null,
        },
        {
            key: 'delivered',
            label: 'Delivered',
            description:
                fulfilmentStatus.value === 'delivered'
                    ? 'Order delivered successfully. Thank you for shopping with us!'
                    : 'Your order has not yet been delivered.',
            icon: CheckCircle2,
            tone: 'success',
            timestamp: null,
            meta: null,
        },
    ];
});

/* ---------------- Timeline styling ---------------- */

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
            if (i < reached) {
                states.push('done');
            } else {
                states.push('future');
            }
        } else if (isPaymentFailed.value) {
            states.push(i === 0 ? 'done' : 'future');
        } else if (i < reached) {
            states.push('done');
        } else if (i === reached) {
            states.push('current');
        } else {
            states.push('future');
        }
    }

    return states;
});

const showCancelNode = computed(() => isCancelled.value);

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'Ksh' }).format(amount);
</script>


<template>

    <Head :title="`Order ${order.order_number}`" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        <!-- ================= Header ================= -->
        <div
            class="flex flex-col gap-4 border-b border-slate-200 pb-6 dark:border-navy-600 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-12 p-2 items-center justify-center rounded-2xl bg-primary/10 text-primary shadow-xs dark:bg-accent-light/10 dark:text-accent-light">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                <path opacity="0.4"
                                    d="M19.24 5.58006H18.84L15.46 2.20006C15.19 1.93006 14.75 1.93006 14.47 2.20006C14.2 2.47006 14.2 2.91006 14.47 3.19006L16.86 5.58006H7.14L9.53 3.19006C9.8 2.92006 9.8 2.48006 9.53 2.20006C9.26 1.93006 8.82 1.93006 8.54 2.20006L5.17 5.58006H4.77C3.87 5.58006 2 5.58006 2 8.14006C2 9.11006 2.2 9.75006 2.62 10.1701C2.86 10.4201 3.15 10.5501 3.46 10.6201C3.75 10.6901 4.06 10.7001 4.36 10.7001H19.64C19.95 10.7001 20.24 10.6801 20.52 10.6201C21.36 10.4201 22 9.82006 22 8.14006C22 5.58006 20.13 5.58006 19.24 5.58006Z"
                                    fill="#4f46e5"></path>
                                <path
                                    d="M19.6509 10.7001H4.36094C4.06094 10.7001 3.75094 10.6901 3.46094 10.6201L4.72094 18.3001C5.00094 20.0201 5.75094 22.0001 9.08094 22.0001H14.6909C18.0609 22.0001 18.6609 20.3101 19.0209 18.4201L20.5309 10.6201C20.2509 10.6801 19.9509 10.7001 19.6509 10.7001ZM10.6109 17.1601C10.6109 17.5501 10.3009 17.8601 9.91094 17.8601C9.52094 17.8601 9.21094 17.5501 9.21094 17.1601V13.8601C9.21094 13.4701 9.52094 13.1601 9.91094 13.1601C10.3009 13.1601 10.6109 13.4701 10.6109 13.8601V17.1601ZM14.8909 17.1601C14.8909 17.5501 14.5809 17.8601 14.1909 17.8601C13.8009 17.8601 13.4909 17.5501 13.4909 17.1601V13.8601C13.4909 13.4701 13.8009 13.1601 14.1909 13.1601C14.5809 13.1601 14.8909 13.4701 14.8909 13.8601V17.1601Z"
                                    fill="#4f46e5"></path>
                            </g>
                        </svg>
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-navy-50">
                                {{ order.order_number }}
                            </h1>
                            <span
                                class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                <ShieldCheck class="size-3.5" />
                                Verified Secure Order
                            </span>
                        </div>
                        <p
                            class="mt-0.5 flex items-center gap-1.5 text-xs font-medium text-slate-400 dark:text-navy-300">
                            <Clock class="size-3.5" />
                            Placed on {{ order.created_at }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link href="/customer/orders"
                    class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                    <ArrowLeft class="size-4" />
                    Back to orders
                </Link>
            </div>
        </div>

        <!-- ================= Full-width: Order Items + Payment Transactions ================= -->
        <div class="mt-6 space-y-6">

            <!-- Order Items Table (full width) -->
            <div
                class="card rounded-2xl border border-slate-200/80 p-6 shadow-sm dark:border-navy-600 dark:bg-navy-750">
                <div class="flex items-center justify-between border-b border-slate-150 pb-4 dark:border-navy-600">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="rounded-lg size-10 bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                    <path opacity="0.4"
                                        d="M21.0409 7.19L12.0009 12.42L2.96094 7.19C3.36094 6.45 3.94094 5.8 4.59094 5.44L9.93094 2.48C11.0709 1.84 12.9309 1.84 14.0709 2.48L19.4109 5.44C20.0609 5.8 20.6409 6.45 21.0409 7.19Z"
                                        fill="#4f46e5"></path>
                                    <path opacity="0.6"
                                        d="M12.0006 12.4199V21.9999C11.2506 21.9999 10.5006 21.8399 9.93062 21.5199L4.59063 18.5599C3.38063 17.8899 2.39062 16.2099 2.39062 14.8299V9.16994C2.39062 8.52994 2.61062 7.82994 2.96062 7.18994L12.0006 12.4199Z"
                                        fill="#4f46e5"></path>
                                    <path
                                        d="M21.61 9.16994V14.8299C21.61 16.2099 20.62 17.8899 19.41 18.5599L14.07 21.5199C13.5 21.8399 12.75 21.9999 12 21.9999V12.4199L21.04 7.18994C21.39 7.82994 21.61 8.52994 21.61 9.16994Z"
                                        fill="#4f46e5"></path>
                                </g>
                            </svg>
                        </div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-navy-50">
                            Order Items
                        </h2>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">


                    <div
                        class="overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-150/15 dark:bg-navy-800">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-left">
                                <thead>
                                    <tr
                                        class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-150/15 dark:bg-primary/[0.04] dark:text-navy-300">
                                        <th
                                            class="border border-slate-150 px-5 py-4 font-bold dark:border-slate-150/15">
                                            Item &amp; Attributes</th>
                                        <th
                                            class="whitespace-nowrap border border-slate-150 px-4 py-4 text-center font-bold dark:border-slate-150/15">
                                            Qty</th>
                                        <th
                                            class="whitespace-nowrap border border-slate-150 px-4 py-4 text-right font-bold dark:border-slate-150/15">
                                            Unit Price</th>
                                        <th
                                            class="whitespace-nowrap border border-slate-150 px-4 py-4 text-right font-bold dark:border-slate-150/15">
                                            Total</th>
                                        <th
                                            class="whitespace-nowrap border border-slate-150 px-5 py-4 text-center font-bold dark:border-slate-150/15">
                                            Action</th>
                                    </tr>
                                </thead>

                                <tbody class="text-sm">
                                    <tr v-for="item in order.items" :key="item.id"
                                        class="transition-colors duration-200 hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]">
                                        <!-- Product details -->
                                        <td
                                            class="min-w-[220px] border border-slate-150 px-5 py-4 dark:border-slate-150/15">
                                            <p class="font-semibold text-slate-900 dark:text-navy-50">
                                                {{ item.product_name }}
                                            </p>

                                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                                SKU: {{ item.sku }}
                                            </p>

                                            <div v-if="item.attributes.length" class="mt-2 flex flex-wrap gap-1.5">
                                                <span v-for="(a, ai) in item.attributes" :key="ai"
                                                    class="badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                                    {{ a.name }}:
                                                    <span class="ml-1 font-semibold">{{ a.value }}</span>
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Quantity -->
                                        <td
                                            class="whitespace-nowrap border border-slate-150 px-4 py-4 text-center dark:border-slate-150/15">
                                            <span
                                                class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15">
                                                {{ item.quantity }}
                                            </span>
                                        </td>

                                        <!-- Unit price -->
                                        <td
                                            class="whitespace-nowrap border border-slate-150 px-4 py-4 text-right font-medium tabular-nums text-slate-600 dark:border-slate-150/15 dark:text-navy-200">
                                            {{ formatCurrency(item.unit_price) }}
                                        </td>

                                        <!-- Line total -->
                                        <td
                                            class="whitespace-nowrap border border-slate-150 px-4 py-4 text-right font-bold tabular-nums text-slate-900 dark:border-slate-150/15 dark:text-navy-50">
                                            {{ formatCurrency(item.line_total) }}
                                        </td>

                                        <!-- Action -->
                                        <td
                                            class="whitespace-nowrap border border-slate-150 px-5 py-4 text-center dark:border-slate-150/15">
                                            <button v-if="item.can_review" type="button"
                                                class="inline-flex items-center justify-center rounded-lg border border-slate-150/20 bg-primary/5 px-3.5 py-2 text-xs font-semibold text-primary transition-all duration-200 hover:border-slate-150 hover:bg-primary hover:text-white hover:shadow-sm dark:border-accent-light/20 dark:bg-accent-light/10 dark:text-accent-light dark:hover:border-accent dark:hover:bg-accent dark:hover:text-white"
                                                @click="openReview(item)">
                                                Review
                                            </button>

                                            <span v-else class="text-xs text-slate-300 dark:text-navy-400">
                                                —
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>


                </div>
            </div>

            <!-- Payment Transactions Table (full width) -->
            <div
                class="card rounded-2xl border border-slate-200/80 p-6 shadow-sm dark:border-navy-600 dark:bg-navy-750">
                <div class="flex items-center gap-2.5">
                    <div
                        class="rounded-lg size-10 bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                <path opacity="0.4"
                                    d="M18.8105 6.24998H20.1706C19.9806 5.97998 19.7806 5.72998 19.5706 5.47998L18.8105 6.24998Z"
                                    fill="#4f46e5"></path>
                                <path opacity="0.4"
                                    d="M18.52 4.41982C18.27 4.20982 18.02 4.00982 17.75 3.81982V5.17982L18.52 4.41982Z"
                                    fill="#4f46e5"></path>
                                <path opacity="0.4"
                                    d="M17 7.75C16.9 7.75 16.81 7.73 16.71 7.69C16.53 7.61 16.38 7.47 16.3 7.28C16.26 7.19 16.24 7.09 16.24 6.99V2.99C16.24 2.98 16.25 2.97 16.25 2.95C14.96 2.35 13.52 2 12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 10.48 21.65 9.04 21.04 7.74C21.03 7.74 21.02 7.75 21 7.75H17Z"
                                    fill="#4f46e5"></path>
                                <path
                                    d="M19.5795 5.47994L22.5295 2.52994C22.8195 2.23994 22.8195 1.75994 22.5295 1.46994C22.2395 1.17994 21.7595 1.17994 21.4695 1.46994L18.5195 4.41994C18.8995 4.74994 19.2495 5.10994 19.5795 5.47994Z"
                                    fill="#4f46e5"></path>
                                <path
                                    d="M17.7498 3C17.7498 2.59 17.4098 2.25 16.9998 2.25C16.5998 2.25 16.2798 2.57 16.2598 2.96C16.7798 3.21 17.2798 3.49 17.7498 3.82V3Z"
                                    fill="#4f46e5"></path>
                                <path
                                    d="M21.7499 7C21.7499 6.59 21.4099 6.25 20.9999 6.25H20.1699C20.4999 6.72 20.7899 7.22 21.0299 7.74C21.4299 7.72 21.7499 7.4 21.7499 7Z"
                                    fill="#4f46e5"></path>
                                <path
                                    d="M13.75 11.82L12.75 11.47V9.25H12.83C13.34 9.25 13.75 9.7 13.75 10.25C13.75 10.66 14.09 11 14.5 11C14.91 11 15.25 10.66 15.25 10.25C15.25 8.87 14.17 7.75 12.83 7.75H12.75V7.5C12.75 7.09 12.41 6.75 12 6.75C11.59 6.75 11.25 7.09 11.25 7.5V7.75H10.95C9.74001 7.75 8.75 8.77 8.75 10.03C8.75 11.49 9.6 11.96 10.25 12.19L11.25 12.54V14.76H11.17C10.66 14.76 10.25 14.31 10.25 13.76C10.25 13.35 9.91 13.01 9.5 13.01C9.09 13.01 8.75 13.35 8.75 13.76C8.75 15.14 9.83001 16.26 11.17 16.26H11.25V16.51C11.25 16.92 11.59 17.26 12 17.26C12.41 17.26 12.75 16.92 12.75 16.51V16.26H13.05C14.26 16.26 15.25 15.24 15.25 13.98C15.25 12.51 14.4 12.04 13.75 11.82ZM10.74 10.76C10.4 10.64 10.25 10.57 10.25 10.02C10.25 9.59 10.57 9.24 10.95 9.24H11.25V10.93L10.74 10.76ZM13.05 14.75H12.75V13.06L13.26 13.24C13.6 13.36 13.75 13.43 13.75 13.98C13.75 14.4 13.43 14.75 13.05 14.75Z"
                                    fill="#4f46e5"></path>
                            </g>
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-navy-50">
                        Payment
                    </h2>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-slate-200 text-2xs uppercase tracking-wider text-slate-400 dark:border-navy-600 dark:text-navy-300">
                                <th class="px-3 py-3 font-semibold">Provider</th>
                                <th class="px-3 py-3 font-semibold">Status</th>
                                <th class="px-3 py-3 font-semibold">Timestamp</th>
                                <th class="px-3 py-3 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-150 text-sm dark:divide-navy-600">
                            <tr v-for="(p, i) in order.payments" :key="i"
                                class="transition-colors hover:bg-slate-50/60 dark:hover:bg-navy-900/30">
                                <td class="px-3 py-3.5">
                                    <p class="font-bold capitalize text-slate-800 dark:text-navy-100">{{ p.provider }}
                                    </p>
                                    <p v-if="p.provider_reference" class=" text-2xs text-slate-400">{{
                                        p.provider_reference }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span :class="badgeClass(p.status)">
                                        {{ p.status }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-xs text-slate-500 dark:text-navy-300">
                                    {{ p.paid_at || '—' }}
                                </td>
                                <td class="px-3 py-3.5 text-right  font-bold text-slate-900 dark:text-navy-50">
                                    {{ formatCurrency(p.amount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- ================= Unified Top Card (50/50 Split on LG) ================= -->
        <div
            class="card mt-6 overflow-hidden rounded-2xl border border-slate-200/80 shadow-sm dark:border-navy-600 dark:bg-navy-750">
            <div class="grid grid-cols-1 lg:grid-cols-2">

                <!-- Left: Live Lifecycle Status (1/2 width on lg) -->
                <div class="border-b border-slate-150 dark:border-navy-600 lg:border-b-0 lg:border-r">
                    <div class="flex h-full flex-col pb-4">
                        <!-- Timeline Header -->
                        <div class="flex items-center justify-between px-4 pt-5 pb-2 sm:px-5">
                            <div
                                class="flex items-center space-x-2.5 border-b border-slate-150 pb-4 dark:border-navy-600">
                                <div
                                    class="rounded-lg size-10 bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                            <path
                                                d="M16.7502 3.56V2C16.7502 1.59 16.4102 1.25 16.0002 1.25C15.5902 1.25 15.2502 1.59 15.2502 2V3.5H8.75023V2C8.75023 1.59 8.41023 1.25 8.00023 1.25C7.59023 1.25 7.25023 1.59 7.25023 2V3.56C4.55023 3.81 3.24023 5.42 3.04023 7.81C3.02023 8.1 3.26023 8.34 3.54023 8.34H20.4602C20.7502 8.34 20.9902 8.09 20.9602 7.81C20.7602 5.42 19.4502 3.81 16.7502 3.56Z"
                                                fill="#4f46e5"></path>
                                            <path opacity="0.4"
                                                d="M21 10.8401V12.5801C21 13.1901 20.46 13.6601 19.86 13.5601C19.58 13.5201 19.29 13.4901 19 13.4901C15.97 13.4901 13.5 15.9601 13.5 18.9901C13.5 19.4501 13.68 20.0901 13.87 20.6701C14.09 21.3201 13.61 21.9901 12.92 21.9901H8C4.5 21.9901 3 19.9901 3 16.9901V10.8301C3 10.2801 3.45 9.83008 4 9.83008H20C20.55 9.84008 21 10.2901 21 10.8401Z"
                                                fill="#4f46e5"></path>
                                            <path
                                                d="M19 15C16.79 15 15 16.79 15 19C15 19.75 15.21 20.46 15.58 21.06C16.27 22.22 17.54 23 19 23C20.46 23 21.73 22.22 22.42 21.06C22.79 20.46 23 19.75 23 19C23 16.79 21.21 15 19 15ZM21.07 18.57L18.94 20.54C18.8 20.67 18.61 20.74 18.43 20.74C18.24 20.74 18.05 20.67 17.9 20.52L16.91 19.53C16.62 19.24 16.62 18.76 16.91 18.47C17.2 18.18 17.68 18.18 17.97 18.47L18.45 18.95L20.05 17.47C20.35 17.19 20.83 17.21 21.11 17.51C21.39 17.81 21.37 18.28 21.07 18.57Z"
                                                fill="#4f46e5"></path>
                                            <path
                                                d="M8.5 14.9999C8.24 14.9999 7.98 14.8899 7.79 14.7099C7.61 14.5199 7.5 14.2599 7.5 13.9999C7.5 13.7399 7.61 13.4799 7.79 13.2899C8.02 13.0599 8.37 12.9499 8.7 13.0199C8.76 13.0299 8.82 13.0499 8.88 13.0799C8.94 13.0999 9 13.1299 9.06 13.1699C9.11 13.2099 9.16 13.2499 9.21 13.2899C9.39 13.4799 9.5 13.7399 9.5 13.9999C9.5 14.2599 9.39 14.5199 9.21 14.7099C9.16 14.7499 9.11 14.7899 9.06 14.8299C9 14.8699 8.94 14.8999 8.88 14.9199C8.82 14.9499 8.76 14.9699 8.7 14.9799C8.63 14.9899 8.56 14.9999 8.5 14.9999Z"
                                                fill="#4f46e5"></path>
                                            <path
                                                d="M12 14.9999C11.74 14.9999 11.48 14.8899 11.29 14.7099C11.11 14.5199 11 14.2599 11 13.9999C11 13.7399 11.11 13.48 11.29 13.29C11.67 12.92 12.34 12.92 12.71 13.29C12.89 13.48 13 13.7399 13 13.9999C13 14.2599 12.89 14.5199 12.71 14.7099C12.52 14.8899 12.26 14.9999 12 14.9999Z"
                                                fill="#4f46e5"></path>
                                            <path
                                                d="M8.5 18.4999C8.24 18.4999 7.98 18.3899 7.79 18.2099C7.61 18.0199 7.5 17.7599 7.5 17.4999C7.5 17.2399 7.61 16.9799 7.79 16.7899C7.89 16.6999 7.99 16.6299 8.12 16.5799C8.49 16.4199 8.93 16.5099 9.21 16.7899C9.39 16.9799 9.5 17.2399 9.5 17.4999C9.5 17.7599 9.39 18.0199 9.21 18.2099C9.02 18.3899 8.76 18.4999 8.5 18.4999Z"
                                                fill="#4f46e5"></path>
                                        </g>
                                    </svg>
                                </div>
                                <h2 class="text-base font-bold text-slate-900 dark:text-navy-50">Lifecycle Status
                                    History
                                </h2>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="inline-flex">
                                    <button
                                        class="btn -mr-1.5 size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                                        <MoreHorizontal class="size-5" />
                                    </button>
                                </div>
                            </div>
                        </div>


                        <!-- Vertical timeline -->
                        <ol class="timeline line-space flex-1 px-4 [--size:1.5rem] sm:px-5">
                            <li v-for="(stage, i) in stages" :key="stage.key" class="timeline-item">
                                <!-- Point -->
                                <div class="relative">
                                    <div class="timeline-item-point flex items-center justify-center rounded-full border"
                                        :class="stageStates[i] === 'future'
                                            ? 'border-slate-300 bg-white text-slate-300 dark:border-navy-600 dark:bg-navy-700 dark:text-navy-600'
                                            : tonePoint[stage.tone]">
                                        <component :is="stage.icon" class="size-3" />
                                    </div>

                                    <!-- Corner ping indicator on current stage -->
                                    <span v-if="stageStates[i] === 'current'"
                                        class="absolute -top-px -right-px flex size-3 items-center justify-center">
                                        <span
                                            class="absolute inline-flex h-full w-full animate-ping rounded-full bg-secondary opacity-80"></span>
                                        <span class="inline-flex size-2 rounded-full bg-secondary"></span>
                                    </span>
                                </div>

                                <!-- Content -->
                                <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                    <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                        <p
                                            class="flex items-center gap-2 pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0">
                                            {{ stage.label }}

                                            <span v-if="stageStates[i] === 'current'"
                                                class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15">
                                                Current
                                            </span>
                                            <span v-else-if="stageStates[i] === 'done'"
                                                class="badge space-x-2.5 rounded-full bg-success/10 text-success dark:bg-success/15">
                                                Done
                                            </span>
                                            <span v-else-if="stageStates[i] === 'cancelled'"
                                                class="badge space-x-2.5 rounded-full bg-error/10 text-error dark:bg-error/15">
                                                Cancelled here
                                            </span>
                                            <span v-else
                                                class="badge space-x-2.5 rounded-full bg-slate-100 text-slate-400 dark:bg-warning/15">
                                                Pending
                                            </span>
                                        </p>

                                        <span v-if="stage.timestamp" class="text-xs text-slate-400 dark:text-navy-300">
                                            {{ stage.timestamp }}
                                        </span>
                                    </div>

                                    <p class="py-1"
                                        :class="stageStates[i] === 'future' ? 'text-slate-400 dark:text-navy-500' : 'text-slate-500 dark:text-navy-200'">
                                        {{ stage.description }}
                                    </p>

                                    <div v-if="stage.meta" class="pt-1">
                                        <span
                                            class="badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                            {{ stage.meta }}
                                        </span>
                                    </div>
                                </div>
                            </li>

                            <!-- Terminal: Cancelled -->
                            <li v-if="showCancelNode" class="timeline-item">
                                <div
                                    class="timeline-item-point flex items-center justify-center rounded-full border border-current bg-white text-error dark:bg-navy-700 dark:text-error-light">
                                    <X class="size-3" />
                                </div>
                                <div class="timeline-item-content flex-1 pl-4 sm:pl-8">
                                    <div class="flex flex-col justify-between pb-2 sm:flex-row sm:pb-0">
                                        <p
                                            class="flex items-center gap-2 pb-2 font-medium leading-none text-slate-600 dark:text-navy-100 sm:pb-0">
                                            Order Cancelled
                                            <span
                                                class="inline-flex items-center rounded-full bg-error/10 px-2 py-0.5 text-2xs font-bold uppercase tracking-wide text-error dark:bg-error/15">
                                                Terminal
                                            </span>
                                        </p>
                                    </div>
                                    <p class="py-1 text-slate-500 dark:text-navy-200">
                                        This order was cancelled before completion. If payment was captured, a refund
                                        will be processed automatically.
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
                        <div class="flex items-center space-x-2.5 border-b border-slate-150 pb-4 dark:border-navy-600">
                            <div
                                class="rounded-lg size-10 bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                        <path
                                            d="M22 6.00008V8.42008C22 10.0001 21 11.0001 19.42 11.0001H16V4.01008C16 2.90008 16.91 1.99008 18.02 2.00008C19.11 2.01008 20.11 2.45008 20.83 3.17008C21.55 3.90008 22 4.90008 22 6.00008Z"
                                            fill="#4f46e5"></path>
                                        <path opacity="0.4"
                                            d="M2 7V21C2 21.83 2.94 22.3 3.6 21.8L5.31 20.52C5.71 20.22 6.27 20.26 6.63 20.62L8.29 22.29C8.68 22.68 9.32 22.68 9.71 22.29L11.39 20.61C11.74 20.26 12.3 20.22 12.69 20.52L14.4 21.8C15.06 22.29 16 21.82 16 21V4C16 2.9 16.9 2 18 2H7H6C3 2 2 3.79 2 6V7Z"
                                            fill="#4f46e5"></path>
                                        <path
                                            d="M12 12.2598H9C8.59 12.2598 8.25 12.5998 8.25 13.0098C8.25 13.4198 8.59 13.7598 9 13.7598H12C12.41 13.7598 12.75 13.4198 12.75 13.0098C12.75 12.5998 12.41 12.2598 12 12.2598Z"
                                            fill="#4f46e5"></path>
                                        <path
                                            d="M9 9.75977H12C12.41 9.75977 12.75 9.41977 12.75 9.00977C12.75 8.59977 12.41 8.25977 12 8.25977H9C8.59 8.25977 8.25 8.59977 8.25 9.00977C8.25 9.41977 8.59 9.75977 9 9.75977Z"
                                            fill="#4f46e5"></path>
                                        <path
                                            d="M5.9707 8.00977C5.4107 8.00977 4.9707 8.45977 4.9707 9.00977C4.9707 9.55977 5.4207 10.0098 5.9707 10.0098C6.5207 10.0098 6.9707 9.55977 6.9707 9.00977C6.9707 8.45977 6.5207 8.00977 5.9707 8.00977Z"
                                            fill="#4f46e5"></path>
                                        <path
                                            d="M5.9707 12.0098C5.4107 12.0098 4.9707 12.4598 4.9707 13.0098C4.9707 13.5598 5.4207 14.0098 5.9707 14.0098C6.5207 14.0098 6.9707 13.5598 6.9707 13.0098C6.9707 12.4598 6.5207 12.0098 5.9707 12.0098Z"
                                            fill="#4f46e5"></path>
                                    </g>
                                </svg>
                            </div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-navy-50">Invoice Summary
                            </h2>
                        </div>
                        <table
                            class="mt-3 w-full divide-y divide-slate-150 text-sm text-slate-600 dark:divide-navy-600 dark:text-navy-200">
                            <tbody>
                                <tr>
                                    <td class="py-3 text-slate-400 dark:text-navy-300">Subtotal</td>
                                    <td class="py-3 text-right  font-semibold text-slate-800 dark:text-navy-50"> {{
                                        formatCurrency(order.subtotal) }}</td>
                                </tr>
                                <tr v-if="Number(order.discount_total) > 0">
                                    <td class="py-3 font-medium text-success">
                                        Discount
                                        <span class="rounded bg-success/10 px-1 py-0.5  text-2xs">({{ order.coupon_code
                                        }})</span>
                                    </td>
                                    <td class="py-3 text-right  text-success">− {{ formatCurrency(order.discount_total)
                                        }}</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-slate-400 dark:text-navy-300">Shipping Fee</td>
                                    <td class="py-3 text-right  font-semibold text-slate-800 dark:text-navy-50"> {{
                                        formatCurrency(order.shipping_fee) }}</td>
                                </tr>
                                <tr
                                    class="border-t-2 border-slate-200 text-base font-black text-slate-900 dark:border-navy-600 dark:text-navy-50">
                                    <td class="py-4">Grand Total</td>
                                    <td class="py-4 text-right  text-lg text-primary dark:text-accent-light"> {{
                                        formatCurrency(order.grand_total) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Right Bottom: Delivery Address -->
                    <div class="flex-1 p-6">
                        <div class="flex items-center space-x-2.5 border-b border-slate-150 pb-4 dark:border-navy-600">
                            <div
                                class="rounded-lg size-10 bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                    <g id="SVGRepo_iconCarrier">
                                        <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                        <path
                                            d="M9.96055 14.0401V12.9301C9.96055 12.5001 9.64055 12.2901 9.25055 12.4601L4.24055 14.6201C3.45055 14.9601 2.81055 14.5401 2.81055 13.6901V12.3701C2.81055 11.6901 3.32055 10.9001 3.95055 10.6401L9.65055 8.19006C9.81055 8.11006 9.95055 7.90006 9.95055 7.72006V4.66006C9.95055 3.70006 10.6605 2.55006 11.5105 2.11006C11.8105 1.96006 12.1705 1.96006 12.4705 2.11006C13.3305 2.55006 14.0405 3.69006 14.0405 4.65006V7.71006C14.0405 7.89006 14.1805 8.10006 14.3405 8.18006L15.3805 8.62006L9.96055 14.0401Z"
                                            fill="#4f46e5"></path>
                                        <path opacity="0.4"
                                            d="M21.1892 12.3701V13.6901C21.1892 14.5401 20.5492 14.9601 19.7592 14.6201L14.7492 12.4601C14.3492 12.3001 14.0392 12.5001 14.0392 12.9301V15.8401C14.0392 16.0701 14.1692 16.4001 14.3392 16.5601L16.6592 18.8901C16.8992 19.1301 17.0092 19.6101 16.8992 19.9401L16.4492 21.3001C16.2492 21.8901 15.5792 22.1601 15.0292 21.8901L12.6492 19.8901C12.2892 19.5801 11.6992 19.5801 11.3392 19.8901L8.95918 21.8901C8.40918 22.1701 7.73918 21.8901 7.55918 21.3001L7.10918 19.9401C6.99918 19.6001 7.10918 19.1301 7.34918 18.8901L9.66918 16.5601C9.82918 16.4001 9.95918 16.0701 9.95918 15.8401V14.0401L15.3792 8.62012L20.0492 10.6301C20.6792 10.9001 21.1892 11.6901 21.1892 12.3701Z"
                                            fill="#4f46e5"></path>
                                    </g>
                                </svg>
                            </div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-navy-50">Delivery Address Details
                            </h2>
                        </div>

                        <table
                            class="mt-3 w-full divide-y divide-slate-150 text-sm text-slate-600 dark:divide-navy-600 dark:text-navy-200">
                            <tbody>
                                <tr>
                                    <td class="py-3 font-medium text-slate-400 dark:text-navy-300">Owner</td>
                                    <td class="py-3 text-right font-bold text-slate-900 dark:text-navy-50">
                                        {{ order.user.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-medium text-slate-400 dark:text-navy-300">Destination</td>
                                    <td class="py-3 text-right font-bold text-slate-900 dark:text-navy-50">
                                        {{ order.shipping_address.county }} — {{ order.shipping_address.town }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3 font-medium text-slate-400 dark:text-navy-300">Street Address</td>
                                    <td class="py-3 text-right text-slate-700 dark:text-navy-100">{{
                                        order.shipping_address.address }}</td>
                                </tr>
                                <tr v-if="order.shipping_address.instructions">
                                    <td class="py-3 font-medium text-slate-400 dark:text-navy-300">Instructions</td>
                                    <td class="py-3 text-right text-xs italic text-slate-500 dark:text-navy-300">"{{
                                        order.shipping_address.instructions }}"</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= Review Modal ================= -->
        <div v-if="reviewItem"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs transition-opacity"
            @click.self="closeReview">
            <div
                class="card w-full max-w-lg animate-in rounded-2xl border border-slate-200 p-6 shadow-2xl fade-in zoom-in-95 duration-200 dark:border-navy-600 dark:bg-navy-750">
                <div class="flex items-center justify-between border-b border-slate-150 pb-4 dark:border-navy-600">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-navy-50">
                        <Star class="size-5 text-warning" />
                        Rate &amp; Review Product
                    </h2>
                    <button type="button"
                        class="flex size-8 items-center justify-center rounded-full text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-navy-700 dark:hover:text-navy-100"
                        @click="closeReview">
                        <X class="size-4" />
                    </button>
                </div>

                <div
                    class="mt-4 rounded-xl border border-slate-150 bg-slate-50 p-3 dark:border-navy-600 dark:bg-navy-900">
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
                                <Star class="size-8"
                                    :class="star <= reviewForm.rating ? 'fill-warning text-warning drop-shadow-xs' : 'text-slate-300 dark:text-navy-500'" />
                            </button>
                        </div>
                        <p v-if="reviewForm.errors.rating" class="mt-1 text-xs font-medium text-error">{{
                            reviewForm.errors.rating }}</p>
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