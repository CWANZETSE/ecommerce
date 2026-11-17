<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

interface OrderRow {
    ulid: string;
    order_number: string;
    grand_total: string;
    payment_status: string;
    fulfilment_status: string;
    created_at: string;
    items_count: number;
}

const props = defineProps<PageProps & {
    orders: {
        data: OrderRow[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();

function badgeClass(status: string): string {
    switch (status) {
        case 'paid':
        case 'delivered':
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
    <Head title="My Orders" />

    <div class="mx-auto max-w-4xl px-4 py-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-150 pb-6 dark:border-navy-600">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">My Orders</h1>
                <p class="text-sm text-slate-400 dark:text-navy-300 mt-1">Review your order history, delivery status, and invoice totals.</p>
            </div>
        </div>

        <!-- Empty Orders State -->
        <div v-if="orders.data.length === 0" class="card mt-8 px-6 py-16 text-center dark:bg-navy-750 border border-slate-150 dark:border-navy-600 shadow-xl rounded-2xl">
            <div class="inline-flex size-20 items-center justify-center rounded-3xl bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 mb-4 shadow-inner">
                <PackageOpen class="size-10" />
            </div>
            <p class="text-base font-semibold text-slate-700 dark:text-navy-100">You have no orders yet.</p>
            <p class="text-sm mt-1 text-slate-400 dark:text-navy-300">Explore our catalog and place your first order.</p>
            <Link
                href="/customer/products"
                class="btn mt-6 inline-flex items-center space-x-2 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus px-6 py-3 rounded-xl shadow-lg transition-all duration-200"
            >
                <ShoppingBag class="size-4" />
                <span>Start shopping</span>
                <ArrowRight class="size-4" />
            </Link>
        </div>

        <!-- Orders List Section -->
        <div v-else class="mt-6 space-y-4">
            <Link
                v-for="order in orders.data"
                :key="order.ulid"
                :href="`/customer/orders/${order.ulid}`"
                class="card block p-5 transition-all duration-300 hover:shadow-xl dark:bg-navy-750 border border-slate-150 dark:border-navy-600 group rounded-2xl"
            >
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="flex items-center space-x-2.5">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                                <Package class="size-4" />
                            </span>
                            <span class="font-mono text-sm font-bold text-primary dark:text-accent-light group-hover:underline">
                                {{ order.order_number }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 dark:text-navy-300 flex items-center gap-2 pl-0.5">
                            <Clock class="size-3.5" />
                            {{ order.created_at }} <span class="mx-0.5">•</span> {{ order.items_count }} item(s)
                        </p>
                    </div>
                    
                    <div class="flex items-center justify-between sm:justify-end gap-6 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-150 dark:border-navy-600">
                        <div class="text-left sm:text-right">
                            <span class="text-xs text-slate-400 dark:text-navy-300 block">Total Amount</span>
                            <span class="text-base font-bold font-mono text-slate-800 dark:text-navy-50">KES {{ order.grand_total }}</span>
                        </div>
                        
                        <div class="flex flex-col items-end gap-1.5">
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="badge px-3 py-1 rounded-full font-semibold capitalize shadow-xs" :class="badgeClass(order.payment_status)">
                                    {{ order.payment_status }}
                                </span>
                                <span class="badge px-3 py-1 rounded-full font-semibold capitalize shadow-xs" :class="badgeClass(order.fulfilment_status)">
                                    {{ order.fulfilment_status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Pagination Links -->
        <nav v-if="orders.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1.5">
            <template v-for="link in orders.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="btn min-w-9 h-9 px-3 rounded-xl border font-medium text-xs transition-colors shadow-xs"
                    :class="link.active 
                        ? 'bg-primary text-white border-primary dark:bg-accent dark:border-accent' 
                        : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-150 dark:border-navy-500 dark:bg-navy-750 dark:text-navy-100 dark:hover:bg-navy-700'"
                    v-html="link.label"
                />
                <span 
                    v-else 
                    class="btn min-w-9 h-9 px-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 text-xs dark:border-navy-600 dark:bg-navy-800 dark:text-navy-400 opacity-50 cursor-not-allowed shadow-xs" 
                    v-html="link.label" 
                />
            </template>
        </nav>
    </div>
</template>