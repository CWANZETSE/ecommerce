<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_orders_index,
    admin_orders_notes_store,
    admin_orders_cancel,
    admin_orders_fulfilment
} from '@/routes';

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
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <div class="space-y-1">
                    <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                        Order {{ order.order_number }}
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-navy-300">Placed {{ order.created_at }}</p>
                </div>
            </div>
            <Link
                :href="admin_orders_index.url()"
                class="btn space-x-1.5 bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
            >
                <span>← Back</span>
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Main: items + history -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Items Card -->
                <div class="card overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                        <h3 class="font-medium text-slate-700 dark:text-navy-100">Items</h3>
                    </div>
                    <div class="is-scrollbar-x overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="border-b border-slate-200 bg-slate-50/50 text-xs text-slate-500 dark:border-navy-600 dark:bg-navy-900/50 dark:text-navy-300">
                                <tr>
                                    <th class="px-4 py-3 font-medium sm:px-5">Product</th>
                                    <th class="px-4 py-3 font-medium sm:px-5">Unit price</th>
                                    <th class="px-4 py-3 font-medium sm:px-5">Qty</th>
                                    <th class="px-4 py-3 font-medium sm:px-5">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-navy-600">
                                <tr v-for="item in order.items" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-navy-900/50">
                                    <td class="px-4 py-3 sm:px-5">
                                        <p class="font-medium text-slate-700 dark:text-navy-100">{{ item.product_name }}</p>
                                        <p class="font-mono text-xs text-slate-400 dark:text-navy-300">{{ item.sku }}</p>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            <span
                                                v-for="(a, i) in item.attributes"
                                                :key="i"
                                                class="inline-flex items-center rounded-md border border-slate-300 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:border-navy-500 dark:bg-navy-750 dark:text-navy-200"
                                            >
                                                {{ a.name }}: {{ a.value }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">KES {{ item.unit_price }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-700 dark:text-navy-100 sm:px-5">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-700 dark:text-navy-100 sm:px-5">KES {{ item.line_total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Status history Card -->
                <div class="card overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                        <h3 class="font-medium text-slate-700 dark:text-navy-100">Status history</h3>
                    </div>
                    <ul class="divide-y divide-slate-200 dark:divide-navy-600">
                        <li v-for="h in order.history" :key="h.id" class="px-4 py-3 text-sm sm:px-5">
                            <div class="flex items-center justify-between">
                                <p class="text-slate-700 dark:text-navy-100">
                                    <span class="badge bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200 text-xs uppercase">{{ h.type }}</span>
                                    <span class="ml-2.5 text-xs sm:text-sm">{{ h.from_status ?? '—' }} → <strong class="font-semibold">{{ h.to_status }}</strong></span>
                                </p>
                                <span class="text-xs text-slate-400 dark:text-navy-300">{{ h.created_at }}</span>
                            </div>
                            <p v-if="h.note" class="mt-1 text-xs text-slate-500 dark:text-navy-300">{{ h.note }}</p>
                        </li>
                        <li v-if="order.history.length === 0" class="px-4 py-6 text-center text-sm text-slate-400 dark:text-navy-300">
                            No history yet.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Sidebar: summary + actions -->
            <aside class="space-y-6">
                <!-- Customer Card -->
                <div class="card p-4 sm:p-5 space-y-2">
                    <h3 class="font-medium text-slate-700 dark:text-navy-100 border-b border-slate-200 dark:border-navy-600 pb-2.5">Customer</h3>
                    <p class="text-sm font-semibold text-slate-800 dark:text-navy-50 pt-1">{{ order.customer_name }}</p>
                    <p class="text-xs text-slate-500 dark:text-navy-300">{{ order.customer_email }}</p>
                    <p class="text-xs text-slate-500 dark:text-navy-300">{{ order.customer_phone }}</p>
                </div>

                <!-- Shipping Address Card -->
                <div class="card p-4 sm:p-5 space-y-2">
                    <h3 class="font-medium text-slate-700 dark:text-navy-100 border-b border-slate-200 dark:border-navy-600 pb-2.5">Shipping address</h3>
                    <p class="text-sm font-semibold text-slate-800 dark:text-navy-50 pt-1">{{ order.shipping_address.county }} — {{ order.shipping_address.town }}</p>
                    <p class="text-xs text-slate-500 dark:text-navy-300">{{ order.shipping_address.address }}</p>
                    <p v-if="order.shipping_address.instructions" class="mt-1 text-xs italic text-slate-400 dark:text-navy-300">
                        "{{ order.shipping_address.instructions }}"
                    </p>
                </div>

                <!-- Totals Card -->
                <div class="card p-4 sm:p-5 space-y-3">
                    <h3 class="font-medium text-slate-700 dark:text-navy-100 border-b border-slate-200 dark:border-navy-600 pb-2.5">Totals</h3>
                    <dl class="space-y-2 text-sm text-slate-600 dark:text-navy-200">
                        <div class="flex justify-between"><dt>Subtotal</dt><dd class="font-medium">KES {{ order.subtotal }}</dd></div>
                        <div v-if="Number(order.discount_total) > 0" class="flex justify-between text-success">
                            <dt>Discount <span class="font-mono text-xs">({{ order.coupon_code }})</span></dt>
                            <dd class="font-medium">− KES {{ order.discount_total }}</dd>
                        </div>
                        <div class="flex justify-between"><dt>Shipping</dt><dd class="font-medium">KES {{ order.shipping_fee }}</dd></div>
                        <div class="flex justify-between border-t border-slate-200 pt-3 text-base font-semibold text-slate-800 dark:border-navy-600 dark:text-navy-50">
                            <dt>Total</dt><dd>KES {{ order.grand_total }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Payment Card -->
                <div class="card p-4 sm:p-5 space-y-3">
                    <h3 class="font-medium text-slate-700 dark:text-navy-100 border-b border-slate-200 dark:border-navy-600 pb-2.5">Payment</h3>
                    <p class="text-sm text-slate-600 dark:text-navy-200">
                        Status: <strong :class="order.payment_status === 'paid' ? 'text-success' : 'text-warning'">{{ order.payment_status }}</strong>
                    </p>
                    <div v-for="p in order.payments" :key="p.id" class="space-y-1 text-xs text-slate-500 dark:text-navy-300 border-t border-slate-100 dark:border-navy-600 pt-2">
                        <p class="font-medium text-slate-700 dark:text-navy-100">{{ p.provider }} — <span class="uppercase">{{ p.status }}</span></p>
                        <p v-if="p.provider_reference">Receipt: <span class="font-mono">{{ p.provider_reference }}</span></p>
                        <p v-if="p.failure_reason" class="text-error">{{ p.failure_reason }}</p>
                        <p>{{ p.created_at }}</p>
                    </div>
                </div>

                <!-- Update Fulfilment Card -->
                <div class="card p-4 sm:p-5 space-y-3">
                    <h3 class="font-medium text-slate-700 dark:text-navy-100 border-b border-slate-200 dark:border-navy-600 pb-2.5">Update fulfilment</h3>
                    <form class="space-y-3" @submit.prevent="updateFulfilment">
                        <select
                            v-model="fulfilmentForm.status"
                            class="form-select w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        >
                            <option v-for="s in fulfilmentStatuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                        <textarea
                            v-model="fulfilmentForm.note"
                            rows="2"
                            placeholder="Note (optional)"
                            class="form-textarea w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                        <p v-if="fulfilmentForm.errors.status" class="text-xs text-error">{{ fulfilmentForm.errors.status }}</p>
                        <button
                            type="submit"
                            :disabled="fulfilmentForm.processing"
                            class="btn w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 disabled:opacity-50 dark:bg-accent dark:hover:bg-accent-focus"
                        >
                            {{ fulfilmentForm.processing ? 'Saving…' : 'Save status' }}
                        </button>
                    </form>
                </div>

                <!-- Internal Note Card -->
                <div class="card p-4 sm:p-5 space-y-3">
                    <h3 class="font-medium text-slate-700 dark:text-navy-100 border-b border-slate-200 dark:border-navy-600 pb-2.5">Add internal note</h3>
                    <form class="space-y-3" @submit.prevent="addNote">
                        <textarea
                            v-model="noteForm.note"
                            rows="2"
                            placeholder="Internal note…"
                            class="form-textarea w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                        <p v-if="noteForm.errors.note" class="text-xs text-error">{{ noteForm.errors.note }}</p>
                        <button
                            type="submit"
                            :disabled="noteForm.processing"
                            class="btn w-full bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 disabled:opacity-50 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450"
                        >
                            Add note
                        </button>
                    </form>
                </div>

                <button
                    v-if="order.fulfilment_status !== 'cancelled' && order.fulfilment_status !== 'delivered'"
                    type="button"
                    class="btn w-full border border-error bg-error/10 font-medium text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25 dark:bg-error/15"
                    @click="cancelOrder"
                >
                    Cancel order
                </button>
            </aside>
        </div>
    </div>
</template>