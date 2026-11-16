<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { PageProps } from '@/types';
import { admin_orders_index, admin_orders_show } from '@/routes';

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

interface StatusOption { value: string; label: string }

interface Props extends PageProps {
    orders: {
        data: OrderRow[];
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search: string | null; payment_status: string | null; fulfilment_status: string | null };
    stats: { total: number; pending_payment: number; paid: number; processing: number };
    paymentStatuses: StatusOption[];
    fulfilmentStatuses: StatusOption[];
}

const props = defineProps<Props>();

const search = ref(props.filters.search ?? '');
const paymentStatus = ref(props.filters.payment_status ?? '');
const fulfilmentStatus = ref(props.filters.fulfilment_status ?? '');

watch([search, paymentStatus, fulfilmentStatus], () => {
    router.get(admin_orders_index.url(), {
        search: search.value || undefined,
        payment_status: paymentStatus.value || undefined,
        fulfilment_status: fulfilmentStatus.value || undefined,
    }, { preserveState: true, replace: true });
});

function badgeClass(status: string): string {
    switch (status) {
        case 'paid':
        case 'delivered':
            return 'badge bg-success/10 text-success dark:bg-success/15';
        case 'pending':
            return 'badge bg-warning/10 text-warning dark:bg-warning/15';
        case 'processing':
            return 'badge bg-info/10 text-info dark:bg-info/15';
        case 'failed':
        case 'cancelled':
            return 'badge bg-error/10 text-error dark:bg-error/15';
        default:
            return 'badge bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200';
    }
}
</script>

<template>

    <Head title="Orders" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    Orders
                </h2>
            </div>
        </div>

        <div class="space-y-4">
            <div class="mt-5 grid grid-cols-1 gap-4  sm:grid-cols-4 ">
                <div
                    class="relative flex flex-col overflow-hidden rounded-lg bg-gradient-to-br from-info to-info-focus p-3.5">
                    <p class="text-xs uppercase text-sky-100">Total</p>
                    <div class="flex items-end justify-between space-x-2">
                        <p class="mt-4 text-2xl font-medium text-white">{{ stats.total }}</p>

                    </div>
                    <div class="mask is-reuleaux-triangle absolute top-0 right-0 -m-3 size-16 bg-white/20"></div>
                </div>
                <div
                    class="relative flex flex-col overflow-hidden rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 p-3.5">
                    <p class="text-xs uppercase text-amber-50">Pending Payment</p>
                    <div class="flex items-end justify-between space-x-2">
                        <p class="mt-4 text-2xl font-medium text-white">{{ stats.pending_payment }}</p>

                    </div>
                    <div class="mask is-diamond absolute top-0 right-0 -m-3 size-16 bg-white/20"></div>
                </div>
                <div
                    class="relative flex flex-col overflow-hidden rounded-lg bg-gradient-to-br from-teal-500 to-teal-500 p-3.5">
                    <p class="text-xs uppercase text-pink-100">Paid</p>
                    <div class="flex items-end justify-between space-x-2">
                        <p class="mt-4 text-2xl font-medium text-white">{{ stats.paid }}</p>

                    </div>
                    <div class="mask is-hexagon-2 absolute top-0 right-0 -m-3 size-16 bg-white/20"></div>
                </div>
                <div
                    class="relative flex flex-col overflow-hidden rounded-lg bg-gradient-to-br from-pink-500 to-rose-500 p-3.5">
                    <p class="text-xs uppercase text-pink-100">Processing</p>
                    <div class="flex items-end justify-between space-x-2">
                        <p class="mt-4 text-2xl font-medium text-white">{{ stats.processing }}</p>

                    </div>
                    <div class="mask is-hexagon-2 absolute top-0 right-0 -m-3 size-16 bg-white/20"></div>
                </div>
            </div>
            

            <!-- Filters -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 flex-wrap gap-2">
                    <input v-model="search" placeholder="Order #, customer, email, phone…"
                        class="form-input flex-1 min-w-[240px] rounded-lg border border-slate-300 bg-transparent px-3.5 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                    <select v-model="paymentStatus"
                        class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                        <option value="">All payments</option>
                        <option v-for="s in paymentStatuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                    <select v-model="fulfilmentStatus"
                        class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                        <option value="">All fulfilment</option>
                        <option v-for="s in fulfilmentStatuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card overflow-hidden">
                <div class="is-scrollbar-x overflow-x-auto">
                    <table class="is-zebra w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th
                                    class="whitespace-nowrap rounded-l-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                                    Order #
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                                    Customer
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                                    Items
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                                    Total
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                                    Payment
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                                    Fulfilment
                                </th>
                                <th
                                    class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                                    Placed
                                </th>
                                <th
                                    class="whitespace-nowrap rounded-r-lg bg-slate-200 px-4 py-3 text-right font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in orders.data" :key="order.ulid">
                                <td
                                    class="whitespace-nowrap rounded-l-lg px-4 py-3 font-mono text-xs font-semibold text-slate-700 dark:text-navy-100 sm:px-5">
                                    {{ order.order_number }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                                    {{ order.customer_name }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                                    {{order.items.reduce((s, i) => s + i.quantity, 0)}}
                                </td>
                                <td
                                    class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700 dark:text-navy-100 sm:px-5">
                                    KES {{ order.grand_total }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <span :class="badgeClass(order.payment_status)">{{ order.payment_status }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <span :class="badgeClass(order.fulfilment_status)">{{ order.fulfilment_status
                                    }}</span>
                                </td>
                                <td
                                    class="whitespace-nowrap px-4 py-3 text-xs text-slate-400 dark:text-navy-300 sm:px-5">
                                    {{ order.created_at }}
                                </td>
                                <td class="whitespace-nowrap rounded-r-lg px-4 py-3 text-right sm:px-5">
                                    <Link :href="admin_orders_show.url(order.ulid)"
                                        class="font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">
                                        View
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="orders.data.length === 0">
                                <td colspan="8" class="px-4 py-8 text-center text-slate-400 dark:text-navy-300">
                                    No orders found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="orders.links.length > 3"
                    class="flex items-center justify-between border-t border-slate-200 px-4 py-4 dark:border-navy-600 sm:px-5">
                    <nav class="flex flex-wrap gap-1">
                        <template v-for="link in orders.links" :key="link.label">
                            <Link v-if="link.url" :href="link.url"
                                class="btn min-w-[2rem] rounded-lg border px-3 py-1.5 text-xs font-medium" :class="link.active
                                    ? 'border-primary bg-primary text-white dark:border-accent dark:bg-accent'
                                    : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100 dark:border-navy-450 dark:bg-navy-750 dark:text-navy-100 dark:hover:bg-navy-700'
                                    " v-html="link.label" />
                            <span v-else
                                class="btn min-w-[2rem] rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs text-slate-400 dark:border-navy-600 dark:bg-navy-800 dark:text-navy-400"
                                v-html="link.label" />
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>