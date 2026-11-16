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
            <div class="mt-4 grid grid-cols-2 gap-3  sm:mt-5 sm:grid-cols-4 sm:gap-5  lg:mt-6">
                <div class="rounded-lg bg-slate-100 p-4 dark:bg-navy-600">
                    <div class="flex justify-between space-x-1">
                        <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                            KES {{ props.summary.gross_sales }}
                        </p>
                        <div class="mask is-star flex size-10 shrink-0 items-center justify-center bg-success">
                            <svg viewBox="-15.36 -15.36 54.72 54.72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                    <path opacity="0.4"
                                        d="M9.99 17.98C14.4028 17.98 17.98 14.4028 17.98 9.99C17.98 5.57724 14.4028 2 9.99 2C5.57724 2 2 5.57724 2 9.99C2 14.4028 5.57724 17.98 9.99 17.98Z"
                                        fill="#ffffff"></path>
                                    <path
                                        d="M21.9701 15.9901C21.9701 19.2901 19.2901 21.9701 15.9901 21.9701C13.9501 21.9701 12.1601 20.9501 11.0801 19.4001C15.4401 18.9101 18.9101 15.4401 19.4001 11.0801C20.9501 12.1601 21.9701 13.9501 21.9701 15.9901Z"
                                        fill="#ffffff"></path>
                                    <path
                                        d="M11.45 9.71016L9.05 8.87016C8.81 8.79016 8.76 8.77016 8.76 8.42016C8.76 8.16016 8.94 7.95016 9.17 7.95016H10.67C10.99 7.95016 11.25 8.24016 11.25 8.60016C11.25 9.01016 11.59 9.35016 12 9.35016C12.41 9.35016 12.75 9.01016 12.75 8.60016C12.75 7.45016 11.86 6.51016 10.75 6.46016V6.41016C10.75 6.00016 10.41 5.66016 10 5.66016C9.59 5.66016 9.25 5.99016 9.25 6.41016V6.46016H9.16C8.11 6.46016 7.25 7.34016 7.25 8.43016C7.25 9.38016 7.67 9.99016 8.54 10.2902L10.95 11.1302C11.19 11.2102 11.24 11.2302 11.24 11.5802C11.24 11.8402 11.06 12.0502 10.83 12.0502H9.33C9.01 12.0502 8.75 11.7602 8.75 11.4002C8.75 10.9902 8.41 10.6502 8 10.6502C7.59 10.6502 7.25 10.9902 7.25 11.4002C7.25 12.5502 8.14 13.4902 9.25 13.5402V13.6002C9.25 14.0102 9.59 14.3502 10 14.3502C10.41 14.3502 10.75 14.0102 10.75 13.6002V13.5502H10.84C11.89 13.5502 12.75 12.6702 12.75 11.5802C12.75 10.6302 12.32 10.0202 11.45 9.71016Z"
                                        fill="#ffffff"></path>
                                </g>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1 text-xs-plus">Gross Sales</p>
                </div>
                <div class="rounded-lg bg-slate-100 p-4 dark:bg-navy-600">
                    <div class="flex justify-between">
                        <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                            − KES {{ props.summary.discounts }}
                        </p>
                        <div class="mask is-star flex size-10 shrink-0 items-center justify-center bg-warning">
                            <svg viewBox="-15.36 -15.36 54.72 54.72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                    <path opacity="0.4"
                                        d="M22 5.15V8.85C22 11.1 21.1 12 18.85 12H16.15C13.9 12 13 11.1 13 8.85V5.15C13 2.9 13.9 2 16.15 2H18.85C21.1 2 22 2.9 22 5.15Z"
                                        fill="#ffffff"></path>
                                    <path opacity="0.4"
                                        d="M11 15.15V18.85C11 21.1 10.1 22 7.85 22H5.15C2.9 22 2 21.1 2 18.85V15.15C2 12.9 2.9 12 5.15 12H7.85C10.1 12 11 12.9 11 15.15Z"
                                        fill="#ffffff"></path>
                                    <path
                                        d="M14.7795 22C14.5095 22 14.2595 21.85 14.1295 21.62C13.9995 21.38 13.9995 21.1 14.1395 20.86L15.1095 19.24C15.3195 18.88 15.7795 18.77 16.1395 18.98C16.4995 19.19 16.6095 19.65 16.3995 20.01L16.2195 20.31C18.6895 19.67 20.5095 17.43 20.5095 14.77C20.5095 14.36 20.8495 14.02 21.2595 14.02C21.6695 14.02 21.9995 14.36 21.9995 14.78C21.9995 18.76 18.7595 22 14.7795 22Z"
                                        fill="#ffffff"></path>
                                    <path
                                        d="M2.75 9.97C2.34 9.97 2 9.64 2 9.22C2 5.24 5.24 2 9.22 2C9.5 2 9.74 2.15 9.88 2.38C10.01 2.62 10.01 2.9 9.87 3.14L8.9 4.75C8.68 5.11 8.22 5.23 7.87 5.01C7.51 4.8 7.4 4.34 7.61 3.98L7.79 3.68C5.33 4.32 3.5 6.56 3.5 9.22C3.5 9.64 3.16 9.97 2.75 9.97Z"
                                        fill="#ffffff"></path>
                                </g>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1 text-xs-plus">Discounts</p>
                </div>
                <div class="rounded-lg bg-slate-100 p-4 dark:bg-navy-600">
                    <div class="flex justify-between">
                        <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                            KES {{ props.summary.net_revenue }}
                        </p>
                        <div class="mask is-star flex size-10 shrink-0 items-center justify-center bg-info">
                            <svg viewBox="-15.36 -15.36 54.72 54.72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                    <path opacity="0.4"
                                        d="M22 16.5V19.5C22 21 21 22 19.5 22H6C6.41 22 6.83 21.94 7.22 21.81C7.33 21.77 7.43999 21.73 7.54999 21.68C7.89999 21.54 8.24001 21.34 8.54001 21.08C8.63001 21.01 8.73001 20.92 8.82001 20.83L8.85999 20.79L15.66 14H19.5C21 14 22 15 22 16.5Z"
                                        fill="#ffffff"></path>
                                    <path opacity="0.6"
                                        d="M18.3694 11.29L15.6594 14L8.85938 20.79C9.55937 20.07 9.99939 19.08 9.99939 18V8.33996L12.7094 5.62996C13.7694 4.56996 15.1894 4.56996 16.2494 5.62996L18.3694 7.74996C19.4294 8.80996 19.4294 10.23 18.3694 11.29Z"
                                        fill="#ffffff"></path>
                                    <path
                                        d="M7.5 2H4.5C3 2 2 3 2 4.5V18C2 18.27 2.02999 18.54 2.07999 18.8C2.10999 18.93 2.13999 19.06 2.17999 19.19C2.22999 19.34 2.28 19.49 2.34 19.63C2.35 19.64 2.35001 19.65 2.35001 19.65C2.36001 19.65 2.36001 19.65 2.35001 19.66C2.49001 19.94 2.65 20.21 2.84 20.46C2.95 20.59 3.06001 20.71 3.17001 20.83C3.28001 20.95 3.4 21.05 3.53 21.15L3.54001 21.16C3.79001 21.35 4.06 21.51 4.34 21.65C4.35 21.64 4.35001 21.64 4.35001 21.65C4.50001 21.72 4.65 21.77 4.81 21.82C4.94 21.86 5.07001 21.89 5.20001 21.92C5.46001 21.97 5.73 22 6 22C6.41 22 6.83 21.94 7.22 21.81C7.33 21.77 7.43999 21.73 7.54999 21.68C7.89999 21.54 8.24001 21.34 8.54001 21.08C8.63001 21.01 8.73001 20.92 8.82001 20.83L8.85999 20.79C9.55999 20.07 10 19.08 10 18V4.5C10 3 9 2 7.5 2ZM6 19.5C5.17 19.5 4.5 18.83 4.5 18C4.5 17.17 5.17 16.5 6 16.5C6.83 16.5 7.5 17.17 7.5 18C7.5 18.83 6.83 19.5 6 19.5Z"
                                        fill="#ffffff"></path>
                                </g>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1 text-xs-plus">Net Revenue</p>
                </div>
                <div class="rounded-lg bg-slate-100 p-4 dark:bg-navy-600">
                    <div class="flex justify-between">
                        <p class="text-xl font-semibold text-slate-700 dark:text-navy-100">
                            {{ props.summary.orders_paid }}
                        </p>
                        <div class="mask is-star flex size-10 shrink-0 items-center justify-center bg-secondary">
                            <svg viewBox="-15.36 -15.36 54.72 54.72" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                    <path opacity="0.6"
                                        d="M14 16C14 17.77 13.23 19.37 12 20.46C10.94 21.42 9.54 22 8 22C4.69 22 2 19.31 2 16C2 13.24 3.88 10.9 6.42 10.21C7.11 11.95 8.59 13.29 10.42 13.79C10.92 13.93 11.45 14 12 14C12.55 14 13.08 13.93 13.58 13.79C13.85 14.47 14 15.22 14 16Z"
                                        fill="#ffffff"></path>
                                    <path
                                        d="M18 8C18 8.78 17.85 9.53 17.58 10.21C16.89 11.95 15.41 13.29 13.58 13.79C13.08 13.93 12.55 14 12 14C11.45 14 10.92 13.93 10.42 13.79C8.59 13.29 7.11 11.95 6.42 10.21C6.15 9.53 6 8.78 6 8C6 4.69 8.69 2 12 2C15.31 2 18 4.69 18 8Z"
                                        fill="#ffffff"></path>
                                    <path opacity="0.4"
                                        d="M22 16C22 19.31 19.31 22 16 22C14.46 22 13.06 21.42 12 20.46C13.23 19.37 14 17.77 14 16C14 15.22 13.85 14.47 13.58 13.79C15.41 13.29 16.89 11.95 17.58 10.21C20.12 10.9 22 13.24 22 16Z"
                                        fill="#ffffff"></path>
                                </g>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1 text-xs-plus">Paid Orders</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                        {{ props.summary.orders_pending }} pending · {{ props.summary.orders_cancelled }} cancelled
                    </p>
                </div>
            </div>


            <!-- Grid Sections -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Top products -->
                <div class="card overflow-hidden">
                    <div
                        class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                        <h3 class="font-medium text-slate-700 dark:text-navy-100">Top products</h3>
                    </div>
                    <div class="is-scrollbar-x overflow-x-auto">
                        <table class="is-zebra w-full text-left text-sm">
    <thead>
        <tr>
            <th class="whitespace-nowrap rounded-l-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                Product
            </th>
            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                Units
            </th>
            <th class="whitespace-nowrap rounded-r-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                Revenue
            </th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="p in props.topProducts" :key="p.sku">
            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                <p class="font-medium text-slate-700 dark:text-navy-100">{{ p.product_name }}</p>
                <p class="font-mono text-xs text-slate-400 dark:text-navy-300">{{ p.sku }}</p>
            </td>
            <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                {{ p.units_sold }}
            </td>
            <td class="whitespace-nowrap rounded-r-lg px-4 py-3 font-semibold text-slate-700 dark:text-navy-100 sm:px-5">
                KES {{ p.revenue }}
            </td>
        </tr>
        <tr v-if="props.topProducts.length === 0">
            <td colspan="3" class="px-4 py-8 text-center text-slate-400 dark:text-navy-300">
                No data.
            </td>
        </tr>
    </tbody>
