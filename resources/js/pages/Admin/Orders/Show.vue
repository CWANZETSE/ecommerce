<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_orders_index,
    admin_orders_notes_store,
    admin_orders_cancel,
    admin_orders_fulfilment
} from '@/routes';
import {
    Package,
    ArrowLeft,
    ShoppingBag,
    History,
    Activity,
    ArrowRight,
    UserRound,
    Mail,
    Phone,
    MapPin,
    MessageSquare,
    Receipt,
    Wallet,
    Truck,
    LoaderCircle,
    Save,
    NotebookPen,
    Plus,
    Ban,
} from '@lucide/vue';

interface StatusOption { value: string; label: string }
interface OrderItem {
    id: string;
    product_name: string;
    sku: string;
    attributes: { name: string; value: string }[];
    unit_price: string;
    quantity: number;
    line_total: string;
}
interface Payment {
    id: string;
    ulid: string;
    provider: string;
    amount: string;
    status: string;
    provider_reference: string | null;
    checkout_request_id: string | null;
    failure_reason: string | null;
    paid_at: string | null;
    created_at: string;
}
interface History {
    id: string;
    from_status: string | null;
    to_status: string;
    type: string;
    note: string | null;
    created_at: string;
}
interface Order {
    id: string;
    ulid: string;
    order_number: string;
    customer_name: string;
    customer_email: string;
    customer_phone: string;
    shipping_address: { county: string; town: string; address: string; instructions?: string | null };
    subtotal: string;
    shipping_fee: string;
    grand_total: string;
    payment_status: string;
    fulfilment_status: string;
    paid_at: string | null;
    cancelled_at: string | null;
    created_at: string;
    items: OrderItem[];
    payments: Payment[];
    history: History[];
    discount_total: string;
    coupon_code: string | null;
}

const props = defineProps<PageProps & {
    order: Order;
    fulfilmentStatuses: StatusOption[];
}>();

const fulfilmentForm = useForm({
    status: props.order.fulfilment_status,
    note: '',
});

const noteForm = useForm({ note: '' });

function updateFulfilment() {
    fulfilmentForm.patch(admin_orders_fulfilment.url(props.order.ulid), {
        preserveScroll: true,
        onSuccess: () => { fulfilmentForm.note = ''; },
    });
}

function addNote() {
    noteForm.post(admin_orders_notes_store.url(props.order.ulid), {
        preserveScroll: true,
        onSuccess: () => { noteForm.note = ''; },
    });
}

