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
            return 'badge space-x-2.5 rounded-full bg-success/10 text-success dark:bg-success/15';
        case 'pending':
            return 'badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15';
        case 'processing':
            return 'badge space-x-2.5 rounded-full bg-info/10 text-info dark:bg-info/15';
        case 'failed':
        case 'cancelled':
            return 'badge space-x-2.5 rounded-full bg-error/10 text-error dark:bg-error/15';
        default:
            return 'badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15';
    }
}
const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount);
</script>

<template>

    <Head title="My Orders" />

    <div class="mx-auto max-w-4xl px-4 py-8">
        <!-- Header Section -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-150 pb-6 dark:border-navy-600">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">My Orders</h1>
                <p class="text-sm text-slate-400 dark:text-navy-300 mt-1">Review your order history, delivery status,
                    and invoice totals.</p>
            </div>
        </div>

        <!-- Empty Orders State -->
        <div v-if="orders.data.length === 0"
            class="card mt-8 px-6 py-16 text-center dark:bg-navy-750 border border-slate-150 dark:border-navy-600 shadow-xl rounded-2xl">
            <div
                class="inline-flex size-20 items-center justify-center rounded-3xl bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 mb-4 shadow-inner">
                <PackageOpen class="size-10" />
            </div>
            <p class="text-base font-semibold text-slate-700 dark:text-navy-100">You have no orders yet.</p>
            <p class="text-sm mt-1 text-slate-400 dark:text-navy-300">Explore our catalog and place your first order.
            </p>
            <Link href="/customer/products"
                class="btn mt-6 inline-flex items-center space-x-2 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus px-6 py-3 rounded-xl shadow-lg transition-all duration-200">
                <ShoppingBag class="size-4" />
                <span>Start shopping</span>
                <ArrowRight class="size-4" />
            </Link>
        </div>

        <!-- Orders List Section -->
        <div v-else class="mt-6 space-y-4">
            <Link v-for="order in orders.data" :key="order.ulid" :href="`/customer/orders/${order.ulid}`"
                class="card block p-5 transition-all duration-300 hover:shadow-xl dark:bg-navy-750 border border-slate-150 dark:border-navy-600 group rounded-2xl">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="flex items-center space-x-2.5">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="rounded-lg size-10 bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
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
                                <span class=" font-bold text-primary dark:text-accent-light group-hover:underline">
                                    {{ order.order_number }}
                                </span>
                            </div>

                        </div>
                        <p class="text-xs text-slate-400 dark:text-navy-300 flex items-center gap-2 pl-0.5">
                            <Clock class="size-3.5" />
                            {{ order.created_at }} <span class="mx-0.5">•</span> {{ order.items_count }} item(s)
                        </p>
                    </div>

                    <div
                        class="flex items-center justify-between sm:justify-end gap-6 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-150 dark:border-navy-600">
                        <div class="text-left sm:text-right">
                            <span class="text-xs text-slate-400 dark:text-navy-300 block">Total Amount</span>
                            <span class="text-base font-bold  text-slate-800 dark:text-navy-50">{{
                                formatCurrency(order.grand_total) }}</span>
                        </div>

                        <div class="flex flex-col items-end gap-1.5">
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="badge px-3 py-1 rounded-full font-semibold capitalize shadow-xs"
                                    :class="badgeClass(order.payment_status)">
                                    {{ order.payment_status }}
                                </span>
                                <span
                                    class="badge relative inline-flex items-center gap-2 rounded-full px-3 py-1 font-semibold capitalize shadow-xs"
                                    :class="badgeClass(order.fulfilment_status)">
                                    {{ order.fulfilment_status }}

                                    <span class="relative flex size-2 shrink-0">
                                        <span
                                            class="absolute inline-flex size-full animate-ping rounded-full bg-warning opacity-75"></span>
                                        <span class="relative inline-flex size-2 rounded-full bg-warning"></span>
                                    </span>
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
                <Link v-if="link.url" :href="link.url"
                    class="btn min-w-9 h-9 px-3 rounded-xl border font-medium text-xs transition-colors shadow-xs"
                    :class="link.active
                        ? 'bg-primary text-white border-primary dark:bg-accent dark:border-accent'
                        : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-150 dark:border-navy-500 dark:bg-navy-750 dark:text-navy-100 dark:hover:bg-navy-700'"
                    v-html="link.label" />
                <span v-else
                    class="btn min-w-9 h-9 px-3 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 text-xs dark:border-navy-600 dark:bg-navy-800 dark:text-navy-400 opacity-50 cursor-not-allowed shadow-xs"
                    v-html="link.label" />
            </template>
        </nav>
    </div>
</template>