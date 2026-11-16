<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

const props = defineProps<PageProps & {
    order: {
        ulid: string;
        order_number: string;
        grand_total: string;
        payment_status: string;
        customer_name: string;
        customer_phone: string;
        items: { product_name: string; sku: string; quantity: number; line_total: string }[];
    };
}>();

function badgeClass(status: string): string {
    switch (status) {
        case 'paid':
        case 'completed':
            return 'bg-success/10 text-success dark:bg-success/20';
        case 'pending':
            return 'bg-warning/10 text-warning dark:bg-warning/20';
        case 'failed':
        case 'cancelled':
            return 'bg-error/10 text-error dark:bg-error/20';
        default:
            return 'bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200';
    }
}
</script>

<template>
    <Head title="Order Received" />

    <StorefrontLayout>
        <div class="mx-auto max-w-2xl px-4 py-12 text-center">
            <!-- Success Icon Header -->
            <div class="inline-flex size-20 items-center justify-center rounded-3xl bg-success/10 text-success dark:bg-success/20 mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">Order Received Successfully!</h1>
            <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">
                Order Reference: <strong class="text-primary dark:text-accent-light font-mono">{{ order.order_number }}</strong>
            </p>

            <!-- Details Card -->
            <div class="card mt-6 p-6 text-left dark:bg-navy-750 border border-slate-150 dark:border-navy-600 shadow-lg space-y-5">
                <!-- M-Pesa Prompt Notice -->
                <div class="rounded-lg bg-info/10 p-4 text-info dark:bg-info/20 flex items-start space-x-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <div class="text-xs+ space-y-1">
                        <p class="font-semibold">M-Pesa STK Push Sent</p>
                        <p>
                            We have sent a payment prompt to <strong class="font-mono">{{ order.customer_phone }}</strong>. Please enter your M-Pesa PIN to complete the transaction.
                        </p>
                    </div>
                </div>

                <!-- Status Row -->
                <div class="flex items-center justify-between text-sm py-2 border-b border-slate-150 dark:border-navy-600">
                    <span class="text-slate-400 dark:text-navy-300">Payment Status:</span>
                    <span class="badge px-3 py-1 rounded-full font-semibold capitalize text-xs" :class="badgeClass(order.payment_status)">
                        {{ order.payment_status }}
                    </span>
                </div>

                <!-- Items Breakdown -->
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300 mb-3">Order Items</h3>
                    <ul class="divide-y divide-slate-150 dark:divide-navy-600 text-sm space-y-2.5">
                        <li v-for="(item, i) in order.items" :key="i" class="flex justify-between items-center pt-2.5 first:pt-0">
                            <span class="text-slate-700 dark:text-navy-100 font-medium">
                                {{ item.product_name }} <span class="text-slate-400 font-normal">× {{ item.quantity }}</span>
                            </span>
                            <span class="font-semibold text-slate-800 dark:text-navy-50">KES {{ item.line_total }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Grand Total -->
                <div class="flex justify-between items-center border-t border-slate-150 pt-4 font-bold text-slate-800 dark:border-navy-600 dark:text-navy-50 text-base">
                    <span>Grand Total</span>
                    <span class="text-primary dark:text-accent-light">KES {{ order.grand_total }}</span>
                </div>

                <!-- Note -->
                <p class="text-xs text-slate-400 dark:text-navy-300 italic pt-1 text-center">
                    You will receive confirmation via SMS or email once the M-Pesa callback is confirmed.
                </p>
            </div>

            <!-- Action Button -->
            <div class="mt-8">
                <Link
                    href="/customer/products"
                    class="btn inline-flex items-center space-x-2 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus px-6 py-3 shadow-lg"
                >
                    <span>Continue shopping</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </Link>
            </div>
        </div>
    </StorefrontLayout>
</template>