function cancelOrder() {
    if (!confirm('Cancel this order? If it was paid, stock will be restored.')) return;
    router.post(admin_orders_cancel.url(props.order.ulid), {}, { preserveScroll: true });
}

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
    <Head :title="`Order ${order.order_number}`" />

    <div class="p-4 sm:p-5 lg:p-6">
        <!-- Page Header -->
        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex items-start gap-3">
                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <Package class="size-5" />
                </div>

                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-semibold tracking-tight text-slate-800 dark:text-navy-50 lg:text-2xl">
                            Order {{ order.order_number }}
                        </h2>

                        <span
                            class="rounded-full bg-primary/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-primary"
                        >
                            {{ order.fulfilment_status }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-navy-300">
                        Placed {{ order.created_at }}
                    </p>
                </div>
            </div>

            <Link
                :href="admin_orders_index.url()"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:border-primary/30 hover:bg-primary/5 hover:text-primary dark:border-navy-600 dark:bg-navy-800 dark:text-navy-200 dark:hover:bg-navy-700"
            >
                <ArrowLeft class="size-4" />
                <span>Back to orders</span>
            </Link>
        </div>

        <!-- Order Content -->
        <div class="grid gap-6 lg:grid-cols-3">

            <!-- Main Content -->
            <div class="min-w-0 space-y-6 lg:col-span-2">

                <!-- Order Items -->
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-navy-600 dark:bg-navy-800">
                    <div class="flex items-center justify-between border-b border-slate-200/80 px-4 py-4 dark:border-navy-600 sm:px-5">
                        <div>
                            <h3 class="font-semibold text-slate-800 dark:text-navy-50">
                                Order items
                            </h3>
                            <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                                {{ order.items.length }} item(s) in this order
                            </p>
                        </div>

                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <ShoppingBag class="size-4" />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr class="border-b border-slate-200/80 bg-primary/5 dark:border-navy-600 dark:bg-primary/10">
                                    <th class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wide text-primary/70 sm:px-5">
                                        Product
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wide text-primary/70 sm:px-5">
                                        Unit price
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wide text-primary/70 sm:px-5">
                                        Qty
                                    </th>
                                    <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-primary/70 sm:px-5">
                                        Total
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 dark:divide-navy-600">
                                <tr
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-navy-700/40"
                                >
                                    <td class="px-4 py-4 sm:px-5">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-navy-50">
                                            {{ item.product_name }}
                                        </p>

                                        <p class="mt-0.5 font-mono text-[11px] text-slate-400 dark:text-navy-300">
                                            {{ item.sku }}
                                        </p>

                                        <div
                                            v-if="item.attributes?.length"
                                            class="mt-2 flex flex-wrap gap-1.5"
                                        >
                                            <span
                                                v-for="(a, i) in item.attributes"
                                                :key="i"
                                                class="inline-flex items-center rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-medium text-slate-500 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-200"
                                            >
                                                {{ a.name }}: {{ a.value }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600 dark:text-navy-200 sm:px-5">
                                        KES {{ item.unit_price }}
                                    </td>

                                    <td class="px-4 py-4 sm:px-5">
                                        <span class="inline-flex min-w-8 items-center justify-center rounded-lg bg-primary/10 px-2 py-1 text-xs font-semibold text-primary">
                                            {{ item.quantity }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-slate-800 dark:text-navy-50 sm:px-5">
                                        KES {{ item.line_total }}
                                    </td>
                                </tr>

                                <tr v-if="order.items.length === 0">
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400 dark:text-navy-300">
                                        No items found for this order.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Status History -->
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-navy-600 dark:bg-navy-800">
                    <div class="flex items-center justify-between border-b border-slate-200/80 px-4 py-4 dark:border-navy-600 sm:px-5">
                        <div>
                            <h3 class="font-semibold text-slate-800 dark:text-navy-50">
                                Status history
                            </h3>
                            <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">
                                Order activity and updates
                            </p>
                        </div>

                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <History class="size-4" />
                        </div>
                    </div>

                    <div class="p-4 sm:p-5">
                        <div v-if="order.history.length" class="space-y-0">
                            <div
                                v-for="(h, index) in order.history"
                                :key="h.id"
                                class="relative flex gap-3 pb-6 last:pb-0"
                            >
                                <div class="relative flex flex-col items-center">
                                    <div class="z-10 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                        <Activity class="size-4" />
                                    </div>

                                    <div
                                        v-if="index !== order.history.length - 1"
                                        class="absolute top-8 h-full w-px bg-slate-200 dark:bg-navy-600"
                                    />
                                </div>

                                <div class="min-w-0 flex-1 pb-1">
                                    <div class="flex flex-col justify-between gap-1 sm:flex-row sm:items-center">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-md bg-primary/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-primary">
                                                {{ h.type }}
                                            </span>

                                            <span class="text-xs text-slate-600 dark:text-navy-200">
                                                {{ h.from_status ?? '—' }}
                                                <ArrowRight class="mx-1 inline size-3" />
                                                <strong class="font-semibold text-slate-800 dark:text-navy-50">
                                                    {{ h.to_status }}
                                                </strong>
                                            </span>
                                        </div>

                                        <span class="text-[11px] text-slate-400 dark:text-navy-300">
                                            {{ h.created_at }}
                                        </span>
                                    </div>

                                    <p
                                        v-if="h.note"
                                        class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-xs leading-relaxed text-slate-500 dark:bg-navy-700/60 dark:text-navy-300"
                                    >
                                        {{ h.note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="py-8 text-center">
                            <History class="mx-auto size-8 text-slate-300 dark:text-navy-500" />
                            <p class="mt-2 text-sm text-slate-400 dark:text-navy-300">
                                No status history available yet.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="min-w-0 space-y-6">

                <!-- Customer -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-navy-600 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <UserRound class="size-4" />
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-navy-50">
                                Customer
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">
                                Customer information
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3 border-t border-slate-100 pt-3 dark:border-navy-600">
                        <div>
                            <p class="text-xs font-semibold text-slate-800 dark:text-navy-50">
                                {{ order.customer_name }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-navy-300">
                            <Mail class="size-3.5 shrink-0 text-slate-400" />
                            <span class="break-all">{{ order.customer_email }}</span>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-navy-300">
                            <Phone class="size-3.5 shrink-0 text-slate-400" />
                            <span>{{ order.customer_phone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-navy-600 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <MapPin class="size-4" />
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-navy-50">
                                Shipping address
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">
                                Delivery information
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2 border-t border-slate-100 pt-3 dark:border-navy-600">
                        <p class="text-sm font-semibold text-slate-800 dark:text-navy-50">
                            {{ order.shipping_address.county }} — {{ order.shipping_address.town }}
                        </p>

                        <p class="text-xs leading-relaxed text-slate-500 dark:text-navy-300">
                            {{ order.shipping_address.address }}
                        </p>

                        <div
                            v-if="order.shipping_address.instructions"
                            class="mt-3 rounded-lg bg-primary/5 p-3 text-xs italic leading-relaxed text-slate-500 dark:bg-primary/10 dark:text-navy-200"
                        >
                            <MessageSquare class="mr-1 inline size-3.5 text-primary" />
                            "{{ order.shipping_address.instructions }}"
                        </div>
                    </div>
                </div>

                <!-- Order Totals -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-navy-600 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <Receipt class="size-4" />
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-navy-50">
                                Order summary
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">
                                Payment breakdown
                            </p>
                        </div>
                    </div>

                    <dl class="space-y-3 border-t border-slate-100 pt-4 text-xs dark:border-navy-600">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500 dark:text-navy-300">Subtotal</dt>
                            <dd class="font-medium text-slate-700 dark:text-navy-100">
                                KES {{ order.subtotal }}
                            </dd>
                        </div>

                        <div
                            v-if="Number(order.discount_total) > 0"
                            class="flex items-center justify-between gap-3"
                        >
                            <dt class="text-success">
                                Discount
                                <span class="font-mono text-[10px]">({{ order.coupon_code }})</span>
                            </dt>
                            <dd class="font-semibold text-success">
                                − KES {{ order.discount_total }}
                            </dd>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500 dark:text-navy-300">Shipping</dt>
                            <dd class="font-medium text-slate-700 dark:text-navy-100">
                                KES {{ order.shipping_fee }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 rounded-xl bg-primary/5 p-3.5 dark:bg-primary/10">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-semibold text-slate-600 dark:text-navy-200">
                                Grand total
                            </span>

                            <span class="text-lg font-bold tracking-tight text-primary">
                                KES {{ order.grand_total }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-navy-600 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <Wallet class="size-4" />
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-navy-50">
                                Payment
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">
                                Transaction details
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 py-3 dark:border-navy-600">
                        <span class="text-xs text-slate-500 dark:text-navy-300">
                            Payment status
                        </span>

                        <span
                            :class="order.payment_status === 'paid'
                                ? 'bg-success/10 text-success'
                                : 'bg-warning/10 text-warning'"
                            class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide"
                        >
                            {{ order.payment_status }}
                        </span>
                    </div>

                    <div v-if="order.payments.length" class="space-y-3">
                        <div
                            v-for="p in order.payments"
                            :key="p.id"
                            class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 dark:border-navy-600 dark:bg-navy-700/40"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-semibold text-slate-700 dark:text-navy-100">
                                    {{ p.provider }}
                                </p>

                                <span
                                    :class="p.status === 'success' || p.status === 'completed'
                                        ? 'text-success'
                                        : p.status === 'failed'
                                            ? 'text-error'
                                            : 'text-warning'"
                                    class="text-[10px] font-semibold uppercase"
                                >
                                    {{ p.status }}
                                </span>
                            </div>

                            <p v-if="p.provider_reference" class="mt-2 text-[11px] text-slate-500 dark:text-navy-300">
                                Receipt:
                                <span class="font-mono text-slate-700 dark:text-navy-100">
                                    {{ p.provider_reference }}
                                </span>
                            </p>

                            <p v-if="p.failure_reason" class="mt-2 text-[11px] text-error">
                                {{ p.failure_reason }}
                            </p>

                            <p class="mt-2 text-[10px] text-slate-400 dark:text-navy-400">
                                {{ p.created_at }}
                            </p>
                        </div>
                    </div>

                    <p v-else class="py-4 text-center text-xs text-slate-400 dark:text-navy-300">
                        No payment transactions recorded.
                    </p>
                </div>

                <!-- Update Fulfilment -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-navy-600 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <Truck class="size-4" />
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-navy-50">
                                Update fulfilment
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">
                                Change order status
                            </p>
                        </div>
                    </div>

                    <form class="space-y-3 border-t border-slate-100 pt-4 dark:border-navy-600" @submit.prevent="updateFulfilment">
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-slate-600 dark:text-navy-200">
                                Fulfilment status
                            </label>

                            <select
                                v-model="fulfilmentForm.status"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition hover:border-primary/40 focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100"
                            >
                                <option v-for="s in fulfilmentStatuses" :key="s.value" :value="s.value">
                                    {{ s.label }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-slate-600 dark:text-navy-200">
                                Note
                                <span class="font-normal text-slate-400">(optional)</span>
                            </label>

                            <textarea
                                v-model="fulfilmentForm.note"
                                rows="3"
                                placeholder="Add a fulfilment note..."
                                class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-primary/40 focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100"
                            />
                        </div>

                        <p v-if="fulfilmentForm.errors.status" class="text-xs text-error">
                            {{ fulfilmentForm.errors.status }}
                        </p>

                        <button
                            type="submit"
                            :disabled="fulfilmentForm.processing"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-focus focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-accent dark:hover:bg-accent-focus"
                        >
                            <LoaderCircle v-if="fulfilmentForm.processing" class="size-4 animate-spin" />
                            <Save v-else class="size-4" />

                            {{ fulfilmentForm.processing ? 'Saving...' : 'Save status' }}
                        </button>
                    </form>
                </div>

                <!-- Internal Note -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-navy-600 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <NotebookPen class="size-4" />
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-navy-50">
                                Internal note
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">
                                Add an administrative note
                            </p>
                        </div>
                    </div>

                    <form class="space-y-3 border-t border-slate-100 pt-4 dark:border-navy-600" @submit.prevent="addNote">
                        <textarea
                            v-model="noteForm.note"
                            rows="3"
                            placeholder="Write an internal note..."
                            class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-primary/40 focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100"
                        />

                        <p v-if="noteForm.errors.note" class="text-xs text-error">
                            {{ noteForm.errors.note }}
                        </p>

                        <button
                            type="submit"
                            :disabled="noteForm.processing"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-primary/30 hover:bg-primary/5 hover:text-primary disabled:cursor-not-allowed disabled:opacity-50 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-200 dark:hover:bg-navy-600"
                        >
                            <LoaderCircle v-if="noteForm.processing" class="size-4 animate-spin" />
                            <Plus v-else class="size-4" />
                            {{ noteForm.processing ? 'Adding...' : 'Add internal note' }}
                        </button>
                    </form>
                </div>

                <!-- Cancel Order -->
                <button
                    v-if="order.fulfilment_status !== 'cancelled' && order.fulfilment_status !== 'delivered'"
                    type="button"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-error/20 bg-error/5 px-4 py-3 text-sm font-semibold text-error transition hover:border-error/40 hover:bg-error/10 focus:ring-2 focus:ring-error/10 dark:bg-error/10 dark:hover:bg-error/15"
                    @click="cancelOrder"
                >
                    <Ban class="size-4" />
                    Cancel order
                </button>

            </aside>
        </div>
    </div>
</template>