</table>
                    </div>
                </div>

                <!-- Top categories -->
                <div class="card overflow-hidden">
                    <div
                        class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                        <h3 class="font-medium text-slate-700 dark:text-navy-100">Top categories</h3>
                    </div>
                    <div class="is-scrollbar-x overflow-x-auto">
                        <table class="is-zebra w-full text-left text-sm">
    <thead>
        <tr>
            <th class="whitespace-nowrap rounded-l-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                Category
            </th>
            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                Units
            </th>
            <th class="whitespace-nowrap rounded-r-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                Revenue
            </th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="c in props.topCategories" :key="c.category">
            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                {{ c.category }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                {{ c.units_sold }}
            </td>
            <td class="whitespace-nowrap rounded-r-lg px-4 py-3 font-semibold text-slate-700 dark:text-navy-100 sm:px-5">
                KES {{ c.revenue }}
            </td>
        </tr>
        <tr v-if="props.topCategories.length === 0">
            <td colspan="3" class="px-4 py-8 text-center text-slate-400 dark:text-navy-300">
                No data.
            </td>
        </tr>
    </tbody>
</table>
                    </div>
                </div>

                <!-- Top customers -->
                <div class="card overflow-hidden">
                    <div
                        class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                        <h3 class="font-medium text-slate-700 dark:text-navy-100">Top customers</h3>
                    </div>
                    <div class="is-scrollbar-x overflow-x-auto">
                        <table class="is-zebra w-full text-left text-sm">
    <thead>
        <tr>
            <th class="whitespace-nowrap rounded-l-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                Customer
            </th>
            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                Orders
            </th>
            <th class="whitespace-nowrap rounded-r-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                Spent
            </th>
        </tr>
    </thead>
    <tbody>
        <tr v-for="c in props.topCustomers" :key="c.customer_email">
            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5">
                <p class="font-medium text-slate-700 dark:text-navy-100">{{ c.customer_name }}</p>
                <p class="text-xs text-slate-400 dark:text-navy-300">{{ c.customer_email }}</p>
            </td>
            <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                {{ c.orders }}
            </td>
            <td class="whitespace-nowrap rounded-r-lg px-4 py-3 font-semibold text-slate-700 dark:text-navy-100 sm:px-5">
                KES {{ c.total_spent }}
            </td>
        </tr>
        <tr v-if="props.topCustomers.length === 0">
            <td colspan="3" class="px-4 py-8 text-center text-slate-400 dark:text-navy-300">
                No data.
            </td>
        </tr>
    </tbody>
</table>
                    </div>
                </div>

                <!-- Secondary Lists & Stats Sidebar Section -->
                <div class="space-y-6">
                    <!-- Payment methods -->
                    <div class="card overflow-hidden">
                        <div
                            class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                            <h3 class="font-medium text-slate-700 dark:text-navy-100">Payment methods</h3>
                        </div>
                        <ul class="divide-y divide-slate-200 text-sm dark:divide-navy-600">
                            <li v-for="p in props.paymentMethods" :key="p.provider"
                                class="flex items-center justify-between px-4 py-3 sm:px-5 hover:bg-slate-50 dark:hover:bg-navy-900/50">
                                <span class="font-medium text-slate-700 dark:text-navy-100">{{ p.provider }}</span>
                                <span class="text-slate-600 dark:text-navy-200">{{ p.count }} · <strong
                                        class="font-semibold text-slate-700 dark:text-navy-100">KES {{ p.total
                                        }}</strong></span>
                            </li>
                            <li v-if="props.paymentMethods.length === 0"
                                class="px-4 py-6 text-center text-slate-400 dark:text-navy-300">No data.</li>
                        </ul>
                    </div>

                    <!-- Coupon usage -->
                    <div class="card overflow-hidden">
                        <div
                            class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                            <h3 class="font-medium text-slate-700 dark:text-navy-100">Coupon usage</h3>
                        </div>
                        <ul class="divide-y divide-slate-200 text-sm dark:divide-navy-600">
                            <li v-for="c in props.couponUsage" :key="c.code"
                                class="flex items-center justify-between px-4 py-3 sm:px-5 hover:bg-slate-50 dark:hover:bg-navy-900/50">
                                <span class="font-mono font-semibold text-slate-700 dark:text-navy-100">{{ c.code
                                    }}</span>
                                <span class="text-slate-600 dark:text-navy-200">{{ c.redemptions }} · <strong
                                        class="font-semibold text-warning">− KES {{ c.total_discount }}</strong></span>
                            </li>
                            <li v-if="props.couponUsage.length === 0"
                                class="px-4 py-6 text-center text-slate-400 dark:text-navy-300">No data.</li>
                        </ul>
                    </div>

                    <!-- Inventory -->
                    <div class="card overflow-hidden">
                        <div
                            class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                            <h3 class="font-medium text-slate-700 dark:text-navy-100">Inventory</h3>
                        </div>
                        <dl class="divide-y divide-slate-200 text-sm dark:divide-navy-600">
    <div class="flex items-center justify-between px-4 py-3 odd:bg-white even:bg-slate-50/70 dark:odd:bg-navy-800 dark:even:bg-navy-900/60 sm:px-5">
        <dt class="text-slate-600 dark:text-navy-200">Variations</dt>
        <dd class="font-semibold text-slate-700 dark:text-navy-100">{{ props.inventory.total_variations }}</dd>
    </div>
    <div class="flex items-center justify-between px-4 py-3 odd:bg-white even:bg-slate-50/70 dark:odd:bg-navy-800 dark:even:bg-navy-900/60 sm:px-5">
        <dt class="text-slate-600 dark:text-navy-200">Low stock</dt>
        <dd class="font-semibold text-warning">{{ props.inventory.low_stock }}</dd>
    </div>
    <div class="flex items-center justify-between px-4 py-3 odd:bg-white even:bg-slate-50/70 dark:odd:bg-navy-800 dark:even:bg-navy-900/60 sm:px-5">
        <dt class="text-slate-600 dark:text-navy-200">Out of stock</dt>
        <dd class="font-semibold text-error">{{ props.inventory.out_of_stock }}</dd>
    </div>
    <div class="flex items-center justify-between px-4 py-3 odd:bg-white even:bg-slate-50/70 dark:odd:bg-navy-800 dark:even:bg-navy-900/60 sm:px-5">
        <dt class="text-slate-600 dark:text-navy-200">Stock value</dt>
        <dd class="font-semibold text-slate-700 dark:text-navy-100">KES {{ props.inventory.stock_value.toFixed(2) }}</dd>
    </div>
</dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>