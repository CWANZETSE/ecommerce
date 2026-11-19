<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_orders_index,
    admin_orders_notes_store,
    admin_orders_cancel,
    admin_orders_fulfilment,
    admin_orders_show_receipt
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
    HistoryIcon,
} from '@lucide/vue';
import MenuSVG from '@/components/SVG/menuSVG.vue';
import PackageSVG from '@/components/SVG/packageSVG.vue';
import StripsSVG from '@/components/SVG/stripsSVG.vue';
import CreativeDesignSVG from '@/components/SVG/creativeDesignSVG.vue';
import HomeSVG from '@/components/SVG/homeSVG.vue';
import CirclesSVG from '@/components/SVG/circlesSVG.vue';
import SettingSVG from '@/components/SVG/settingSVG.vue';
import DeliveryPlaneSVG from '@/components/SVG/deliveryPlaneSVG.vue';
import PaySVG from '@/components/SVG/paySVG.vue';
import ReceiptSVG from '@/components/SVG/receiptSVG.vue';
import NoteSVG from '@/components/SVG/NoteSVG.vue';

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
                    <MenuSVG />
                </div>

                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-semibold tracking-tight text-slate-800 dark:text-navy-50 lg:text-2xl">
                            Order {{ order.order_number }}
                        </h2>

                        <span
                            class="rounded-full bg-primary/10 px-2.5 py-1 text-[10px] font-semibold capitalize tracking-wide text-primary">
                            {{ order.fulfilment_status }}
                        </span>
                    </div>


                </div>
            </div>

            <Link :href="admin_orders_index.url()"
                class="btn border border-primary/30 bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:border-accent-light/30 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                <ArrowLeft class="size-4" />
                <span>Back to orders</span>
            </Link>
        </div>
        <Link :href="admin_orders_show_receipt.url(order.ulid)"
            class="btn border border-primary/30 mb-3 bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:border-accent-light/30 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
            <ReceiptSVG /> Show Receipt
        </Link>
        <!-- Order Content -->
        <div class="grid items-start gap-5 xl:grid-cols-3 lg:gap-6">
            <!-- Main Content -->
            <div class="min-w-0 space-y-5 xl:col-span-2 lg:space-y-6">
                <!-- Order Items -->
                <div
                    class="overflow-hidden rounded-2xl border border-white bg-white shadow-sm dark:border-navy-800 dark:bg-navy-800">
                    <!-- Card Header -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-150 px-4 py-4 dark:border-navy-700 sm:px-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <PackageSVG />
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100 sm:text-base">
                                    Order items
                                </h3>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-navy-300">
                                    {{ order.items.length }} item(s) in this order
                                </p>
                            </div>
                        </div>

                        <span
                            class="badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                            {{ order.items.length }} {{ order.items.length === 1 ? 'line item' : 'line items' }}
                        </span>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr
                                    class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300">
                                    <th
                                        class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                        Product
                                    </th>
                                    <th
                                        class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                        Unit price
                                    </th>
                                    <th
                                        class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                        Qty
                                    </th>
                                    <th
                                        class="whitespace-nowrap border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700">
                                        Total
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                                <tr v-for="item in order.items" :key="item.id"
                                    class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]">
                                    <td class="border border-slate-150 px-5 py-3.5 align-top dark:border-slate-700">
                                        <p class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                                            {{ item.product_name }}
                                        </p>

                                        <p
                                            class="badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                            SKU: {{ item.sku }}
                                        </p>

                                        <div v-if="item.attributes?.length" class="mt-2.5 flex flex-wrap gap-1.5">
                                            <span v-for="(a, i) in item.attributes" :key="i"
                                                class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15">
                                                <span>{{ a.name }}:</span>
                                                <span class="font-semibold text-slate-500 dark:text-navy-100">{{ a.value
                                                    }}</span>
                                            </span>
                                        </div>
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-slate-150 px-5 py-3.5 align-middle text-sm text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                        <span class="text-xs font-medium text-slate-400 dark:text-navy-400">KES</span>
                                        <span class="ml-1 font-medium text-slate-700 dark:text-navy-100">{{
                                            item.unit_price }}</span>
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-slate-150 px-5 py-3.5 align-middle dark:border-slate-700">
                                        <span
                                            class="badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                            ×{{ item.quantity }}
                                        </span>
                                    </td>

                                    <td
                                        class="whitespace-nowrap border border-slate-150 px-5 py-3.5 text-right align-middle dark:border-slate-700">
                                        <span
                                            class="badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                            KES {{ item.line_total }}
                                        </span>
                                    </td>
                                </tr>

                                <tr v-if="order.items.length === 0">
                                    <td colspan="4"
                                        class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300">
                                        No items found for this order.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Status History -->


                <div class="card rounded-2xl overflow-hidden pb-4">
                    <div class="my-3 flex items-center justify-between px-4 sm:px-5">

                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <StripsSVG />
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 dark:text-navy-100 sm:text-base">
                                    Order History
                                </h3>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-navy-300">
                                    Order activity and updates
                                </p>
                            </div>
                        </div>
                        <div class="">

                        </div>
                    </div>

                    <div v-if="order.history.length" class="px-4 sm:px-5">
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-left">
                                <thead>
                                    <tr
                                        class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300">
                                        <th
                                            class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                            Activity
                                        </th>
                                        <th class="border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                            Status / Note
                                        </th>
                                        <th class="border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                            User
                                        </th>
                                        <th
                                            class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                            DateTime
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                                    <tr v-for="h in order.history" :key="h.id"
                                        class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]">
                                        <td
                                            class="whitespace-nowrap border border-slate-150 px-5 py-3.5 align-top dark:border-slate-700">
                                            <span class="capitalize">
                                                {{ h.type }}
                                            </span>
                                        </td>

                                        <td
                                            class="min-w-[220px] border border-slate-150 px-5 py-3.5 align-middle dark:border-slate-700">
                                            <div v-if="!h.note"
                                                class="flex flex-wrap items-center gap-1 text-sm text-slate-600 dark:text-navy-200">
                                                <span class="capitalize">{{ h.from_status ?? '—' }}</span>
                                                <ArrowRight class="inline size-3 shrink-0" />
                                                <strong
                                                    class="badge space-x-2.5 rounded-full capitalize bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                                    {{ h.to_status }}
                                                </strong>
                                            </div>

                                            <p v-if="h.note"
                                                class=" text-sm italic leading-relaxed text-slate-400 dark:bg-primary/[0.05] dark:text-navy-300">
                                                &ldquo;{{ h.note }}&rdquo;
                                            </p>
                                        </td>
                                        <td
                                            class="whitespace-nowrap border border-slate-150 px-5 py-3.5 align-top dark:border-slate-700">
                                            <span class="text-slate-400 text-sm">
                                                {{ h?.user?.name??'System' }}
                                            </span>
                                        </td>
                                        <td
                                            class="min-w-[220px] border border-slate-150 px-5 py-3.5 align-middle dark:border-slate-700">
                                            <span
                                                class="badge space-x-1 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                                <HistoryIcon class="size-3" />
                                            </span>
                                            {{ h.created_at }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else class="px-4 py-8 text-center sm:px-5">
                        <History class="mx-auto size-8 text-slate-300 dark:text-navy-500" />
                        <p class="mt-2 text-sm text-slate-400 dark:text-navy-300">
                            No status history available yet.
                        </p>
                    </div>
                </div>


            </div>

            <!-- Sidebar -->
            <aside class="grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-1">
                <!-- Customer -->
                <div
                    class="min-w-0 rounded-2xl border border-white bg-white p-4 shadow-sm dark:border-navy-800 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <CreativeDesignSVG />
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

                    <div class="grid gap-3 border-t border-slate-100 pt-3 dark:border-navy-600">
                        <div class="rounded-lg bg-slate-50/70 p-3 dark:bg-navy-700">
                            <p
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-navy-400">
                                Name
                            </p>
                            <p class="mt-1 text-xs font-semibold text-slate-800 dark:text-navy-50">
                                {{ order.customer_name }}
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <div class="flex min-w-0 items-start gap-2 text-xs text-slate-500 dark:text-navy-300">
                                <Mail class="mt-0.5 size-3.5 shrink-0 text-slate-400" />
                                <span class="break-all">{{ order.customer_email }}</span>
                            </div>

                            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-navy-300">
                                <Phone class="size-3.5 shrink-0 text-slate-400" />
                                <span>{{ order.customer_phone }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div
                    class="min-w-0 rounded-2xl border border-white bg-white p-4 shadow-sm dark:border-navy-800 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <DeliveryPlaneSVG />
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

                    <div class="grid gap-3 border-t border-slate-100 pt-3 dark:border-navy-600">
                        <div class="rounded-lg bg-slate-50/70 p-3 dark:bg-navy-700">
                            <p
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-navy-400">
                                Location
                            </p>
                            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-navy-50">
                                {{ order.shipping_address.county }} — {{ order.shipping_address.town }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-[10px] font-medium uppercase tracking-wide text-slate-400 dark:text-navy-400">
                                Address
                            </p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-navy-300">
                                {{ order.shipping_address.address }}
                            </p>
                        </div>

                        <div v-if="order.shipping_address.instructions"
                            class="rounded-lg border border-primary/10 bg-primary/[0.04] p-3 text-xs italic leading-relaxed text-slate-500 dark:bg-primary/10 dark:text-navy-200">
                            <MessageSquare class="mr-1 inline size-3.5 text-primary" />
                            "{{ order.shipping_address.instructions }}"
                        </div>
                    </div>
                </div>

                <!-- Order Totals -->
                <div
                    class="min-w-0 rounded-2xl border border-white bg-white p-4 shadow-sm dark:border-navy-800 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <ReceiptSVG />
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

                    <dl class="grid grid-cols-2 gap-2 border-t border-slate-100 pt-4 text-xs dark:border-navy-600">
                        <div class="rounded-lg bg-slate-50/70 p-3 dark:bg-navy-700">
                            <dt class="text-slate-500 dark:text-navy-300">
                                Subtotal
                            </dt>
                            <dd class="mt-1 font-semibold text-slate-700 dark:text-navy-100">
                                KES {{ order.subtotal }}
                            </dd>
                        </div>

                        <div v-if="Number(order.discount_total) > 0"
                            class="rounded-lg bg-success/[0.05] p-3 dark:bg-success/10">
                            <dt class="text-success">
                                Discount
                                <span class="font-mono text-[10px]">({{ order.coupon_code }})</span>
                            </dt>
                            <dd class="mt-1 font-semibold text-success">
                                − KES {{ order.discount_total }}
                            </dd>
                        </div>

                        <div class="rounded-lg bg-slate-50/70 p-3 dark:bg-navy-700">
                            <dt class="text-slate-500 dark:text-navy-300">
                                Shipping
                            </dt>
                            <dd class="mt-1 font-semibold text-slate-700 dark:text-navy-100">
                                KES {{ order.shipping_fee }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-3 rounded-xl   bg-slate-150 p-4 dark:bg-navy-700">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-semibold text-slate-600 dark:text-navy-200">
                                Grand total
                            </span>
                            <span class="text-lg font-bold tracking-tight text-warning">
                                KES {{ order.grand_total }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment -->
                <div
                    class="min-w-0 rounded-2xl border border-white bg-white p-4 shadow-sm dark:border-navy-800 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <PaySVG />
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

                    <div class="grid gap-3 border-t border-slate-100 pt-3 dark:border-navy-600">
                        <div
                            class="flex items-center justify-between gap-2 rounded-lg bg-slate-50/70 p-3 dark:bg-navy-700">
                            <span class="text-xs text-slate-500 dark:text-navy-300">
                                Payment status
                            </span>

                            <span :class="order.payment_status === 'paid'
                                ? 'bg-success/10 text-success'
                                : 'bg-warning/10 text-warning'"
                                class="rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide">
                                {{ order.payment_status }}
                            </span>
                        </div>

                        <div v-if="order.payments.length" class="grid gap-3">
                            <div v-for="p in order.payments" :key="p.id"
                                class="min-w-0 rounded-xl border border-slate-100 bg-slate-50/70 p-3 dark:border-navy-600 dark:bg-navy-700">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs capitalize font-semibold text-slate-700 dark:text-navy-100">
                                        {{ p.provider }}
                                    </p>

                                    <span :class="p.status === 'success' || p.status === 'completed'
                                        ? 'text-success'
                                        : p.status === 'failed'
                                            ? 'text-error'
                                            : 'text-warning'" class="text-[10px] font-semibold uppercase">
                                        {{ p.status }}
                                    </span>
                                </div>

                                <p v-if="p.provider_reference"
                                    class="mt-2 break-all text-[11px] text-slate-500 dark:text-navy-300">
                                    Receipt:
                                    <span
                                        class="badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
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
                </div>

                <!-- Update Fulfilment -->
                <div
                    class="min-w-0 rounded-2xl border border-white bg-white p-4 shadow-sm dark:border-navy-800 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <CirclesSVG />
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

                    <form class="grid gap-3 border-t border-slate-100 pt-4 dark:border-navy-600"
                        @submit.prevent="updateFulfilment">
                        <div class="grid gap-1.5">
                            <label class="text-xs font-medium text-slate-600 dark:text-navy-200">
                                Fulfilment status
                            </label>

                            <select v-model="fulfilmentForm.status"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition hover:border-primary/40 focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100">
                                <option v-for="s in fulfilmentStatuses" :key="s.value" :value="s.value">
                                    {{ s.label }}
                                </option>
                            </select>
                        </div>


                        <p v-if="fulfilmentForm.errors.status" class="text-xs text-error">
                            {{ fulfilmentForm.errors.status }}
                        </p>

                        <button type="submit" :disabled="fulfilmentForm.processing"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-focus focus:ring-2 focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-accent dark:hover:bg-accent-focus">
                            <LoaderCircle v-if="fulfilmentForm.processing" class="size-4 animate-spin" />
                            <Save v-else class="size-4" />
                            {{ fulfilmentForm.processing ? 'Saving...' : 'Save status' }}
                        </button>
                    </form>
                </div>

                <!-- Internal Note -->
                <div
                    class="min-w-0 rounded-2xl border border-white bg-white p-4 shadow-sm dark:border-navy-800 dark:bg-navy-800 sm:p-5">
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <NoteSVG />
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

                    <form class="grid gap-3 border-t border-slate-100 pt-4 dark:border-navy-600"
                        @submit.prevent="addNote">
                        <textarea v-model="noteForm.note" rows="3" placeholder="Write an internal note..."
                            class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-primary/40 focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100" />

                        <p v-if="noteForm.errors.note" class="text-xs text-error">
                            {{ noteForm.errors.note }}
                        </p>

                        <button type="submit" :disabled="noteForm.processing"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-primary/30 hover:bg-primary/5 hover:text-primary disabled:cursor-not-allowed disabled:opacity-50 dark:border-navy-500 dark:bg-navy-700 dark:text-navy-200 dark:hover:bg-navy-600">
                            <LoaderCircle v-if="noteForm.processing" class="size-4 animate-spin" />
                            <Plus v-else class="size-4" />
                            {{ noteForm.processing ? 'Adding...' : 'Add internal note' }}
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</template>
