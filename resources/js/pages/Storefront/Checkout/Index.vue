<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { customer_checkout_store } from '@/routes';
import PaySVG from '@/components/SVG/paySVG.vue';
import ShippingSVG from '@/components/SVG/shippingSVG.vue';

interface County {
    name: string;
    fee: string;
}

interface CartLine {
    id: string;
    quantity: number;
    variation_id: string;
    sku: string;
    unit_price: string;
    line_total: string;
    stock_quantity: number;
    product: {
        id: string;
        name: string;
        slug: string;
        primary_image_url: string | null;
    };
    attributes: { name: string; value: string }[];
}

interface CartPayload {
    items: CartLine[];
    subtotal: string;
    discount: string;
    coupon_code: string | null;
    item_count: number;
}

interface SavedAddress {
    id: string;
    label: string | null;
    recipient_name: string;
    county: string;
    town: string;
    address: string;
    instructions: string | null;
    is_default: boolean;
}

interface Props extends PageProps {
    cart: CartPayload;
    counties: County[];
    user: {} | null;
    addresses: SavedAddress[];
}

const props = defineProps<Props>();

type AddressMode = 'saved' | 'new' | null;

const addressMode = ref<AddressMode>(
    props.addresses.length > 0 ? 'saved' : 'new',
);

const selectedAddressId = ref<string | null>(null);

if (props.addresses.length > 0) {
    const defaultAddress =
        props.addresses.find((a) => a.is_default) ?? props.addresses[0];
    selectedAddressId.value = defaultAddress.id;
}

const selectedAddress = computed(() =>
    props.addresses.find((a) => a.id === selectedAddressId.value) ?? null,
);

/* ---------------- Form ---------------- */
const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    phone: props.user?.phone ?? '',
    county: '',
    town: '',
    address: '',
    instructions: '',
    save_address: false,
    address_label: '',
    idempotency_key: crypto.randomUUID(),
});

const isAdmin = computed(() => props.auth?.user?.role === 'admin');

// When a saved address is picked, mirror its delivery fields into the form.
// Phone is never copied — it belongs to the contact section for M-Pesa.
watch(
    [addressMode, selectedAddressId],
    () => {
        if (addressMode.value === 'saved' && selectedAddress.value) {
            form.name = selectedAddress.value.recipient_name;
            form.county = selectedAddress.value.county;
            form.town = selectedAddress.value.town;
            form.address = selectedAddress.value.address;
            form.instructions = selectedAddress.value.instructions ?? '';
        }
    },
    { immediate: true },
);

function switchToNew() {
    addressMode.value = 'new';
    form.county = '';
    form.town = '';
    form.address = '';
    form.instructions = '';
    form.save_address = false;
    form.address_label = '';
}

function switchToSaved() {
    addressMode.value = 'saved';
}

/* ---------------- Totals ---------------- */
const shippingFee = computed(() => {
    const selected = props.counties.find((c) => c.name === form.county);
    return selected ? Number(selected.fee) : 0;
});

const grandTotal = computed(() => {
    const sub = Number(props.cart.subtotal);
    const disc = Number(props.cart.discount);
    return (sub - disc + shippingFee.value).toFixed(2);
});

const hasDiscount = computed(() => Number(props.cart.discount) > 0);

/* ---------------- Submit ---------------- */
function submit() {
    form.post(customer_checkout_store.url());
}

const formatMoney = (amount) =>
    new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount || 0);
</script>

