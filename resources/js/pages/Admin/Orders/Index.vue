<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import type { PageProps } from "@/types";
import {
    admin_dashboard,
    admin_orders_index,
    admin_orders_show,
} from "@/routes";
import Pagination from "@/components/Pagination.vue";
import { HistoryIcon } from "@lucide/vue";
import PackageSVG from "@/components/SVG/packageSVG.vue";
import MenuSVG from "@/components/SVG/menuSVG.vue";
import CartSVG from "@/components/SVG/cartSVG.vue";

interface OrderRow {
    ulid: string;
    order_number: string;
    customer_name: string;
    grand_total: string;
    payment_status: string;
    fulfilment_status: string;
    created_at: string;
    items: { quantity: number }[];
}

interface StatusOption {
    value: string;
    label: string;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface Props extends PageProps {
    orders: Paginator<OrderRow>;
    filters: {
        search: string | null;
        payment_status: string | null;
        fulfilment_status: string | null;
    };
    stats: {
        total: number;
        pending_payment: number;
        paid: number;
        processing: number;
    };
    paymentStatuses: StatusOption[];
    fulfilmentStatuses: StatusOption[];
}

const props = defineProps<Props>();

const search = ref(props.filters.search ?? "");
const paymentStatus = ref(props.filters.payment_status ?? "");
const fulfilmentStatus = ref(props.filters.fulfilment_status ?? "");

watch([search, paymentStatus, fulfilmentStatus], () => {
    router.get(
        admin_orders_index.url(),
        {
            search: search.value || undefined,
            payment_status: paymentStatus.value || undefined,
            fulfilment_status: fulfilmentStatus.value || undefined,
        },
        { preserveState: true, replace: true },
    );
});

function badgeClass(status: string): string {
    switch (status) {
        case "paid":
        case "delivered":
            return "badge bg-success/10 text-success dark:bg-success/15";
        case "pending":
            return "badge bg-warning/10 text-warning dark:bg-warning/15";
        case "processing":
            return "badge bg-info/10 text-info dark:bg-info/15";
        case "failed":
        case "cancelled":
            return "badge bg-error/10 text-error dark:bg-error/15";
        default:
            return "badge bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200";
    }
}

function formatDate(value: string | null | undefined): string {
    if (!value) return "—";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return "—";

    const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    const pad = (num: number) => String(num).padStart(2, "0");

    return `${days[date.getDay()]} ${pad(date.getDate())}-${pad(date.getMonth() + 1)}-${date.getFullYear()} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}
</script>

<template>
    <Head title="Orders" />

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
                        Orders
                    </h4>
                    <ol
                        class="flex items-center whitespace-nowrap"
                        aria-label="Breadcrumb"
                    >
                        <li class="flex items-center">
                            <Link
                                class="opacity-80 text-sm text-link dark:text-darklink leading-none"
                                :href="admin_dashboard.url()"
                                data-discover="true"
                                >Home</Link
                            >
                        </li>
                        <li>
                            <div
                                class="p-0.5 rounded-full bg-primary/10 dark:bg-primary mx-2.5 flex items-center"
                            ></div>
                        </li>
                        <li
                            class="flex items-center text-sm text-default dark:text-darklink leading-none"
                            aria-current="page"
                        >
                            Orders
                        </li>
                    </ol>
                </div>
                <div
                    class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <CartSVG />
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <!-- Stats Cards -->
            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div
                    class="relative flex flex-col overflow-hidden rounded-lg bg-gradient-to-br from-info to-info-focus p-3.5"
                >
                    <p class="text-xs uppercase text-sky-100">Orders</p>
                    <div class="flex items-end justify-between space-x-2">
                        <p class="mt-4 text-2xl font-medium text-white">
                            {{ stats.total }}
                        </p>
                    </div>
                    <div
                        class="mask is-reuleaux-triangle absolute top-0 right-0 -m-3 size-16 bg-white/20"
                    ></div>
                </div>
                <div
                    class="relative flex flex-col overflow-hidden rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 p-3.5"
                >
                    <p class="text-xs uppercase text-amber-50">
                        Pending Payment
                    </p>
                    <div class="flex items-end justify-between space-x-2">
                        <p class="mt-4 text-2xl font-medium text-white">
                            {{ stats.pending_payment }}
                        </p>
                    </div>
                    <div
                        class="mask is-diamond absolute top-0 right-0 -m-3 size-16 bg-white/20"
                    ></div>
                </div>
                <div
                    class="relative flex flex-col overflow-hidden rounded-lg bg-gradient-to-br from-teal-500 to-teal-500 p-3.5"
                >
                    <p class="text-xs uppercase text-pink-100">Paid</p>
                    <div class="flex items-end justify-between space-x-2">
                        <p class="mt-4 text-2xl font-medium text-white">
                            {{ stats.paid }}
                        </p>
                    </div>
                    <div
                        class="mask is-hexagon-2 absolute top-0 right-0 -m-3 size-16 bg-white/20"
                    ></div>
                </div>
                <div
                    class="relative flex flex-col overflow-hidden rounded-lg bg-gradient-to-br from-pink-500 to-rose-500 p-3.5"
                >
                    <p class="text-xs uppercase text-pink-100">Processing</p>
                    <div class="flex items-end justify-between space-x-2">
                        <p class="mt-4 text-2xl font-medium text-white">
                            {{ stats.processing }}
                        </p>
                    </div>
                    <div
                        class="mask is-hexagon-2 absolute top-0 right-0 -m-3 size-16 bg-white/20"
                    ></div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex flex-1 flex-wrap gap-2">
                    <input
                        v-model="search"
                        placeholder="Order #, customer, email, phone…"
                        class="form-input flex-1 min-w-[240px] rounded-lg border border-slate-300 bg-transparent px-3.5 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    />
                    <select
                        v-model="paymentStatus"
                        class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    >
                        <option value="">All payments</option>
                        <option
                            v-for="s in paymentStatuses"
                            :key="s.value"
                            :value="s.value"
                        >
                            {{ s.label }}
                        </option>
                    </select>
                    <select
                        v-model="fulfilmentStatus"
                        class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    >
                        <option value="">All fulfilment</option>
                        <option
                            v-for="s in fulfilmentStatuses"
                            :key="s.value"
                            :value="s.value"
                        >
                            {{ s.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Table Card -->
            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300"
                            >
                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Date
                                </th>
                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Order #
                                </th>
                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Customer
                                </th>
                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Items
                                </th>
                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Total
                                </th>
                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Payment
                                </th>
                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Fulfilment
                                </th>

                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-150 dark:divide-slate-150/15"
                        >
                            <tr
                                v-for="order in orders.data"
                                :key="order.ulid"
                                class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                            >
                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-3.5 align-middle dark:border-slate-700"
                                >
                                    <span
                                        class="badge space-x-1 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light"
                                    >
                                        <HistoryIcon class="size-3" />
                                    </span>
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-3 text-xs font-bold text-slate-700 dark:border-slate-700 dark:text-navy-100"
                                >
                                    {{ order.order_number }}
                                </td>

                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-3 font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100"
                                >
                                    {{ order.customer_name }}
                                </td>

                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200"
                                >
                                    {{
                                        order.items.reduce(
                                            (s, i) => s + i.quantity,
                                            0,
                                        )
                                    }}
                                </td>

                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-3 font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100"
                                >
                                    KES {{ order.grand_total }}
                                </td>

                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700"
                                >
                                    <span
                                        :class="
                                            badgeClass(order.payment_status)
                                        "
                                        class="capitalize"
                                    >
                                        {{ order.payment_status }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700"
                                >
                                    <span
                                        :class="
                                            badgeClass(order.fulfilment_status)
                                        "
                                        class="capitalize"
                                    >
                                        {{ order.fulfilment_status }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-3 text-right dark:border-slate-700"
                                >
                                    <Link
                                        :href="
                                            admin_orders_show.url(order.ulid)
                                        "
                                        class="inline-flex items-center justify-center rounded-lg border border-primary/20 bg-primary/[0.06] px-3 py-2 text-xs font-semibold text-primary transition-colors hover:bg-primary/15 focus:bg-primary/15 active:bg-primary/20 dark:border-accent-light/20 dark:bg-accent-light/[0.06] dark:text-accent-light dark:hover:bg-accent-light/15"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="orders.data.length === 0">
                                <td
                                    colspan="8"
                                    class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300"
                                >
                                    No orders found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Unified Pagination Component -->
                <Pagination :paginator="orders" />
            </div>
        </div>
    </div>
</template>
