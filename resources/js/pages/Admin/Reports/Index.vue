<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { PageProps } from '@/types';

interface Props extends PageProps {
    summary: {
        gross_sales: string;
        shipping: string;
        discounts: string;
        net_revenue: string;
        orders_paid: number;
        orders_pending: number;
        orders_cancelled: number;
    };
    daily: { date: string; revenue: string; orders: number }[];
    filters: { from: string; to: string };
    topProducts: { product_name: string; sku: string; units_sold: number; revenue: string }[];
    topCategories: { category: string; units_sold: number; revenue: string }[];
    topCustomers: { customer_email: string; customer_name: string; orders: number; total_spent: string }[];
    paymentMethods: { provider: string; count: number; total: string }[];
    couponUsage: { code: string; type: string; redemptions: number; total_discount: string }[];
    inventory: { total_variations: number; out_of_stock: number; low_stock: number; stock_value: number };
}

const props = defineProps<Props>();

const from = ref(props.filters.from);
const to = ref(props.filters.to);

function applyFilters() {
    router.get('/admin/reports', { from: from.value, to: to.value }, { preserveState: true, replace: true });
}

function exportSales() {
    window.location.href = `/admin/reports/export/sales?from=${from.value}&to=${to.value}`;
}

function exportInventory() {
    window.location.href = '/admin/reports/export/inventory';
}
</script>

