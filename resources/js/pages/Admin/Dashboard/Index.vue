<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import type { PageProps } from '@/types';
import { admin_orders_show } from '@/routes';
import VueApexCharts from 'vue3-apexcharts';
import { ArrowUpRight } from '@lucide/vue';

// Interfaces
interface RecentOrder { ulid: string; order_number: string; customer_name: string; grand_total: string; payment_status: string; fulfilment_status: string; created_at: string; }
interface TopProduct { product_name: string; sku: string; total_sold: number; total_revenue: string; }
interface Activity { id: string; action: string; description: string; user_name: string; created_at: string; }
interface TopCustomer { customer_name: string; customer_email: string; total_spent: string; order_count: number; }
interface ChartData { date: string; total: string; }

interface Props extends PageProps {
    filters: { from: string; to: string };
    stats: { revenue: string; total_orders: number; pending_orders: number; new_customers: number; total_products: number; low_stock: number; out_of_stock: number; };
    charts: {
        revenue_trend: ChartData[];
        order_status: { status: string; count: number }[];
        payment_status: { status: string; count: number }[]; // Changed from payment_methods
    };
    recentOrders: RecentOrder[];
    topProducts: TopProduct[];
    recentActivity: Activity[];
    topCustomers: TopCustomer[];
}

const props = defineProps<Props>();

const fromDate = ref(props.filters.from);
const toDate = ref(props.filters.to);

watch([fromDate, toDate], () => {
    router.get('/admin/dashboard', { from: fromDate.value, to: toDate.value }, { preserveState: true, preserveScroll: true, replace: true });
});

// Badge helper
function badgeClass(status: string): string {
    switch (status) {
        case 'paid': case 'delivered': return 'bg-success/10 text-success';
        case 'pending': return 'bg-warning/10 text-warning';
        case 'processing': return 'bg-info/10 text-info';
        default: return 'bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-300';
    }
}

// Helper to map status names to specific Tailwind Hex colors
const getStatusColor = (status: string): string => {
    const s = status.toLowerCase();
    if (s.includes('paid') || s.includes('deliver') || s.includes('complete')) return '#10b981'; // Success
    if (s.includes('pending') || s.includes('await')) return '#f59e0b'; // Warning
    if (s.includes('process') || s.includes('ship')) return '#3b82f6'; // Info
    if (s.includes('fail') || s.includes('cancel') || s.includes('refund')) return '#ef4444'; // Error
    return '#64748b'; // Default Slate
};

// --- ApexCharts Configuration ---

// 1. Revenue Area Chart
const revenueSeries = computed(() => [{ name: 'Revenue', data: props.charts.revenue_trend.map(d => d.total) }]);
const revenueOptions = computed(() => ({
    chart: { type: 'area', height: 300, fontFamily: 'inherit', toolbar: { show: false } },
    colors: ['#10b981'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [20, 100, 100, 100] } },
    xaxis: { categories: props.charts.revenue_trend.map(d => d.date), labels: { style: { colors: '#94a3b8' } } },
    yaxis: { labels: { formatter: (val: number) => `KES ${val.toFixed(0)}`, style: { colors: '#94a3b8' } } },
    grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
    tooltip: { y: { formatter: (val: number) => `KES ${val.toFixed(2)}` } }
}));

// 2. Order Status Donut
const orderStatusSeries = computed(() => props.charts.order_status.map(d => d.count));
const orderStatusOptions = computed(() => ({
    chart: {
        type: 'donut',
        height: 320,
        fontFamily: 'inherit'
    },
    labels: props.charts.order_status.map(d => d.status),
    colors: props.charts.order_status.map(d => getStatusColor(d.status)),
    dataLabels: { enabled: true },
    legend: {
        position: 'bottom',
        horizontalAlign: 'left'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    name: { show: true },
                    value: { show: true },
                    total: {
                        show: true,
                        label: 'Orders',
                        formatter: () => props.stats.total_orders.toString()
                    }
                }
            }
        }
    },
    // NO startAngle or endAngle properties here!
}));

// 3. Payment Methods Donut
const paymentSeries = computed(() => props.charts.payment_status.map(d => d.count));
const paymentOptions = computed(() => ({
    chart: {
        type: 'donut',
        height: 320,
        fontFamily: 'inherit'
    },
    labels: props.charts.payment_status.map(d => d.status),
    colors: props.charts.payment_status.map(d => getStatusColor(d.status)),
    dataLabels: { enabled: true },
    legend: {
        position: 'bottom',
        horizontalAlign: 'left'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '75%' // 75% creates a clean, full donut ring
            }
        }
    },
}));
</script>

