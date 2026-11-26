<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import type { PageProps } from "@/types";
import { admin_dashboard, admin_orders_show } from "@/routes";
import VueApexCharts from "vue3-apexcharts";
import { ArrowRight, ArrowUpRight } from "@lucide/vue";
import PaySVG from "@/components/SVG/paySVG.vue";
import CartSVG from "@/components/SVG/cartSVG.vue";
import StripsSVG from "@/components/SVG/stripsSVG.vue";
import PackageSVG from "@/components/SVG/packageSVG.vue";
import ReportsSVG from "@/components/SVG/reportsSVG.vue";
import MenuSVG from "@/components/SVG/menuSVG.vue";

// Interfaces
interface RecentOrder {
    ulid: string;
    order_number: string;
    customer_name: string;
    grand_total: string;
    payment_status: string;
    fulfilment_status: string;
    created_at: string;
}
interface TopProduct {
    product_name: string;
    sku: string;
    total_sold: number;
    total_revenue: string;
}
interface Activity {
    id: string;
    action: string;
    description: string;
    user_name: string;
    created_at: string;
}
interface TopCustomer {
    customer_name: string;
    customer_email: string;
    total_spent: string;
    order_count: number;
}
interface ChartData {
    date: string;
    total: string;
}

interface Props extends PageProps {
    filters: { from: string; to: string };
    stats: {
        revenue: string;
        total_orders: number;
        pending_orders: number;
        new_customers: number;
        total_products: number;
        low_stock: number;
        out_of_stock: number;
    };
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
    router.get(
        "/admin/dashboard",
        { from: fromDate.value, to: toDate.value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
});

// Badge helper
function badgeClass(status: string): string {
    switch (status) {
        case "paid":
        case "delivered":
            return "bg-success/10 text-success";
        case "pending":
            return "bg-warning/10 text-warning";
        case "processing":
            return "bg-info/10 text-info";
        default:
            return "bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-300";
    }
}

// Helper to map status names to specific Tailwind Hex colors
const getStatusColor = (status: string): string => {
    const s = status.toLowerCase();
    if (s.includes("paid") || s.includes("deliver") || s.includes("complete"))
        return "#13DEB9"; // Success
    if (s.includes("pending") || s.includes("await")) return "#f59e0b"; // Warning
    if (s.includes("process") || s.includes("ship")) return "#5D87FF"; // Info
    if (s.includes("fail") || s.includes("cancel") || s.includes("refund"))
        return "#ef4444"; // Error
    return "#64748b"; // Default Slate
};

// --- ApexCharts Configuration ---

// 1. Revenue Area Chart
const revenueSeries = computed(() => [
    { name: "Revenue", data: props.charts.revenue_trend.map((d) => d.total) },
]);
const revenueOptions = computed(() => ({
    chart: {
        type: "area",
        height: 300,
        fontFamily: "Geologica",
        toolbar: { show: false },
    },
    colors: ["#13DEB9"],
    dataLabels: { enabled: false },
    stroke: { curve: "smooth", width: 3 },
    fill: {
        type: "gradient",
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100, 100, 100],
        },
    },
    xaxis: {
        categories: props.charts.revenue_trend.map((d) => d.date),
        labels: { style: { colors: "#94a3b8" } },
    },
    yaxis: {
        labels: {
            formatter: (val: number) => `KES ${val.toFixed(0)}`,
            style: { colors: "#94a3b8" },
        },
    },
    grid: { show: false, borderColor: "#818cf8", strokeDashArray: 4 },
    tooltip: { y: { formatter: (val: number) => `KES ${val.toFixed(2)}` } },
}));

// 2. Order Status Donut

// 2. Order Status Bar Chart

const orderStatusData = computed(() =>
    props.charts.order_status.map((item) => ({
        status: item.status,
        count: Number(item.count),
    })),
);

const orderStatusSeries = computed(() => [
    {
        name: "Orders",
        data: orderStatusData.value.map((item) => item.count),
    },
]);