<template>

    <Head title="Reports" />

    <div class="p-4 sm:p-5 lg:p-6">
        <!-- Header & Filters -->
        <div class=" gap-4 py-5 lg:flex-row lg:items-center lg:justify-between lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                Reports
            </h2>

            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-navy-300">From</span>
                    <input v-model="from" type="date"
                        class="form-input mt-1 rounded-lg border border-slate-300 bg-transparent px-2.5 py-1.5 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                </div>
                <div>
                    <span class="text-xs font-medium text-slate-500 dark:text-navy-300">To</span>
                    <input v-model="to" type="date"
                        class="form-input mt-1 rounded-lg border border-slate-300 bg-transparent px-2.5 py-1.5 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                </div>
                <button type="button"
                    class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                    @click="applyFilters">
                    Apply
                </button>
                <button type="button"
                    class="btn bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450"
                    @click="exportSales">
                    Export sales CSV
                </button>
                <button type="button"
                    class="btn bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450"
                    @click="exportInventory">
                    Export inventory CSV
                </button>
            </div>
        </div>

        
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="mt-4 grid grid-cols-1 gap-3 sm:mt-5 sm:grid-cols-2 sm:gap-5 lg:mt-6 xl:grid-cols-4">
        <!-- Gross Sales -->
        <div class="rounded-2xl border border-slate-150 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-navy-800 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">
                        Gross Sales
                    </p>
                    <p class="mt-2 text-xl font-bold tracking-tight text-slate-700 dark:text-navy-100 sm:text-2xl">
                        KES {{ props.summary.gross_sales }}
                    </p>
                </div>
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-success/10 text-success dark:bg-success/15">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v20" />
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-navy-700">
                <div class="h-full w-full rounded-full bg-success/60"></div>
            </div>
        </div>

        <!-- Discounts -->
        <div class="rounded-2xl border border-slate-150 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-navy-800 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">
                        Discounts
                    </p>
                    <p class="mt-2 text-xl font-bold tracking-tight text-slate-700 dark:text-navy-100 sm:text-2xl">
                        − KES {{ props.summary.discounts }}
                    </p>
                </div>
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-warning/10 text-warning dark:bg-warning/15">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41 11 3.83V3H4v7h.83l9.58 9.59a2 2 0 0 0 2.83 0l3.35-3.35a2 2 0 0 0 0-2.83Z" />
                        <path d="M7.5 7.5h.01" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-navy-700">
                <div class="h-full w-full rounded-full bg-warning/60"></div>
            </div>
        </div>

        <!-- Net Revenue -->
        <div class="rounded-2xl border border-slate-150 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-navy-800 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">
                        Net Revenue
                    </p>
                    <p class="mt-2 text-xl font-bold tracking-tight text-slate-700 dark:text-navy-100 sm:text-2xl">
                        KES {{ props.summary.net_revenue }}
                    </p>
                </div>
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-info/10 text-info dark:bg-info/15">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 17 9 11l4 4 8-9" />
                        <path d="M15 6h6v6" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 h-1 overflow-hidden rounded-full bg-slate-100 dark:bg-navy-700">
                <div class="h-full w-full rounded-full bg-info/60"></div>
            </div>
        </div>

        <!-- Paid Orders -->
        <div class="rounded-2xl border border-slate-150 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-navy-800 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-navy-300">
                        Paid Orders
                    </p>
                    <p class="mt-2 text-xl font-bold tracking-tight text-slate-700 dark:text-navy-100 sm:text-2xl">
                        {{ props.summary.orders_paid }}
                    </p>
                </div>
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-secondary/10 text-secondary dark:bg-secondary/15">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="8" r="4" />
                        <path d="M3 21v-2a6 6 0 0 1 12 0v2" />
                        <path d="m16 11 2 2 4-4" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs text-slate-400 dark:text-navy-300">
                <span class="font-medium text-warning">{{ props.summary.orders_pending }}</span> pending
                <span class="mx-1.5">·</span>
                <span class="font-medium text-error">{{ props.summary.orders_cancelled }}</span> cancelled
            </p>
        </div>
    </div>

    <!-- Dashboard Sections -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <!-- Top Products -->
        <div class="overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
            <div class="border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]">
                <h3 class="font-semibold text-slate-700 dark:text-navy-100">
                    Top Products
                </h3>
                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                    Best performing products by sales revenue
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-150 text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:text-navy-300">
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Product
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Units
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700">
                                Revenue
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                        <tr
                            v-for="p in props.topProducts"
                            :key="p.sku"
                            class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                        >
                            <td class="border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <p class="min-w-[140px] font-semibold text-slate-700 dark:text-navy-100">
                                    {{ p.product_name }}
                                </p>
                                <p class="mt-0.5 font-mono text-xs text-slate-400 dark:text-navy-300">
                                    {{ p.sku }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                {{ p.units_sold }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-right font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                KES {{ p.revenue }}
                            </td>
                        </tr>

                        <tr v-if="props.topProducts.length === 0">
                            <td colspan="3" class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300">
                                No data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Categories -->
        <div class="overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
            <div class="border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]">
                <h3 class="font-semibold text-slate-700 dark:text-navy-100">
                    Top Categories
                </h3>
                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                    Best performing categories by sales revenue
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-150 text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:text-navy-300">
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Category
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Units
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700">
                                Revenue
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                        <tr
                            v-for="c in props.topCategories"
                            :key="c.category"
                            class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                        >
                            <td class="border border-slate-150 px-5 py-3 font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                {{ c.category }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                {{ c.units_sold }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-right font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                KES {{ c.revenue }}
                            </td>
                        </tr>

                        <tr v-if="props.topCategories.length === 0">
                            <td colspan="3" class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300">
                                No data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
            <div class="border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]">
                <h3 class="font-semibold text-slate-700 dark:text-navy-100">
                    Top Customers
                </h3>
                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                    Customers ranked by total spending
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-150 text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:text-navy-300">
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Customer
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Orders
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700">
                                Spent
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                        <tr
                            v-for="c in props.topCustomers"
                            :key="c.customer_email"
                            class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                        >
                            <td class="border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <p class="min-w-[140px] font-semibold text-slate-700 dark:text-navy-100">
                                    {{ c.customer_name }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                                    {{ c.customer_email }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                {{ c.orders }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-right font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                KES {{ c.total_spent }}
                            </td>
                        </tr>

                        <tr v-if="props.topCustomers.length === 0">
                            <td colspan="3" class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300">
                                No data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Secondary Lists and Statistics -->
        <div class="space-y-6">

            <!-- Payment Methods -->
            <div class="overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <div class="border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]">
                    <h3 class="font-semibold text-slate-700 dark:text-navy-100">
                        Payment Methods
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                        Payment distribution by provider
                    </p>
                </div>

                <ul class="divide-y divide-slate-150 dark:divide-slate-150/15">
                    <li
                        v-for="p in props.paymentMethods"
                        :key="p.provider"
                        class="flex items-center justify-between gap-4 px-5 py-3 text-sm transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                    >
                        <span class="font-medium text-slate-700 dark:text-navy-100">
                            {{ p.provider }}
                        </span>

                        <span class="whitespace-nowrap text-right text-slate-500 dark:text-navy-300">
                            {{ p.count }}
                            <span class="mx-1">·</span>
                            <strong class="font-semibold text-slate-700 dark:text-navy-100">
                                KES {{ p.total }}
                            </strong>
                        </span>
                    </li>

                    <li
                        v-if="props.paymentMethods.length === 0"
                        class="px-5 py-8 text-center text-sm text-slate-400 dark:text-navy-300"
                    >
                        No data.
                    </li>
                </ul>
            </div>

            <!-- Coupon Usage -->
            <div class="overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <div class="border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]">
                    <h3 class="font-semibold text-slate-700 dark:text-navy-100">
                        Coupon Usage
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                        Discounts redeemed through coupons
                    </p>
                </div>

                <ul class="divide-y divide-slate-150 dark:divide-slate-150/15">
                    <li
                        v-for="c in props.couponUsage"
                        :key="c.code"
                        class="flex items-center justify-between gap-4 px-5 py-3 text-sm transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                    >
                        <span class="font-mono font-semibold text-slate-700 dark:text-navy-100">
                            {{ c.code }}
                        </span>

                        <span class="whitespace-nowrap text-right text-slate-500 dark:text-navy-300">
                            {{ c.redemptions }}
                            <span class="mx-1">·</span>
                            <strong class="font-semibold text-warning">
                                − KES {{ c.total_discount }}
                            </strong>
                        </span>
                    </li>

                    <li
                        v-if="props.couponUsage.length === 0"
                        class="px-5 py-8 text-center text-sm text-slate-400 dark:text-navy-300"
                    >
                        No data.
                    </li>
                </ul>
            </div>

            <!-- Inventory -->
            <div class="overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <div class="border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]">
                    <h3 class="font-semibold text-slate-700 dark:text-navy-100">
                        Inventory
                    </h3>
                    <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                        Current stock overview
                    </p>
                </div>

                <dl class="divide-y divide-slate-150 dark:divide-slate-150/15">
                    <div class="flex items-center justify-between gap-4 px-5 py-3 text-sm transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]">
                        <dt class="text-slate-600 dark:text-navy-200">
                            Variations
                        </dt>
                        <dd class="font-semibold text-slate-700 dark:text-navy-100">
                            {{ props.inventory.total_variations }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4 px-5 py-3 text-sm transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]">
                        <dt class="text-slate-600 dark:text-navy-200">
                            Low Stock
                        </dt>
                        <dd class="font-semibold text-warning">
                            {{ props.inventory.low_stock }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4 px-5 py-3 text-sm transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]">
                        <dt class="text-slate-600 dark:text-navy-200">
                            Out of Stock
                        </dt>
                        <dd class="font-semibold text-error">
                            {{ props.inventory.out_of_stock }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4 px-5 py-3 text-sm transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]">
                        <dt class="text-slate-600 dark:text-navy-200">
                            Stock Value
                        </dt>
                        <dd class="font-semibold text-slate-700 dark:text-navy-100">
                            KES {{ props.inventory.stock_value.toFixed(2) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>

    </div>
</template>