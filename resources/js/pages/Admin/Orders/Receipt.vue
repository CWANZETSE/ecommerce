<template>

    <Head :title="`Receipt ${order.order_number}`" />

    <div class="p-4 sm:p-5 lg:p-6">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex items-start gap-3">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <ReceiptSVG />
                </div>

                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-semibold tracking-tight text-slate-800 dark:text-navy-50 lg:text-2xl">
                            Order Receipt
                        </h2>
                    </div>


                </div>
            </div>

            <Link :href="admin_orders_show.url(order.ulid)"
                class="btn border border-primary/30 bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:border-accent-light/30 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                <ArrowLeft class="size-4" />
                <span>Back to order {{ order.order_number }}</span>
            </Link>
        </div>

        <!-- Order Content -->
        <div
    class="mx-auto w-full max-w-full grid border border-white items-start gap-5 bg-white  dark:border-slate-700 dark:bg-navy-800 xl:grid-cols-3 lg:gap-6">
            <!-- Main Content -->
            <div
                class="min-w-0 space-y-5 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800 xl:col-span-3 lg:space-y-6">
                <div
                    class="rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800 xl:w-full">
                    <!-- Receipt Header -->
                    <div class="flex justify-center border-b border-slate-150 px-6 py-4 dark:border-slate-700">
                        <span class="uppercase text-slate-600 dark:text-navy-200">
                            Preview
                        </span>
                    </div>

                    <div class="p-5 xl:p-8">
                        <!-- Store and Customer Details -->
                        <div
                            class="mb-9 flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between rounded-xl bg-primary/10 p-4 dark:bg-navy-700">
                            <!-- Store Details -->
                            <div>
                                <span class="mb-1 block text-sm font-medium text-slate-600 dark:text-navy-300">
                                    From
                                </span>

                                <h5 class="mb-2 text-base font-semibold text-slate-800 dark:text-navy-50">
                                    {{ settings.store_name || 'Store' }}
                                </h5>

                                <div class="mb-4 space-y-1 text-sm text-slate-500 dark:text-navy-300">
                                    <p v-if="settings.store_address">
                                        {{ settings.store_address }}
                                    </p>

                                    <p v-if="settings.store_email">
                                        {{ settings.store_email }}
                                    </p>

                                    <p v-if="settings.store_phone">
                                        {{ settings.store_phone }}
                                    </p>

                                    <p v-if="settings.store_tagline" class="italic">
                                       &ldquo;{{ settings.store_tagline }}&rdquo; 
                                    </p>
                                </div>

                                <span class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-navy-300">
                                    Issued On:
                                </span>

                                <span class="block text-sm text-slate-500 dark:text-navy-300">
                                    {{ order.paid_at }}
                                </span>
                            </div>

                            <!-- Customer Details -->
                            <div class="sm:text-right">
                                <span class="mb-1 block text-sm font-medium text-slate-600 dark:text-navy-300">
                                    To
                                </span>

                                <h5 class="mb-2 text-base font-semibold text-slate-800 dark:text-navy-50">
                                    {{ order.customer_name }}
                                </h5>

                                <div class="mb-4 space-y-1 text-sm text-slate-500 dark:text-navy-300">
                                    <p v-if="order.shipping_address">
                                        {{ order.shipping_address?.county }}
                                        {{ order.shipping_address?.town }}
                                        {{ order.shipping_address?.address }}
                                    </p>

                                    <p v-if="order.customer_email">
                                        {{ order.customer_email }}
                                    </p>

                                    <p v-if="order.customer_phone">
                                        {{ order.customer_phone }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Table Start -->
                        <div
                            class="overflow-hidden rounded-2xl border border-slate-150 bg-white dark:border-slate-700 dark:bg-navy-800">
                            <div class="max-w-full overflow-x-auto">
                                <div class="min-w-[650px]">
                                    <!-- Table Header -->
                                    <div class="grid grid-cols-11 bg-primary/[0.025] px-5 py-3 dark:bg-primary/[0.04]">
                                        <div class="col-span-1 flex items-center">
                                            <p class="text-sm font-medium text-slate-600 dark:text-navy-300">
                                                #
                                            </p>
                                        </div>

                                        <div class="col-span-4 flex items-center">
                                            <p class="text-sm font-medium text-slate-600 dark:text-navy-300">
                                                Product
                                            </p>
                                        </div>

                                        <div class="col-span-2 flex items-center justify-center">
                                            <p class="text-sm font-medium text-slate-600 dark:text-navy-300">
                                                Quantity
                                            </p>
                                        </div>

                                        <div class="col-span-2 flex items-center justify-center">
                                            <p class="text-sm font-medium text-slate-600 dark:text-navy-300">
                                                Unit Cost
                                            </p>
                                        </div>

                                        <div class="col-span-2 flex items-center justify-end">
                                            <p
                                                class="w-full text-right text-sm font-medium text-slate-600 dark:text-navy-300">
                                                Total
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Table Body -->
                                    <div v-for="(item, index) in order.items" :key="item.id"
                                        class="grid grid-cols-11 border-t border-slate-150 px-5 py-3.5 dark:border-slate-700">
                                        <div class="col-span-1 flex items-center">
                                            <p class="text-theme-sm text-slate-500 dark:text-navy-300">
                                                {{ index + 1 }}.
                                            </p>
                                        </div>

                                        <div class="col-span-4 flex flex-col justify-center">
                                            <p class="text-theme-sm font-medium text-slate-700 dark:text-navy-100">
                                                {{ item.product_name }}
                                            </p>

                                            <p v-if="item.sku" class="mt-0.5 text-xs text-slate-400 dark:text-navy-400">
                                                SKU: {{ item.sku }}
                                            </p>

                                            <div v-if="item.attributes?.length" class="mt-2.5 flex flex-wrap gap-1.5">
                                                <span v-for="(a, i) in item.attributes" :key="i"
                                                    class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15">
                                                    <span>{{ a.name }}:</span>
                                                    <span class="font-semibold text-slate-500 dark:text-navy-100">
                                                        {{ a.value }}
                                                    </span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-span-2 flex items-center justify-center">
                                            <span
                                                class="badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                                ×{{ item.quantity }}
                                            </span>
                                        </div>

                                        <div class="col-span-2 flex items-center justify-center">
                                            <p class="text-center text-theme-sm text-slate-500 dark:text-navy-300">
                                                {{ formatMoney(item.unit_price) }}
                                            </p>
                                        </div>

                                        <div class="col-span-2 flex items-center justify-end">
                                            <p
                                                class="text-right text-theme-sm font-medium text-slate-700 dark:text-navy-100">
                                                {{ formatMoney(item.line_total) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Empty State -->
                                    <div v-if="!order.items.length"
                                        class="border-t border-slate-150 px-5 py-8 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300">
                                        No order items available.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Invoice Table End -->

                        <!-- Totals -->
                        <div
                            class="my-6 overflow-hidden rounded-2xl border border-slate-300 bg-primary/10 dark:border-navy-600 dark:bg-navy-800">
                            <!-- Grid container: stacks on mobile, 2 columns on desktop -->
                            <div class="grid grid-cols-1 sm:grid-cols-2">

                                <!-- Left Column: Total -->
                                <div
                                    class="flex items-center gap-4 p-5 border-slate-300 sm:border-b-0 sm:border-r dark:border-navy-600">
                                    <span class="text-lg font-bold text-slate-800 dark:text-navy-50">Total:</span>
                                    <span class="text-2xl font-extrabold text-slate-900 dark:text-white">
                                        {{ formatMoney(order.grand_total) }}
                                    </span>
                                </div>

                                <!-- Right Column: Breakdown -->
                                <div class="flex flex-col justify-center gap-2 p-5">
                                    <div class="flex justify-between text-sm text-slate-600 dark:text-navy-300">
                                        <span>Subtotal:</span>
                                        <span class="font-medium">{{ formatMoney(order.subtotal) }}</span>
                                    </div>

                                    <div class="flex justify-between text-sm text-slate-600 dark:text-navy-300">
                                        <span>Shipping Fee:</span>
                                        <span class="font-medium">{{ formatMoney(order.shipping_fee) }}</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- Payment Details -->
                        <div v-if="order.payments.length" class="mb-6">
                            <h4 class="mb-3 text-sm font-semibold text-slate-800 dark:text-navy-50">
                                Payment Details
                            </h4>

                            <div v-for="payment in order.payments" :key="payment.id"
                                class="mb-2 rounded-lg border border-slate-150 p-3 dark:border-slate-700">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                </div>

                                <div
                                    class="alert overflow-hidden rounded-lg bg-warning/10 text-warning dark:bg-warning/15">
                                    <div class="flex flex-wrap items-center justify-between gap-2 space-x-3 p-4">
                                        <div>
                                            <p v-if="payment.provider_reference"
                                                class="mt-1 text-xs capitalize text-slate-500 dark:text-navy-300">
                                                {{ payment.provider }} Reference: {{ payment.provider_reference }}
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-sm font-semibold text-slate-800 dark:text-navy-50">
                                                {{ formatMoney(payment.amount) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between gap-3 print:hidden">
                            <a
    :href="admin_orders_print_receipt.url(order.ulid)" type="button"
                                class="btn border gap-2 border-primary/30 bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:border-accent-light/30 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                                <PrintSVG />
                                Print Receipt
                        </a>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_orders_index,
    admin_orders_notes_store,
    admin_orders_cancel,
    admin_orders_fulfilment,
    admin_orders_show_receipt,
    admin_orders_show,
    admin_orders_print_receipt
} from '@/routes';

import MenuSVG from '@/components/SVG/menuSVG.vue';
import { ArrowLeft } from '@lucide/vue';
import PrintSVG from '@/components/SVG/printSVG.vue';
import SendSVG from '@/components/SVG/sendSVG.vue';
import ReceiptSVG from '@/components/SVG/receiptSVG.vue';

interface OrderItem {
    id: string;
    product_name: string;
    sku: string;
    attributes: { name: string; value: string }[];
    unit_price: string;
    quantity: number;
    line_total: string;
}
interface Payment {
    id: string;
    ulid: string;
    provider: string;
    amount: string;
    status: string;
    provider_reference: string | null;
    checkout_request_id: string | null;
    failure_reason: string | null;
    paid_at: string | null;
    created_at: string;
}

interface Order {
    id: string;
    ulid: string;
    order_number: string;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    shipping_address: { county: string; town: string; address: string; instructions?: string | null };
    subtotal: string;
    shipping_fee: string;
    grand_total: string;
    payment_status: string;
    fulfilment_status: string;
    paid_at: string | null;
    cancelled_at: string | null;
    created_at: string;
    items: OrderItem[];
    payments: Payment[];
    history: History[];
    discount_total: string;
    coupon_code: string | null;
}

const props = defineProps<PageProps & {
    order: Order;
    settings: Record<string, string | null>;
}>();

const formatMoney = (amount: number | string | null) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: props.settings.currency || 'KES',
    }).format(Number(amount ?? 0));
};
</script>