const orderStatusOptions = computed(() => ({
    chart: {
        type: "bar" as const,
        height: 250,
        fontFamily: "Geologica",
        toolbar: { show: false },
    },

    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: "55%",
            borderRadius: 5,
            distributed: true,
        },
    },

    colors: orderStatusData.value.map((item) => getStatusColor(item.status)),

    dataLabels: {
        enabled: false,
        formatter: (value: number) => value.toString(),
        style: {
            fontSize: "12px",
            fontWeight: 600,
        },
    },

    xaxis: {
        categories: orderStatusData.value.map((item) => item.status),
        labels: {
            style: {
                colors: "#94a3b8",
                fontSize: "11px",
            },
        },
        axisBorder: {
            show: true,
        },
        axisTicks: {
            show: true,
        },
    },

    yaxis: {
        min: 0,
        forceNiceScale: true,
        labels: {
            formatter: (value: number) => Math.round(value).toString(),
            style: {
                colors: "#94a3b8",
            },
        },
    },

    grid: {
        show: false,
        borderColor: "#e2e8f0",
        strokeDashArray: 4,
    },

    legend: {
        show: false,
    },

    tooltip: {
        y: {
            formatter: (value: number) => `${value} orders`,
        },
    },
}));

// 3. Payment Methods Donut
// 3. Payment Status Donut

const paymentStatusData = computed(() =>
    props.charts.payment_status.map((item) => ({
        status: item.status,
        count: Number(item.count),
    })),
);

const paymentSeries = computed(() =>
    paymentStatusData.value.map((item) => item.count),
);

const paymentOptions = computed(() => ({
    chart: {
        type: "donut",
        height: 320,
        fontFamily: "Geologica",
    },

    labels: paymentStatusData.value.map((item) => item.status),

    colors: paymentStatusData.value.map((item) => getStatusColor(item.status)),

    dataLabels: {
        enabled: true,
        formatter: (_percentage: number, opts: any) =>
            opts.w.globals.series[opts.seriesIndex],
        style: {
            fontSize: "12px",
            fontWeight: 600,
        },
        dropShadow: {
            enabled: false,
        },
    },

    legend: {
        show: true,
        position: "bottom",
        horizontalAlign: "left",
        formatter: (seriesName: string, opts: any) => {
            const count = opts.w.globals.series[opts.seriesIndex];
            return `${seriesName} (${count})`;
        },
    },

    plotOptions: {
        pie: {
            minAngleToShowLabel: 0,
            donut: {
                size: "75%",
                labels: {
                    show: true,
                    name: {
                        show: true,
                    },
                    value: {
                        show: true,
                        formatter: (value: string) => value,
                    },
                    total: {
                        show: true,
                        label: "Payments",
                        formatter: () =>
                            paymentStatusData.value
                                .reduce((total, item) => total + item.count, 0)
                                .toString(),
                    },
                },
            },
        },
    },
}));
</script>

