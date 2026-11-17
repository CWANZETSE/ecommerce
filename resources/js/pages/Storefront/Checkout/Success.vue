<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

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
    <StorefrontLayout>
        <Head title="Order Received" />

        <div class="mx-auto max-w-2xl px-4 py-12 text-center">
            <!-- Success Icon Header -->
            <div class="inline-flex size-20 items-center justify-center rounded-3xl bg-success/10 text-success dark:bg-success/20 mb-5 shadow-inner">
                <CheckCircle2 class="size-10" />
            </div>

            <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">Order Received Successfully!</h1>
            <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">
                Order Reference: <strong class="text-primary dark:text-accent-light font-mono">{{ order.order_number }}</strong>
            </p>

            <!-- Details Card -->
            <div class="card mt-6 p-6 text-left dark:bg-navy-750 border border-slate-150 dark:border-navy-600 shadow-xl space-y-6 rounded-2xl">
                <!-- M-Pesa Prompt Notice -->
                <div class="rounded-xl bg-info/10 p-4 text-info dark:bg-info/20 flex items-start space-x-3.5 border border-info/20">
                    <Smartphone class="size-5 shrink-0 mt-0.5 text-info" />
                    <div class="text-xs+ space-y-1">
                        <p class="font-semibold text-slate-800 dark:text-navy-50">M-Pesa STK Push Sent</p>
                        <p class="text-slate-600 dark:text-navy-200 leading-relaxed">
                            We have sent an instant payment prompt to <strong class="font-mono text-primary dark:text-accent-light">{{ order.customer_phone }}</strong>. Please check your phone and enter your M-Pesa PIN to complete the transaction.
                        </p>
                    </div>
                </div>

                <!-- Status Row -->
                <div class="flex items-center justify-between text-sm py-2 border-b border-slate-150 dark:border-navy-600">
                    <span class="text-slate-400 dark:text-navy-300 font-medium">Payment Status:</span>
                    <span class="badge px-3 py-1 rounded-full font-semibold capitalize text-xs shadow-xs" :class="badgeClass">
                        {{ order.payment_status }}
                    </span>
                </div>

                <!-- Items Breakdown -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <PackageCheck class="size-4 text-slate-400" />
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">Order Items</h3>
                    </div>
                    <ul class="divide-y divide-slate-150 dark:divide-navy-600 text-sm space-y-2.5">
                        <li v-for="(item, i) in order.items" :key="i" class="flex justify-between items-center pt-2.5 first:pt-0">
                            <span class="text-slate-700 dark:text-navy-100 font-medium flex items-center gap-2">
                                <span class="size-1.5 rounded-full bg-primary dark:bg-accent"></span>
                                {{ item.product_name }} <span class="text-slate-400 dark:text-navy-300 font-normal">× {{ item.quantity }}</span>
                            </span>
                            <span class="font-semibold font-mono text-slate-800 dark:text-navy-50">KES {{ item.line_total }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Grand Total -->
                <div class="flex justify-between items-center border-t border-slate-150 pt-4 font-bold text-slate-800 dark:border-navy-600 dark:text-navy-50 text-base">
                    <span>Grand Total</span>
                    <span class="text-primary dark:text-accent-light font-mono text-lg">KES {{ order.grand_total }}</span>
                </div>

                <!-- Note -->
                <p class="text-xs text-slate-400 dark:text-navy-300 italic pt-2 text-center border-t border-slate-150/60 dark:border-navy-600/60">
                    You will receive confirmation via SMS or email once the M-Pesa payment is confirmed.
                </p>
            </div>

            <!-- Action Button -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <Link
                    href="/customer/products"
                    class="btn inline-flex items-center space-x-2 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus px-6 py-3 rounded-xl shadow-lg transition-all duration-200"
                >
                    <ShoppingBag class="size-4" />
                    <span>Continue shopping</span>
                    <ArrowRight class="size-4" />
                </Link>
                <Link
                    href="/customer/orders"
                    class="btn inline-flex items-center space-x-2 bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 dark:bg-navy-700 dark:text-navy-100 dark:hover:bg-navy-600 px-6 py-3 rounded-xl transition-all duration-200"
                >
                    <span>View all orders</span>
                </Link>
            </div>
        </div>
    </StorefrontLayout>
</template>