<template>

    <Head title="Dashboard" />

    <div class="p-4 sm:p-5 lg:p-6">
        <!-- Header & Date Filter -->
        <div class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between lg:py-6">
            <div>
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Dashboard Overview</h2>
                <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">Showing data from {{ filters.from }} to {{
                    filters.to }}</p>
            </div>
            <div class="flex items-center gap-2">
                <input v-model="fromDate" type="date"
                    class="form-input rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm focus:border-primary dark:border-navy-450 dark:focus:border-accent" />
                <span class="text-slate-400">to</span>
                <input v-model="toDate" type="date"
                    class="form-input rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm focus:border-primary dark:border-navy-450 dark:focus:border-accent" />
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Revenue -->
            <div
                class="rounded-xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500 dark:text-navy-300">Total Revenue</p>
                    <div class="flex size-12 p-2 items-center justify-center rounded-full bg-success/10 text-success">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                <path opacity="0.4"
                                    d="M18.8105 6.24998H20.1706C19.9806 5.97998 19.7806 5.72998 19.5706 5.47998L18.8105 6.24998Z"
                                    fill="#2dd4bf"></path>
                                <path opacity="0.4"
                                    d="M18.52 4.41982C18.27 4.20982 18.02 4.00982 17.75 3.81982V5.17982L18.52 4.41982Z"
                                    fill="#2dd4bf"></path>
                                <path opacity="0.4"
                                    d="M17 7.75C16.9 7.75 16.81 7.73 16.71 7.69C16.53 7.61 16.38 7.47 16.3 7.28C16.26 7.19 16.24 7.09 16.24 6.99V2.99C16.24 2.98 16.25 2.97 16.25 2.95C14.96 2.35 13.52 2 12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 10.48 21.65 9.04 21.04 7.74C21.03 7.74 21.02 7.75 21 7.75H17Z"
                                    fill="#2dd4bf"></path>
                                <path
                                    d="M19.5795 5.47994L22.5295 2.52994C22.8195 2.23994 22.8195 1.75994 22.5295 1.46994C22.2395 1.17994 21.7595 1.17994 21.4695 1.46994L18.5195 4.41994C18.8995 4.74994 19.2495 5.10994 19.5795 5.47994Z"
                                    fill="#2dd4bf"></path>
                                <path
                                    d="M17.7498 3C17.7498 2.59 17.4098 2.25 16.9998 2.25C16.5998 2.25 16.2798 2.57 16.2598 2.96C16.7798 3.21 17.2798 3.49 17.7498 3.82V3Z"
                                    fill="#2dd4bf"></path>
                                <path
                                    d="M21.7499 7C21.7499 6.59 21.4099 6.25 20.9999 6.25H20.1699C20.4999 6.72 20.7899 7.22 21.0299 7.74C21.4299 7.72 21.7499 7.4 21.7499 7Z"
                                    fill="#2dd4bf"></path>
                                <path
                                    d="M13.75 11.82L12.75 11.47V9.25H12.83C13.34 9.25 13.75 9.7 13.75 10.25C13.75 10.66 14.09 11 14.5 11C14.91 11 15.25 10.66 15.25 10.25C15.25 8.87 14.17 7.75 12.83 7.75H12.75V7.5C12.75 7.09 12.41 6.75 12 6.75C11.59 6.75 11.25 7.09 11.25 7.5V7.75H10.95C9.74001 7.75 8.75 8.77 8.75 10.03C8.75 11.49 9.6 11.96 10.25 12.19L11.25 12.54V14.76H11.17C10.66 14.76 10.25 14.31 10.25 13.76C10.25 13.35 9.91 13.01 9.5 13.01C9.09 13.01 8.75 13.35 8.75 13.76C8.75 15.14 9.83001 16.26 11.17 16.26H11.25V16.51C11.25 16.92 11.59 17.26 12 17.26C12.41 17.26 12.75 16.92 12.75 16.51V16.26H13.05C14.26 16.26 15.25 15.24 15.25 13.98C15.25 12.51 14.4 12.04 13.75 11.82ZM10.74 10.76C10.4 10.64 10.25 10.57 10.25 10.02C10.25 9.59 10.57 9.24 10.95 9.24H11.25V10.93L10.74 10.76ZM13.05 14.75H12.75V13.06L13.26 13.24C13.6 13.36 13.75 13.43 13.75 13.98C13.75 14.4 13.43 14.75 13.05 14.75Z"
                                    fill="#2dd4bf"></path>
                            </g>
                        </svg>
                    </div>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-700 dark:text-navy-100">KES {{ stats.revenue }}</p>
            </div>

            <!-- Orders -->
            <div
                class="rounded-xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500 dark:text-navy-300">Total Orders</p>
                    <div class="flex size-12 p-2 items-center justify-center rounded-full bg-primary/10 text-primary">
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
                    </div>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-700 dark:text-navy-100">{{ stats.total_orders }}</p>
                <p class="mt-1 text-xs text-warning">{{ stats.pending_orders }} pending fulfilment</p>
            </div>

            <!-- Customers -->
            <div
                class="rounded-xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500 dark:text-navy-300">New Customers</p>
                    <div class="flex size-12 p-2 items-center justify-center rounded-full bg-info/10 text-info">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                <path opacity="0.4"
                                    d="M22 16.5V19.5C22 21 21 22 19.5 22H6C6.41 22 6.83 21.94 7.22 21.81C7.33 21.77 7.43999 21.73 7.54999 21.68C7.89999 21.54 8.24001 21.34 8.54001 21.08C8.63001 21.01 8.73001 20.92 8.82001 20.83L8.85999 20.79L15.66 14H19.5C21 14 22 15 22 16.5Z"
                                    fill="#06b6d4"></path>
                                <path opacity="0.6"
                                    d="M18.3694 11.29L15.6594 14L8.85938 20.79C9.55937 20.07 9.99939 19.08 9.99939 18V8.33996L12.7094 5.62996C13.7694 4.56996 15.1894 4.56996 16.2494 5.62996L18.3694 7.74996C19.4294 8.80996 19.4294 10.23 18.3694 11.29Z"
                                    fill="#06b6d4"></path>
                                <path
                                    d="M7.5 2H4.5C3 2 2 3 2 4.5V18C2 18.27 2.02999 18.54 2.07999 18.8C2.10999 18.93 2.13999 19.06 2.17999 19.19C2.22999 19.34 2.28 19.49 2.34 19.63C2.35 19.64 2.35001 19.65 2.35001 19.65C2.36001 19.65 2.36001 19.65 2.35001 19.66C2.49001 19.94 2.65 20.21 2.84 20.46C2.95 20.59 3.06001 20.71 3.17001 20.83C3.28001 20.95 3.4 21.05 3.53 21.15L3.54001 21.16C3.79001 21.35 4.06 21.51 4.34 21.65C4.35 21.64 4.35001 21.64 4.35001 21.65C4.50001 21.72 4.65 21.77 4.81 21.82C4.94 21.86 5.07001 21.89 5.20001 21.92C5.46001 21.97 5.73 22 6 22C6.41 22 6.83 21.94 7.22 21.81C7.33 21.77 7.43999 21.73 7.54999 21.68C7.89999 21.54 8.24001 21.34 8.54001 21.08C8.63001 21.01 8.73001 20.92 8.82001 20.83L8.85999 20.79C9.55999 20.07 10 19.08 10 18V4.5C10 3 9 2 7.5 2ZM6 19.5C5.17 19.5 4.5 18.83 4.5 18C4.5 17.17 5.17 16.5 6 16.5C6.83 16.5 7.5 17.17 7.5 18C7.5 18.83 6.83 19.5 6 19.5Z"
                                    fill="#06b6d4"></path>
                            </g>
                        </svg>
                    </div>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-700 dark:text-navy-100">{{ stats.new_customers }}</p>
            </div>

            <!-- Inventory -->
            <div
                class="rounded-xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500 dark:text-navy-300">Inventory Health</p>
                    <div class="flex size-12 p-2 items-center justify-center rounded-full bg-error/10 text-error">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                <path opacity="0.4"
                                    d="M21.0409 7.19L12.0009 12.42L2.96094 7.19C3.36094 6.45 3.94094 5.8 4.59094 5.44L9.93094 2.48C11.0709 1.84 12.9309 1.84 14.0709 2.48L19.4109 5.44C20.0609 5.8 20.6409 6.45 21.0409 7.19Z"
                                    fill="#f43f5e"></path>
                                <path opacity="0.6"
                                    d="M12.0006 12.4199V21.9999C11.2506 21.9999 10.5006 21.8399 9.93062 21.5199L4.59063 18.5599C3.38063 17.8899 2.39062 16.2099 2.39062 14.8299V9.16994C2.39062 8.52994 2.61062 7.82994 2.96062 7.18994L12.0006 12.4199Z"
                                    fill="#f43f5e"></path>
                                <path
                                    d="M21.61 9.16994V14.8299C21.61 16.2099 20.62 17.8899 19.41 18.5599L14.07 21.5199C13.5 21.8399 12.75 21.9999 12 21.9999V12.4199L21.04 7.18994C21.39 7.82994 21.61 8.52994 21.61 9.16994Z"
                                    fill="#f43f5e"></path>
                            </g>
                        </svg>
                    </div>
                </div>
                <p class="mt-4 text-2xl font-bold text-slate-700 dark:text-navy-100">{{ stats.total_products }} <span
                        class="text-sm font-normal text-slate-400">products</span></p>
                <div class="mt-1 flex gap-3 text-xs">
                    <span class="text-warning">{{ stats.low_stock }} low stock</span>
                    <span class="text-error">{{ stats.out_of_stock }} out of stock</span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Revenue Trend Chart -->
            <div
                class="col-span-1 rounded-2xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800 lg:col-span-2">
                <h3 class="mb-4 font-medium text-slate-700 dark:text-navy-100">Revenue Trend</h3>
                <VueApexCharts type="area" height="300" :options="revenueOptions" :series="revenueSeries" />
            </div>

            <!-- Order Status Donut -->
            <div
                class="col-span-1 rounded-2xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <h3 class="mb-4 font-medium text-slate-700 dark:text-navy-100">Order Fulfilment</h3>
                <VueApexCharts type="donut" height="320" :options="orderStatusOptions" :series="orderStatusSeries" />
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Payment Status Donut (Replaced Payment Providers) -->
            <div
                class="col-span-1 rounded-2xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <h3 class="mb-4 font-medium text-slate-700 dark:text-navy-100">Payment Status</h3>
                <VueApexCharts type="donut" height="320" :options="paymentOptions" :series="paymentSeries" />
            </div>

            <!-- Recent Activity Log -->
            <div
                class="col-span-1 rounded-2xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800 lg:col-span-2">
                <h3 class="mb-4 font-medium text-slate-700 dark:text-navy-100">Recent Admin Activity</h3>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <li v-for="(event, eventIdx) in recentActivity" :key="event.id">
                            <div class="relative pb-8">
                                <span v-if="eventIdx !== recentActivity.length - 1"
                                    class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200 dark:bg-navy-600"
                                    aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span
                                            class="flex size-8 items-center justify-center rounded-full bg-primary/10 text-primary ring-8 ring-white dark:ring-navy-800">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <p class="text-sm text-slate-600 dark:text-navy-200">
                                                <span class="font-medium text-slate-900 dark:text-navy-50">{{
                                                    event.user_name }}</span>
                                                {{ event.description }}
                                            </p>
                                        </div>
                                        <div
                                            class="whitespace-nowrap text-right text-xs text-slate-400 dark:text-navy-300">
                                            {{ event.created_at }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li v-if="recentActivity.length === 0" class="text-sm text-slate-400">No recent activity found.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent Orders -->
            <div
    class="mt-4 overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800"
>
    <!-- Card Header -->
    <div
        class="flex items-center justify-between border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]"
    >
        <div>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                Recent Orders
            </h3>
            <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                Latest customer transactions
            </p>
        </div>

        <span
            class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-1 text-[11px] font-semibold text-primary dark:bg-accent-light/10 dark:text-accent-light"
        >
            {{ recentOrders.length }} orders
        </span>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full table-fixed border-collapse text-left text-sm">
            <thead>
                <tr
                    class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300"
                >
                    <th class="w-1/4 border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                        Order
                    </th>
                    <th class="w-1/4 border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                        Customer
                    </th>
                    <th class="w-1/4 border border-slate-150 px-5 py-4 font-bold text-right dark:border-slate-700">
                        Total
                    </th>
                    <th class="w-1/6 border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700">
                        Action
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                <tr
                    v-for="order in recentOrders"
                    :key="order.ulid"
                    class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                >
                    <!-- Order -->
                    <td
                        class="border border-slate-150 px-5 py-3 align-top dark:border-slate-700"
                    >
                        <p class="break-words font-mono text-xs font-bold text-slate-700 dark:text-navy-100">
                            {{ order.order_number }}
                        </p>

                        <span
                            :class="badgeClass(order.payment_status)"
                            class="mt-1 inline-flex max-w-full rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                        >
                            {{ order.payment_status }}
                        </span>
                    </td>

                    <!-- Customer -->
                    <td
                        class="break-words border border-slate-150 px-5 py-3 align-top text-slate-600 dark:border-slate-700 dark:text-navy-200"
                    >
                        {{ order.customer_name }}
                    </td>

                    <!-- Total -->
                    <td
                        class="border border-slate-150 px-5 py-3 align-top dark:border-slate-700"
                    >
                        <div class="max-w-full overflow-x-auto whitespace-nowrap font-semibold text-right text-slate-700 dark:text-navy-100">
                            {{ order.grand_total }}
                        </div>
                    </td>

                    <!-- Action -->
                    <td
                        class="border border-slate-150 px-5 py-3 text-left align-top dark:border-slate-700"
                    >
                        <Link
                            :href="admin_orders_show.url(order.ulid)"
                            class="badge space-x-2.5 rounded-full bg-info/10 text-info dark:bg-info/15"
                        >
                            View
                        </Link>
                    </td>
                </tr>

                <!-- Empty State -->
                <tr v-if="recentOrders.length === 0">
                    <td
                        colspan="4"
                        class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300"
                    >
                        <div class="flex flex-col items-center">
                            <div
                                class="mb-3 flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300"
                            >
                                <icon name="shopping-bag" class="size-5" />
                            </div>

                            <p class="font-medium text-slate-600 dark:text-navy-200">
                                No orders found.
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-400">
                                Recent orders will appear here.
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

            <!-- Top Selling Products -->
            <div
    class="mt-4 overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800"
>
    <!-- Card Header -->
    <div
        class="flex items-center justify-between border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]"
    >
        <div>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                Top Selling Products
            </h3>
            <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                Best performing products by sales
            </p>
        </div>

        <span
            class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-1 text-[11px] font-semibold text-primary dark:bg-accent-light/10 dark:text-accent-light"
        >
            {{ topProducts.length }} products
        </span>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full table-fixed border-collapse text-left text-sm">
            <thead>
                <tr
                    class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300"
                >
                    <th class="w-1/3 border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                        Product
                    </th>
                    <th class="w-1/4 border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                        Sold
                    </th>
                    <th class="w-1/3 border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700">
                        Revenue
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                <tr
                    v-for="(product, index) in topProducts"
                    :key="index"
                    class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                >
                    <!-- Product -->
                    <td
                        class="break-words border border-slate-150 px-5 py-3 align-top dark:border-slate-700"
                    >
                        <p class="font-semibold text-slate-700 dark:text-navy-100">
                            {{ product.product_name }}
                        </p>
                        <p class="mt-1 break-all font-mono text-2xs text-slate-400 dark:text-navy-400">
                            {{ product.sku }}
                        </p>
                    </td>

                    <!-- Quantity Sold -->
                    <td
                        class="border border-slate-150 px-5 py-3 align-top font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100"
                    >
                        {{ product.total_sold }} units
                    </td>

                    <!-- Revenue -->
                    <td
                        class="border border-slate-150 px-5 py-3 text-right align-top"
                    >
                        <div class="max-w-full overflow-x-auto whitespace-nowrap font-semibold tabular-nums text-success">
                            KES {{ product.total_revenue }}
                        </div>
                    </td>
                </tr>

                <!-- Empty State -->
                <tr v-if="topProducts.length === 0">
                    <td
                        colspan="3"
                        class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300"
                    >
                        <div class="flex flex-col items-center">
                            <div
                                class="mb-3 flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300"
                            >
                                <icon name="package" class="size-5" />
                            </div>

                            <p class="font-medium text-slate-600 dark:text-navy-200">
                                No sales found.
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-400">
                                Top selling products will appear here.
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
        </div>
    </div>
</template>