<template>
    <Head title="Dashboard" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div
            class="p-6 border-0 shadow-md dark:shadow-dark-md mb-6 py-4 bg-primary/10 dark:bg-primary/10 overflow-hidden rounded-md border-none !shadow-none dark:!shadow-none"
            style="border-radius: 12px"
        >
            <div class="flex items-center justify-between gap-6 relative">
                <div>
                    <h4
                        class="font-semibold text-xl text-dark dark:text-white mb-3"
                    >
                        Analysis Center
                    </h4>
                    <ol
                        class="flex items-center whitespace-nowrap"
                        aria-label="Breadcrumb"
                    >
                        <li
                            class="flex items-center text-sm text-default dark:text-darklink leading-none"
                            aria-current="page"
                        >
                            <span
                                class="badge space-x-2.5 rounded-sm bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light"
                                >Showing Summary From {{ filters.from }} to
                                {{ filters.to }}</span
                            >
                        </li>
                    </ol>
                </div>
                <div
                    class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <MenuSVG />
                </div>
            </div>
        </div>

        <!-- Header & Date Filter -->
        <div
            class="flex flex-col gap-4 py-5 sm:flex-row sm:items-center sm:justify-between lg:py-6"
        >
            <div></div>
            <div class="flex items-center gap-2">
                <input
                    v-model="fromDate"
                    type="date"
                    class="form-input rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm focus:border-primary dark:border-navy-450 dark:focus:border-accent"
                />
                <span class="text-slate-400">to</span>
                <input
                    v-model="toDate"
                    type="date"
                    class="form-input rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm focus:border-primary dark:border-navy-450 dark:focus:border-accent"
                />
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Revenue -->
            <div
                class="rounded-xl bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800"
            >
                <div class="flex items-center justify-between">
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-navy-300"
                    >
                        Total Revenue
                    </p>
                    <div
                        class="flex size-12 p-2 items-center justify-center rounded-full bg-success/10 text-success"
                    >
                        <PaySVG />
                    </div>
                </div>
                <p
                    class="mt-4 text-2xl font-bold text-slate-700 dark:text-navy-100"
                >
                    KES {{ stats.revenue }}
                </p>
            </div>

            <!-- Orders -->
            <div
                class="rounded-xl bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800"
            >
                <div class="flex items-center justify-between">
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-navy-300"
                    >
                        Total Orders
                    </p>
                    <div
                        class="flex size-12 p-2 items-center justify-center rounded-full bg-primary/10 text-primary"
                    >
                        <CartSVG />
                    </div>
                </div>
                <p
                    class="mt-4 text-2xl font-bold text-slate-700 dark:text-navy-100"
                >
                    {{ stats.total_orders }}
                </p>
                <p
                    class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15"
                >
                    {{ stats.pending_orders }} pending fulfilment
                </p>
            </div>

            <!-- Customers -->
            <div
                class="rounded-xl bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800"
            >
                <div class="flex items-center justify-between">
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-navy-300"
                    >
                        New Customers
                    </p>
                    <div
                        class="flex size-12 p-2 items-center justify-center rounded-full bg-info/10 text-info"
                    >
                        <StripsSVG />
                    </div>
                </div>
                <p
                    class="mt-4 text-2xl font-bold text-slate-700 dark:text-navy-100"
                >
                    {{ stats.new_customers }}
                </p>
            </div>

            <!-- Inventory -->
            <div
                class="rounded-xl bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800"
            >
                <div class="flex items-center justify-between">
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-navy-300"
                    >
                        Inventory Health
                    </p>
                    <div
                        class="flex size-12 p-2 items-center justify-center rounded-full bg-error/10 text-error"
                    >
                        <ReportsSVG />
                    </div>
                </div>
                <p
                    class="mt-4 text-2xl font-bold text-slate-700 dark:text-navy-100"
                >
                    {{ stats.total_products }}
                    <span class="text-sm font-normal text-slate-400"
                        >products</span
                    >
                </p>
                <div class="mt-1 flex gap-3 text-xs text-center">
                    <span
                        class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15"
                        >{{ stats.low_stock }} low stock</span
                    >
                    <span
                        class="badge space-x-2.5 rounded-full bg-error/10 text-error dark:bg-error/15"
                        >{{ stats.out_of_stock }} out of stock</span
                    >
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-1">
            <!-- Revenue Trend Chart -->
            <div
                class="col-span-1 rounded-2xl bg-white p-5 dark:border-slate-700 dark:bg-navy-800 lg:col-span-2"
            >
                <h3 class="mb-4 font-medium text-slate-700 dark:text-navy-100">
                    Revenue Trend
                </h3>
                <VueApexCharts
                    type="area"
                    height="300"
                    :options="revenueOptions"
                    :series="revenueSeries"
                />
            </div>

            <!-- Order Status Donut -->
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Payment Status Donut (Replaced Payment Providers) -->
            <div
                class="w-full rounded-2xl bg-white p-5 dark:border-slate-700 dark:bg-navy-800 lg:w-1/2"
            >
                <div class="flex items-center gap-2.5">
                    <div
                        class="rounded-lg size-10 bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g
                                id="SVGRepo_tracerCarrier"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            ></g>
                            <g id="SVGRepo_iconCarrier">
                                <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                <path
                                    opacity="0.4"
                                    d="M18.8105 6.24998H20.1706C19.9806 5.97998 19.7806 5.72998 19.5706 5.47998L18.8105 6.24998Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    opacity="0.4"
                                    d="M18.52 4.41982C18.27 4.20982 18.02 4.00982 17.75 3.81982V5.17982L18.52 4.41982Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    opacity="0.4"
                                    d="M17 7.75C16.9 7.75 16.81 7.73 16.71 7.69C16.53 7.61 16.38 7.47 16.3 7.28C16.26 7.19 16.24 7.09 16.24 6.99V2.99C16.24 2.98 16.25 2.97 16.25 2.95C14.96 2.35 13.52 2 12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 10.48 21.65 9.04 21.04 7.74C21.03 7.74 21.02 7.75 21 7.75H17Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    d="M19.5795 5.47994L22.5295 2.52994C22.8195 2.23994 22.8195 1.75994 22.5295 1.46994C22.2395 1.17994 21.7595 1.17994 21.4695 1.46994L18.5195 4.41994C18.8995 4.74994 19.2495 5.10994 19.5795 5.47994Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    d="M17.7498 3C17.7498 2.59 17.4098 2.25 16.9998 2.25C16.5998 2.25 16.2798 2.57 16.2598 2.96C16.7798 3.21 17.2798 3.49 17.7498 3.82V3Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    d="M21.7499 7C21.7499 6.59 21.4099 6.25 20.9999 6.25H20.1699C20.4999 6.72 20.7899 7.22 21.0299 7.74C21.4299 7.72 21.7499 7.4 21.7499 7Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    d="M13.75 11.82L12.75 11.47V9.25H12.83C13.34 9.25 13.75 9.7 13.75 10.25C13.75 10.66 14.09 11 14.5 11C14.91 11 15.25 10.66 15.25 10.25C15.25 8.87 14.17 7.75 12.83 7.75H12.75V7.5C12.75 7.09 12.41 6.75 12 6.75C11.59 6.75 11.25 7.09 11.25 7.5V7.75H10.95C9.74001 7.75 8.75 8.77 8.75 10.03C8.75 11.49 9.6 11.96 10.25 12.19L11.25 12.54V14.76H11.17C10.66 14.76 10.25 14.31 10.25 13.76C10.25 13.35 9.91 13.01 9.5 13.01C9.09 13.01 8.75 13.35 8.75 13.76C8.75 15.14 9.83001 16.26 11.17 16.26H11.25V16.51C11.25 16.92 11.59 17.26 12 17.26C12.41 17.26 12.75 16.92 12.75 16.51V16.26H13.05C14.26 16.26 15.25 15.24 15.25 13.98C15.25 12.51 14.4 12.04 13.75 11.82ZM10.74 10.76C10.4 10.64 10.25 10.57 10.25 10.02C10.25 9.59 10.57 9.24 10.95 9.24H11.25V10.93L10.74 10.76ZM13.05 14.75H12.75V13.06L13.26 13.24C13.6 13.36 13.75 13.43 13.75 13.98C13.75 14.4 13.43 14.75 13.05 14.75Z"
                                    fill="#4f46e5"
                                ></path>
                            </g>
                        </svg>
                    </div>
                    <h2
                        class="text-base font-bold text-slate-900 dark:text-navy-50"
                    >
                        Payment Status
                    </h2>
                </div>
                <VueApexCharts
                    type="donut"
                    height="220"
                    :options="paymentOptions"
                    :series="paymentSeries"
                />
                <span class="block h-px w-full bg-primary/10 my-1"></span>
                <div class="flex items-center gap-2.5 mt-3">
                    <div
                        class="rounded-lg size-10 bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g
                                id="SVGRepo_tracerCarrier"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            ></g>
                            <g id="SVGRepo_iconCarrier">
                                <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                <path
                                    opacity="0.4"
                                    d="M18.8105 6.24998H20.1706C19.9806 5.97998 19.7806 5.72998 19.5706 5.47998L18.8105 6.24998Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    opacity="0.4"
                                    d="M18.52 4.41982C18.27 4.20982 18.02 4.00982 17.75 3.81982V5.17982L18.52 4.41982Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    opacity="0.4"
                                    d="M17 7.75C16.9 7.75 16.81 7.73 16.71 7.69C16.53 7.61 16.38 7.47 16.3 7.28C16.26 7.19 16.24 7.09 16.24 6.99V2.99C16.24 2.98 16.25 2.97 16.25 2.95C14.96 2.35 13.52 2 12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 10.48 21.65 9.04 21.04 7.74C21.03 7.74 21.02 7.75 21 7.75H17Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    d="M19.5795 5.47994L22.5295 2.52994C22.8195 2.23994 22.8195 1.75994 22.5295 1.46994C22.2395 1.17994 21.7595 1.17994 21.4695 1.46994L18.5195 4.41994C18.8995 4.74994 19.2495 5.10994 19.5795 5.47994Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    d="M17.7498 3C17.7498 2.59 17.4098 2.25 16.9998 2.25C16.5998 2.25 16.2798 2.57 16.2598 2.96C16.7798 3.21 17.2798 3.49 17.7498 3.82V3Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    d="M21.7499 7C21.7499 6.59 21.4099 6.25 20.9999 6.25H20.1699C20.4999 6.72 20.7899 7.22 21.0299 7.74C21.4299 7.72 21.7499 7.4 21.7499 7Z"
                                    fill="#4f46e5"
                                ></path>
                                <path
                                    d="M13.75 11.82L12.75 11.47V9.25H12.83C13.34 9.25 13.75 9.7 13.75 10.25C13.75 10.66 14.09 11 14.5 11C14.91 11 15.25 10.66 15.25 10.25C15.25 8.87 14.17 7.75 12.83 7.75H12.75V7.5C12.75 7.09 12.41 6.75 12 6.75C11.59 6.75 11.25 7.09 11.25 7.5V7.75H10.95C9.74001 7.75 8.75 8.77 8.75 10.03C8.75 11.49 9.6 11.96 10.25 12.19L11.25 12.54V14.76H11.17C10.66 14.76 10.25 14.31 10.25 13.76C10.25 13.35 9.91 13.01 9.5 13.01C9.09 13.01 8.75 13.35 8.75 13.76C8.75 15.14 9.83001 16.26 11.17 16.26H11.25V16.51C11.25 16.92 11.59 17.26 12 17.26C12.41 17.26 12.75 16.92 12.75 16.51V16.26H13.05C14.26 16.26 15.25 15.24 15.25 13.98C15.25 12.51 14.4 12.04 13.75 11.82ZM10.74 10.76C10.4 10.64 10.25 10.57 10.25 10.02C10.25 9.59 10.57 9.24 10.95 9.24H11.25V10.93L10.74 10.76ZM13.05 14.75H12.75V13.06L13.26 13.24C13.6 13.36 13.75 13.43 13.75 13.98C13.75 14.4 13.43 14.75 13.05 14.75Z"
                                    fill="#4f46e5"
                                ></path>
                            </g>
                        </svg>
                    </div>
                    <h2
                        class="text-base font-bold text-slate-900 dark:text-navy-50"
                    >
                        Order Fulfillment
                    </h2>
                </div>
                <VueApexCharts
                    type="bar"
                    height="250"
                    :options="orderStatusOptions"
                    :series="orderStatusSeries"
                />
            </div>

            <!-- Recent Activity Log -->
            <div
                class="w-full rounded-2xl bg-white p-5 dark:border-slate-700 dark:bg-navy-800 lg:w-1/2"
            >
                <h3 class="mb-4 font-medium text-slate-700 dark:text-navy-100">
                    Recent Admin Activity
                </h3>
                <div class="space-y-6">
                    <div
                        v-for="(event, index) in recentActivity"
                        :key="event.id"
                        class="group relative"
                    >
                        <!-- Divider (except last) -->
                        <div
                            v-if="index !== recentActivity.length - 1"
                            class="absolute -bottom-3 left-0 h-px w-full bg-slate-100 dark:bg-navy-700"
                        ></div>

                        <div class="flex gap-4">
                            <div class="flex flex-col items-center">
                                <div
                                    class="rounded-full size-10 bg-primary/10 p-2 text-primary dark:bg-accent-light/10 dark:text-accent-light"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <g
                                            id="SVGRepo_bgCarrier"
                                            stroke-width="0"
                                        ></g>
                                        <g
                                            id="SVGRepo_tracerCarrier"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        ></g>
                                        <g id="SVGRepo_iconCarrier">
                                            <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
                                            <path
                                                d="M16.7502 3.56V2C16.7502 1.59 16.4102 1.25 16.0002 1.25C15.5902 1.25 15.2502 1.59 15.2502 2V3.5H8.75023V2C8.75023 1.59 8.41023 1.25 8.00023 1.25C7.59023 1.25 7.25023 1.59 7.25023 2V3.56C4.55023 3.81 3.24023 5.42 3.04023 7.81C3.02023 8.1 3.26023 8.34 3.54023 8.34H20.4602C20.7502 8.34 20.9902 8.09 20.9602 7.81C20.7602 5.42 19.4502 3.81 16.7502 3.56Z"
                                                fill="#4f46e5"
                                            ></path>
                                            <path
                                                opacity="0.4"
                                                d="M20 9.84009C20.55 9.84009 21 10.2901 21 10.8401V17.0001C21 20.0001 19.5 22.0001 16 22.0001H8C4.5 22.0001 3 20.0001 3 17.0001V10.8401C3 10.2901 3.45 9.84009 4 9.84009H20Z"
                                                fill="#4f46e5"
                                            ></path>
                                            <path
                                                d="M8.5 14.9999C8.24 14.9999 7.98 14.8899 7.79 14.7099C7.61 14.5199 7.5 14.2599 7.5 13.9999C7.5 13.7399 7.61 13.4799 7.79 13.2899C8.07 13.0099 8.51 12.9199 8.88 13.0799C9.01 13.1299 9.12 13.1999 9.21 13.2899C9.39 13.4799 9.5 13.7399 9.5 13.9999C9.5 14.2599 9.39 14.5199 9.21 14.7099C9.02 14.8899 8.76 14.9999 8.5 14.9999Z"
                                                fill="#4f46e5"
                                            ></path>
                                            <path
                                                d="M12 14.9999C11.74 14.9999 11.48 14.8899 11.29 14.7099C11.11 14.5199 11 14.2599 11 13.9999C11 13.7399 11.11 13.4799 11.29 13.2899C11.38 13.1999 11.49 13.1299 11.62 13.0799C11.99 12.9199 12.43 13.0099 12.71 13.2899C12.89 13.4799 13 13.7399 13 13.9999C13 14.2599 12.89 14.5199 12.71 14.7099C12.66 14.7499 12.61 14.7899 12.56 14.8299C12.5 14.8699 12.44 14.8999 12.38 14.9199C12.32 14.9499 12.26 14.9699 12.2 14.9799C12.13 14.9899 12.07 14.9999 12 14.9999Z"
                                                fill="#4f46e5"
                                            ></path>
                                            <path
                                                d="M15.5 15C15.24 15 14.98 14.89 14.79 14.71C14.61 14.52 14.5 14.26 14.5 14C14.5 13.74 14.61 13.48 14.79 13.29C14.89 13.2 14.99 13.13 15.12 13.08C15.3 13 15.5 12.98 15.7 13.02C15.76 13.03 15.82 13.05 15.88 13.08C15.94 13.1 16 13.13 16.06 13.17C16.11 13.21 16.16 13.25 16.21 13.29C16.39 13.48 16.5 13.74 16.5 14C16.5 14.26 16.39 14.52 16.21 14.71C16.16 14.75 16.11 14.79 16.06 14.83C16 14.87 15.94 14.9 15.88 14.92C15.82 14.95 15.76 14.97 15.7 14.98C15.63 14.99 15.56 15 15.5 15Z"
                                                fill="#4f46e5"
                                            ></path>
                                            <path
                                                d="M8.5 18.5C8.37 18.5 8.24 18.47 8.12 18.42C7.99 18.37 7.89 18.3 7.79 18.21C7.61 18.02 7.5 17.76 7.5 17.5C7.5 17.24 7.61 16.98 7.79 16.79C7.89 16.7 7.99 16.63 8.12 16.58C8.3 16.5 8.5 16.48 8.7 16.52C8.76 16.53 8.82 16.55 8.88 16.58C8.94 16.6 9 16.63 9.06 16.67C9.11 16.71 9.16 16.75 9.21 16.79C9.39 16.98 9.5 17.24 9.5 17.5C9.5 17.76 9.39 18.02 9.21 18.21C9.16 18.25 9.11 18.3 9.06 18.33C9 18.37 8.94 18.4 8.88 18.42C8.82 18.45 8.76 18.47 8.7 18.48C8.63 18.49 8.57 18.5 8.5 18.5Z"
                                                fill="#4f46e5"
                                            ></path>
                                            <path
                                                d="M12 18.4999C11.74 18.4999 11.48 18.3899 11.29 18.2099C11.11 18.0199 11 17.7599 11 17.4999C11 17.2399 11.11 16.98 11.29 16.79C11.66 16.42 12.34 16.42 12.71 16.79C12.89 16.98 13 17.2399 13 17.4999C13 17.7599 12.89 18.0199 12.71 18.2099C12.52 18.3899 12.26 18.4999 12 18.4999Z"
                                                fill="#4f46e5"
                                            ></path>
                                            <path
                                                d="M15.5 18.4999C15.24 18.4999 14.98 18.3899 14.79 18.2099C14.61 18.0199 14.5 17.7599 14.5 17.4999C14.5 17.2399 14.61 16.98 14.79 16.79C15.16 16.42 15.84 16.42 16.21 16.79C16.39 16.98 16.5 17.2399 16.5 17.4999C16.5 17.7599 16.39 18.0199 16.21 18.2099C16.02 18.3899 15.76 18.4999 15.5 18.4999Z"
                                                fill="#4f46e5"
                                            ></path>
                                        </g>
                                    </svg>
                                </div>
                            </div>
                            <div class="pb-2">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                        >{{ event.user_name }}</span
                                    >
                                    <span
                                        class="mt-0.5 text-[11px] font-medium tracking-widest text-slate-400"
                                        >{{ event.created_at }}</span
                                    >
                                </div>
                                <div
                                    class="mt-1.5 inline-block rounded-2xl rounded-tl-sm bg-primary/10 px-4 py-2.5 dark:bg-navy-700"
                                >
                                    <p
                                        class="text-sm text-slate-700 dark:text-navy-200"
                                    >
                                        {{ event.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent Orders -->
            <div
                class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800"
            >
                <!-- Card Header -->
                <div
                    class="flex items-center justify-between border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]"
                >
                    <div>
                        <h3
                            class="text-sm font-semibold text-slate-700 dark:text-navy-100"
                        >
                            Recent Orders
                        </h3>
                        <p
                            class="mt-0.5 text-xs text-slate-400 dark:text-navy-300"
                        >
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
                    <table
                        class="w-full table-fixed border-collapse text-left text-sm"
                    >
                        <thead>
                            <tr
                                class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300"
                            >
                                <th
                                    class="w-1/4 border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Order
                                </th>
                                <th
                                    class="w-1/4 border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Customer
                                </th>
                                <th
                                    class="w-1/4 border border-slate-150 px-5 py-4 font-bold text-right dark:border-slate-700"
                                >
                                    Total
                                </th>
                                <th
                                    class="w-1/6 border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-150 dark:divide-slate-150/15"
                        >
                            <tr
                                v-for="order in recentOrders"
                                :key="order.ulid"
                                class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                            >
                                <!-- Order -->
                                <td
                                    class="border border-slate-150 px-5 py-3 align-top dark:border-slate-700"
                                >
                                    <p
                                        class="break-words text-xs font-bold text-slate-700 dark:text-navy-100"
                                    >
                                        {{ order.order_number }}
                                    </p>

                                    <span
                                        :class="
                                            badgeClass(order.payment_status)
                                        "
                                        class="mt-1 inline-flex max-w-full rounded-full px-2 py-0.5 text-[10px] font-semibold capitalize"
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
                                    <div
                                        class="max-w-full overflow-x-auto whitespace-nowrap font-semibold text-right text-slate-700 dark:text-navy-100"
                                    >
                                        {{ order.grand_total }}
                                    </div>
                                </td>

                                <!-- Action -->
                                <td
                                    class="border border-slate-150 px-5 py-3 text-left align-top dark:border-slate-700"
                                >
                                    <Link
                                        :href="
                                            admin_orders_show.url(order.ulid)
                                        "
                                        class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15 transition-all duration-200 hover:scale-x-105"
                                    >
                                        <span>View</span>
                                        <ArrowRight
                                            class="size-3.5 transition-transform duration-200 group-hover:translate-x-1"
                                        />
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
                                            <icon
                                                name="shopping-bag"
                                                class="size-5"
                                            />
                                        </div>

                                        <p
                                            class="font-medium text-slate-600 dark:text-navy-200"
                                        >
                                            No orders found.
                                        </p>
                                        <p
                                            class="mt-1 text-xs text-slate-400 dark:text-navy-400"
                                        >
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
                class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800"
            >
                <!-- Card Header -->
                <div
                    class="flex items-center justify-between border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]"
                >
                    <div>
                        <h3
                            class="text-sm font-semibold text-slate-700 dark:text-navy-100"
                        >
                            Top Selling Products
                        </h3>
                        <p
                            class="mt-0.5 text-xs text-slate-400 dark:text-navy-300"
                        >
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
                    <table
                        class="w-full table-fixed border-collapse text-left text-sm"
                    >
                        <thead>
                            <tr
                                class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300"
                            >
                                <th
                                    class="w-1/3 border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Product
                                </th>
                                <th
                                    class="w-1/4 border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Sold
                                </th>
                                <th
                                    class="w-1/3 border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700"
                                >
                                    Revenue
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-150 dark:divide-slate-150/15"
                        >
                            <tr
                                v-for="(product, index) in topProducts"
                                :key="index"
                                class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                            >
                                <!-- Product -->
                                <td
                                    class="break-words border border-slate-150 px-5 py-3 align-top dark:border-slate-700"
                                >
                                    <p
                                        class="font-semibold text-slate-700 dark:text-navy-100"
                                    >
                                        {{ product.product_name }}
                                    </p>
                                    <p
                                        class="mt-1 break-all text-2xs text-slate-400 dark:text-navy-400"
                                    >
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
                                    <div
                                        class="max-w-full overflow-x-auto whitespace-nowrap font-semibold tabular-nums text-success"
                                    >
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
                                            <icon
                                                name="package"
                                                class="size-5"
                                            />
                                        </div>

                                        <p
                                            class="font-medium text-slate-600 dark:text-navy-200"
                                        >
                                            No sales found.
                                        </p>
                                        <p
                                            class="mt-1 text-xs text-slate-400 dark:text-navy-400"
                                        >
                                            Top selling products will appear
                                            here.
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