<template>

    <Head title="Checkout" />

    <div class="mx-auto max-w-5xl px-4 py-8">
        <!-- Header Section -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-150 pb-6 dark:border-navy-600">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">Checkout</h1>
                <p class="text-sm text-slate-400 dark:text-navy-300 mt-1">Complete your delivery details and securely
                    pay via M-Pesa.</p>
            </div>
            <div>
                <Link href="/cart"
                    class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                    ← Back to cart
                </Link>
            </div>
        </div>

        <!-- Checkout Layout Grid -->
        <div class="mt-6 grid gap-6 lg:grid-cols-5">
            <!-- Checkout Form -->
            <form class="lg:col-span-3 space-y-6" @submit.prevent="submit">
                <!-- Contact Details Card -->
                <section class="card p-5 dark:bg-navy-750 border border-slate-150 dark:border-navy-600">
                    <div class="flex items-center space-x-3 border-b border-slate-150 pb-4 dark:border-navy-600">
                        <div
                            class="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            <PaySVG/>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-slate-800 dark:text-navy-50">Payment
                                Details</h2>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Where should we send your M-Pesa
                                payment prompt?</p>
                        </div>
                    </div>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Full Name</label>
                            <input :value="auth.user?.name" :disabled="!isAdmin" placeholder="Enter your full name"
                                class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:text-navy-100 disabled:cursor-not-allowed disabled:bg-slate-150 disabled:text-slate-500 disabled:hover:bg-slate-150 dark:disabled:bg-navy-900/90 dark:disabled:text-navy-400 dark:disabled:hover:bg-navy-900/90" />
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                The name that will appear on receipt. 
                            </p>
                        </div>

                        <!-- M-Pesa Phone -->
                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">
                                M-Pesa Phone Number <span class="text-error">*</span>
                            </label>
                            <input v-model="form.phone" type="tel" required placeholder="0712345678"
                                class="form-input w-full mt-2 rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                The STK Push M-Pesa prompt will be sent to this phone number.
                            </p>
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-error">
                                {{ form.errors.phone }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Delivery Information Card -->
                <section class="card p-5 dark:bg-navy-750 border border-slate-150 dark:border-navy-600">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-150 pb-4 dark:border-navy-600">
                        <div class="flex items-center space-x-3">
                            <div class="flex size-9 items-center justify-center rounded-lg bg-info/10 text-info">
                                <ShippingSVG/>
                            </div>
                            <h2 class="text-base font-semibold text-slate-800 dark:text-navy-50">Delivery Address</h2>
                        </div>

                        <div v-if="props.addresses.length > 0" class="flex gap-2">
                            <button type="button"
                                class="btn px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                                :class="addressMode === 'saved' ? 'btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25' : 'border border-slate-300 text-slate-700 hover:bg-slate-150 dark:border-navy-500 dark:text-navy-100 dark:hover:bg-navy-700'"
                                @click="switchToSaved">
                                Saved addresses
                            </button>
                            <button type="button"
                                class="btn px-3 py-1.5 text-xs font-medium rounded-lg transition-colors"
                                :class="addressMode === 'new' ? 'btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25' : 'border border-slate-300 text-slate-700 hover:bg-slate-150 dark:border-navy-500 dark:text-navy-100 dark:hover:bg-navy-700'"
                                @click="switchToNew">
                                Use new address
                            </button>
                        </div>
                    </div>

                    <!-- Saved Address Picker -->
                    <div v-if="addressMode === 'saved' && props.addresses.length > 0" class="mt-4 space-y-3">
                        <label v-for="addr in props.addresses" :key="addr.id"
                            class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 text-sm transition-all"
                            :class="selectedAddressId === addr.id ? 'border-1 border-primary bg-primary/5 dark:border-accent dark:bg-accent/10' : 'border-slate-150 hover:bg-slate-50 dark:border-navy-600 dark:hover:bg-navy-900/50'">
                            <input v-model="selectedAddressId" type="radio" :value="addr.id"
                                class="mt-1 form-radio text-primary dark:text-accent" />
                            <div class="flex-1 space-y-1">
                                <div class="flex items-center space-x-2">
                                    <p class="font-semibold text-slate-800 dark:text-navy-50">
                                        {{ addr.label ?? 'Delivery Address' }}
                                    </p>
                                    <span v-if="addr.is_default"
                                        class="badge bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light px-2 py-0.5 rounded-full text-xs font-semibold">
                                        Default
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 dark:text-navy-300">
                                    Recipient: <span class="font-medium text-slate-600 dark:text-navy-200">{{
                                        addr.recipient_name }}</span>
                                </p>
                                <p class="text-xs text-slate-500 dark:text-navy-300">
                                    <span class="font-medium text-slate-700 dark:text-navy-100">{{ addr.county }}</span>
                                    — {{ addr.town }}, {{ addr.address }}
                                </p>
                                <p v-if="addr.instructions"
                                    class="text-xs italic text-slate-400 dark:text-navy-300 pt-0.5">
                                    Note: "{{ addr.instructions }}"
                                </p>
                            </div>
                        </label>

                        <p v-if="form.errors.county" class="mt-1 text-xs text-error">
                            {{ form.errors.county }}
                        </p>
                        <p v-if="form.errors.town" class="mt-1 text-xs text-error">
                            {{ form.errors.town }}
                        </p>
                        <p v-if="form.errors.address" class="mt-1 text-xs text-error">
                            {{ form.errors.address }}
                        </p>
                    </div>

                    <!-- New Address Form -->
                    <div v-else class="mt-4 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">County</label>
                                <select v-model="form.county" required
                                    class="form-select mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:text-navy-100">
                                    <option value="">Select delivery county…</option>
                                    <option v-for="c in props.counties" :key="c.name" :value="c.name">
                                        {{ c.name }} — KES {{ c.fee }}
                                    </option>
                                </select>
                                <p v-if="form.errors.county" class="mt-1 text-xs text-error">
                                    {{ form.errors.county }}
                                </p>
                            </div>
                            <div>
                                <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Town /
                                    City</label>
                                <input v-model="form.town" required placeholder="e.g. Kitengela"
                                    class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:text-navy-100" />
                                <p v-if="form.errors.town" class="mt-1 text-xs text-error">
                                    {{ form.errors.town }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Street Address /
                                Building</label>
                            <textarea v-model="form.address" required rows="2"
                                placeholder="Street name, apartment, building number"
                                class="form-textarea mt-1.5 w-full rounded-lg bg-slate-150 p-3 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:text-navy-100" />
                            <p v-if="form.errors.address" class="mt-1 text-xs text-error">
                                {{ form.errors.address }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Delivery Instructions
                                (optional)</label>
                            <textarea v-model="form.instructions" rows="2"
                                placeholder="Specific landmarks or delivery notes"
                                class="form-textarea mt-1.5 w-full rounded-lg bg-slate-150 p-3 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:text-navy-100" />
                        </div>

                        <!-- Save Address Option (Auth Users) -->
                        <div v-if="props.user"
                            class="rounded-lg border border-slate-150 bg-slate-100/50 p-4 dark:border-navy-600 dark:bg-navy-900/50 space-y-3">
                            <label class="inline-flex items-center space-x-2 cursor-pointer">
                                <input v-model="form.save_address" type="checkbox"
                                    class="form-checkbox is-outline size-4 rounded-sm border-slate-400/70 bg-slate-100 before:bg-primary checked:border-primary dark:border-navy-500 dark:bg-navy-900 dark:before:bg-accent" />
                                <span class="text-sm font-medium text-slate-700 dark:text-navy-200">Save this address to
                                    my account</span>
                            </label>
                            <div v-if="form.save_address">
                                <input v-model="form.address_label" placeholder="Address Label (e.g. Home, Office)"
                                    class="form-input w-full rounded-lg bg-slate-150 px-3 py-2 text-sm ring-primary/50 placeholder:text-slate-400 dark:bg-navy-900 dark:text-navy-100" />
                                <p v-if="form.errors.address_label" class="mt-1 text-xs text-error">
                                    {{ form.errors.address_label }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Submit Button -->
                <button type="submit" :disabled="form.processing || !form.county || !form.phone"
                    class="btn w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus py-3.5 text-base shadow-lg disabled:opacity-50">
                    {{
                        form.processing
                            ? 'Placing order & sending STK push…'
                            : !form.phone
                                ? 'Enter M-Pesa phone to continue'
                                : !form.county
                                    ? 'Select a delivery county'
                                    : `Pay KES ${formatMoney(grandTotal)} with M-Pesa`
                    }}
                </button>
            </form>

            <!-- Order Summary Sidebar -->
            <aside class="lg:col-span-2 space-y-6">
                <div class="card p-5 dark:bg-navy-750 border border-slate-150 dark:border-navy-600">
                    <h2
                        class="text-base font-semibold text-slate-800 dark:text-navy-50 border-b border-slate-150 pb-4 dark:border-navy-600">
                        Order Summary ({{ props.cart.item_count }})
                    </h2>

                    <ul class="mt-4 max-h-72 divide-y divide-slate-150 overflow-y-auto pr-1 dark:divide-navy-600">
                        <li v-for="(line, i) in props.cart.items" :key="i" class="space-y-1 py-3 first:pt-0 last:pb-0">
                            <div class="flex items-start gap-3">
                                <!-- Image -->
                                <div
                                    class="relative size-12 shrink-0 overflow-hidden rounded-lg bg-slate-100 dark:bg-navy-900">
                                    <img v-if="line.product.primary_image_url" :src="line.product.primary_image_url"
                                        :alt="line.product.name" class="h-full w-full object-cover object-center" />
                                    <div v-else
                                        class="flex h-full w-full items-center justify-center text-slate-300 dark:text-navy-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                                            {{ line.product.name }}
                                            <span class="font-normal text-slate-400">× {{ line.quantity }}</span>
                                        </p>
                                        <span class="shrink-0 text-sm font-bold text-slate-800 dark:text-navy-50">
                                            KES {{ formatMoney(line.line_total) }}
                                        </span>
                                    </div>
                                    <p v-if="line.attributes.length"
                                        class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                        <span v-for="(a, j) in line.attributes" :key="j">
                                            {{ a.name }}: {{ a.value }}<span v-if="j < line.attributes.length - 1">,
                                            </span>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <dl
                        class="mt-4 space-y-3 border-t border-slate-150 pt-4 text-sm text-slate-600 dark:border-navy-600 dark:text-navy-200">
                        <div class="flex justify-between">
                            <dt class="text-slate-400 dark:text-navy-300">Subtotal</dt>
                            <dd class="font-medium text-slate-800 dark:text-navy-50">KES {{
                                formatMoney(props.cart.subtotal) }}</dd>
                        </div>
                        <div v-if="hasDiscount" class="flex justify-between text-success">
                            <dt>
                                Discount
                                <span class="font-mono text-xs">({{ props.cart.coupon_code }})</span>
                            </dt>
                            <dd>− KES {{ formatMoney(props.cart.discount) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400 dark:text-navy-300">Shipping Fee</dt>
                            <dd>
                                <span v-if="form.county" class="font-medium text-slate-800 dark:text-navy-50">KES {{
                                    formatMoney(shippingFee) }}</span>
                                <span v-else class="text-xs text-slate-400 dark:text-navy-300 italic">Select
                                    county</span>
                            </dd>
                        </div>
                        <div
                            class="flex justify-between border-t border-slate-150 pt-3 text-base font-bold text-slate-800 dark:border-navy-600 dark:text-navy-50">
                            <dt>Total Amount</dt>
                            <dd>KES {{ formatMoney(grandTotal) }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Trust & Terms Note -->
                <div
                    class="alert flex rounded-lg border border-primary/30 bg-primary/10 py-4 px-4 text-primary sm:px-5">
                    By placing this order, you agree to our terms of service and return policy. Payments are securely
                    processed via M-Pesa. Your order will be confirmed instantly upon successful authorization.
                </div>
            </aside>
        </div>
    </div>
</template>