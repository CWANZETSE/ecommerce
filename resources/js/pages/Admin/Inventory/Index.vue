<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { PageProps } from '@/types';
import {
    admin_inventory_index,
    admin_inventory_adjust,
    admin_inventory_movements,
    admin_dashboard,
} from '@/routes';
import Pagination from '@/components/Pagination.vue';
import PackageSVG from '@/components/SVG/packageSVG.vue';

interface AttributeValue {
    id: string;
    value: string;
    attribute: { id: string; name: string };
}
interface VariationRow {
    id: string;
    sku: string;
    stock_quantity: number;
    low_stock_threshold: number | null;
    product: { id: string; name: string; sku: string };
    attribute_values: AttributeValue[];
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
    variations: Paginator<VariationRow>;
    filters: { filter: string; search: string | null };
    counts: { all: number; low: number; out: number };
}

const props = defineProps<Props>();

const search = ref(props.filters.search ?? '');

watch(search, (val) => {
    router.get(admin_inventory_index.url(), {
        ...props.filters,
        search: val || undefined,
    }, { preserveState: true, replace: true });
});

function setFilter(filter: string) {
    router.get(admin_inventory_index.url(), { ...props.filters, filter }, {
        preserveState: true,
        replace: true,
    });
}

// Modal state
const showModal = ref(false);
const modalVariation = ref<VariationRow | null>(null);
const modalType = ref<'receive' | 'damage' | 'set'>('receive');
const modalQuantity = ref<number>(1);
const modalReason = ref('');
const modalProcessing = ref(false);

function openModal(variation: VariationRow) {
    modalVariation.value = variation;
    modalType.value = 'receive';
    modalQuantity.value = 1;
    modalReason.value = '';
    showModal.value = true;
}

function closeModal() {
    if (!modalProcessing.value) {
        showModal.value = false;
    }
}

function submitModal() {
    if (!modalVariation.value) return;
    modalProcessing.value = true;

    router.post(
        admin_inventory_adjust.url(modalVariation.value.id),
        {
            type: modalType.value,
            quantity: modalQuantity.value,
            reason: modalReason.value || null,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                modalProcessing.value = false;
                showModal.value = false;
            },
        },
    );
}

function stockBadge(variation: VariationRow): { text: string; class: string } {
    const threshold = variation.low_stock_threshold ?? 5;
    if (variation.stock_quantity <= 0) {
        return { text: 'Out of stock', class: 'badge bg-secondary/10 text-secondary dark:bg-secondary-light/15 dark:text-secondary-light' };
    }
    if (variation.stock_quantity <= threshold) {
        return { text: 'Low stock', class: 'badge bg-warning/10 text-warning dark:bg-warning/15' };
    }
    return { text: 'In stock', class: 'badge bg-success/10 text-success dark:bg-success/15' };
}
</script>

<template>
    <Head title="Inventory" />

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
                        Stock Inventory Report
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
                            Inventory
                        </li>
                    </ol>
                </div>
                <div
                    class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <PackageSVG />
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2">
                    <button v-for="tab in [
                        { key: 'all', label: `All (${props.counts.all})` },
                        { key: 'low', label: `Low stock (${props.counts.low})` },
                        { key: 'out', label: `Out of stock (${props.counts.out})` },
                    ]" :key="tab.key" class="btn rounded-lg px-3.5 py-2 text-xs font-medium transition-colors"
                        :class="props.filters.filter === tab.key
                                ? 'btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25'
                                : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100 dark:border-navy-450 dark:bg-navy-750 dark:text-navy-100 dark:hover:bg-navy-700'
                            " @click="setFilter(tab.key)">
                        {{ tab.label }}
                    </button>
                </div>

                <div class="w-full sm:w-72">
                    <input v-model="search" placeholder="Search by SKU or product name…"
                        class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                </div>
            </div>

            <div
                class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300">
                                <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                    Product
                                </th>
                                <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                    Attributes
                                </th>
                                <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                    Variation SKU
                                </th>
                                <th class="whitespace-nowrap border border-slate-150 px-4 py-4 text-center font-bold dark:border-slate-700">
                                    Stock
                                </th>
                                <th class="whitespace-nowrap border border-slate-150 px-4 py-4 text-center font-bold dark:border-slate-700">
                                    Status
                                </th>
                                <th class="whitespace-nowrap border border-slate-150 px-5 py-4 text-center font-bold dark:border-slate-700">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="text-sm">
                            <tr v-for="v in variations.data" :key="v.id"
                                class="transition-colors duration-200 hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]">
                                <!-- Product -->
                                <td class="min-w-[180px] border border-slate-150 px-5 py-4 dark:border-slate-700">
                                    <p class="font-semibold text-slate-900 dark:text-navy-50">
                                        {{ v.product.name }}
                                    </p>
                                </td>

                                <!-- Attributes -->
                                <td class="min-w-[180px] border border-slate-150 px-5 py-4 dark:border-slate-700">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span v-for="av in v.attribute_values" :key="av.id"
                                            class="badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light">
                                            {{ av.attribute.name }}:
                                            <span class="ml-1 font-semibold">{{ av.value }}</span>
                                        </span>

                                        <span v-if="v.attribute_values.length === 0"
                                            class="text-xs text-slate-400 dark:text-navy-400">
                                            No attributes
                                        </span>
                                    </div>
                                </td>

                                <!-- Variation SKU -->
                                <td class="whitespace-nowrap border border-slate-150 px-5 py-4 font-mono text-xs text-slate-500 dark:border-slate-700 dark:text-navy-300">
                                    {{ v.sku }}
                                </td>

                                <!-- Stock -->
                                <td class="whitespace-nowrap border border-slate-150 px-4 py-4 text-center dark:border-slate-700">
                                    <span class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15">
                                        {{ v.stock_quantity }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="whitespace-nowrap border border-slate-150 px-4 py-4 text-center dark:border-slate-700">
                                    <span :class="stockBadge(v).class">
                                        {{ stockBadge(v).text }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="whitespace-nowrap border border-slate-150 px-5 py-4 text-center dark:border-slate-700">
                                    <div class="inline-flex items-center justify-center gap-2">
                                        <!-- Adjust stock -->
                                        <button type="button"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-150/20 bg-primary/5 p-2.5 text-primary transition-all duration-200 hover:border-slate-150 hover:bg-primary hover:text-white hover:shadow-sm dark:border-accent-light/20 dark:bg-accent-light/10 dark:text-accent-light dark:hover:border-accent dark:hover:bg-accent dark:hover:text-white"
                                            title="Adjust stock" @click="openModal(v)">
                                            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path opacity="0.4"
                                                    d="M18.5094 1.7998C18.0094 1.7998 17.6094 2.1998 17.6094 2.6998V7.3498C17.6094 7.8498 18.0094 8.2498 18.5094 8.2498C19.0094 8.2498 19.4094 7.8498 19.4094 7.3498V2.6998C19.4094 2.1998 19.0094 1.7998 18.5094 1.7998Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M11.9996 15.75C11.4996 15.75 11.0996 16.15 11.0996 16.65V21.3C11.0996 21.8 11.4996 22.2 11.9996 22.2C12.4996 22.2 12.8996 21.8 12.8996 21.3V16.65C12.8996 16.16 12.4996 15.75 11.9996 15.75Z"
                                                    fill="currentColor" />
                                                <path opacity="0.4"
                                                    d="M5.48984 1.7998C4.98984 1.7998 4.58984 2.1998 4.58984 2.6998V7.3498C4.58984 7.8498 4.98984 8.2498 5.48984 8.2498C5.98984 8.2498 6.38984 7.8498 6.38984 7.3498V2.6998C6.38984 2.1998 5.97984 1.7998 5.48984 1.7998Z"
                                                    fill="currentColor" />
                                                <path opacity="0.4"
                                                    d="M7.35047 10.1699H3.63047C3.13047 10.1699 2.73047 10.5699 2.73047 11.0699C2.73047 11.5699 3.13047 11.9699 3.63047 11.9699H4.59047V21.2999C4.59047 21.7999 4.99047 22.1999 5.49047 22.1999C5.99047 22.1999 6.39047 21.7999 6.39047 21.2999V11.9699H7.35047C7.85047 11.9699 8.25047 11.5699 8.25047 11.0699C8.25047 10.5699 7.84047 10.1699 7.35047 10.1699Z"
                                                    fill="currentColor" />
                                                <path opacity="0.4"
                                                    d="M20.37 10.1699H16.65C16.15 10.1699 15.75 10.5699 15.75 11.0699C15.75 11.5699 16.15 11.9699 16.65 11.9699H17.61V21.2999C17.61 21.7999 18.01 22.1999 18.51 22.1999C19.01 22.1999 19.41 21.7999 19.41 21.2999V11.9699H20.37C20.87 11.9699 21.27 11.5699 21.27 11.0699C21.27 10.5699 20.87 10.1699 20.37 10.1699Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M13.8602 12.0298H12.9002V2.6998C12.9002 2.1998 12.5002 1.7998 12.0002 1.7998C11.5002 1.7998 11.1002 2.1998 11.1002 2.6998V12.0298H10.1402C9.64023 12.0298 9.24023 12.4298 9.24023 12.9298C9.24023 13.4298 9.64023 13.8298 10.1402 13.8298H13.8602C14.3602 13.8298 14.7602 13.4298 14.7602 12.9298C14.7602 12.4298 14.3602 12.0298 13.8602 12.0298Z"
                                                    fill="currentColor" />
                                            </svg>
                                        </button>

                                        <!-- Stock movements -->
                                        <Link :href="admin_inventory_movements.url(v.id)"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-150/20 bg-slate-100 p-2.5 text-slate-600 transition-all duration-200 hover:border-slate-300 hover:bg-slate-200 hover:shadow-sm dark:border-navy-500 dark:bg-navy-700 dark:text-navy-200 dark:hover:border-navy-400 dark:hover:bg-navy-600"
                                            title="View stock movements">
                                            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path opacity="0.4"
                                                    d="M7.37 22H16.62C19.31 22 21.49 19.82 21.49 17.13V8.37C21.49 5.68 19.31 3.5 16.62 3.5H7.37C4.68 3.5 2.5 5.68 2.5 8.37V17.12C2.5 19.82 4.68 22 7.37 22Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M8.28906 6.29C7.86906 6.29 7.53906 5.95 7.53906 5.54V2.75C7.53906 2.34 7.86906 2 8.28906 2C8.70906 2 9.03906 2.34 9.03906 2.75V5.53C9.03906 5.95 8.70906 6.29 8.28906 6.29Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M15.7109 6.29C15.2909 6.29 14.9609 5.95 14.9609 5.54V2.75C14.9609 2.33 15.3009 2 15.7109 2C16.1309 2 16.4609 2.34 16.4609 2.75V5.53C16.4609 6.29 15.7109 6.29 15.7109 6.29Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M14.7794 13.71H7.35938C6.93938 13.71 6.60938 13.37 6.60938 12.96C6.60938 12.55 6.94938 12.21 7.35938 12.21H14.7794C15.1994 12.21 15.5294 12.55 15.5294 12.96C15.5294 13.37 15.1994 13.71 14.7794 13.71Z"
                                                    fill="currentColor" />
                                                <path
                                                    d="M11.9994 17.4199H7.35938C6.93938 17.4199 6.60938 17.0799 6.60938 16.6699C6.60938 16.2499 6.94938 15.9199 7.35938 15.9199H11.9994C12.4194 15.9199 12.7494 16.2599 12.7494 16.6699C12.7494 17.0799 12.4194 17.4199 11.9994 17.4199Z"
                                                    fill="currentColor" />
                                            </svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="variations.data.length === 0">
                                <td colspan="6"
                                    class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300">
                                    No variations match this filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Replace the entire <nav> block with our unified component -->
                <Pagination :paginator="variations" />
                
            </div>
        </div>
    </div>

    <!-- Adjust modal -->
    <div v-if="showModal && modalVariation"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
        @click.self="closeModal">
        <div class="card w-full max-w-md p-5 sm:p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-navy-600">
                <a href="#" class="w-12 text-center">
                    <div class="avatar size-10">
                        <div class="is-initial mask is-squircle bg-secondary text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1.5  text-ellipsis whitespace-nowrap text-xs text-slate-700 dark:text-navy-100">
                        Adjust Stock
                    </p>
                </a>
                <button type="button"
                    class="btn size-7 rounded-full p-0 text-slate-400 hover:bg-slate-200/60 dark:hover:bg-navy-600"
                    @click="closeModal">
                    ✕
                </button>
            </div>

            <div class="rounded-lg bg-slate-150 mt-3 px-2.5 py-2 dark:bg-navy-600">
                <p class="mt-0.5 text-tiny-plus uppercase">{{ modalVariation.product.name }}</p>

                <p class="mt-0.5 truncate text-xs font-medium text-slate-700 dark:text-navy-100">
                    <template v-if="modalVariation.attribute_values.length">
                        {{modalVariation.attribute_values.map(av => av.value).join(' / ')}}
                    </template>
                    <span v-else class="text-slate-400 dark:text-navy-300">Default variation</span>
                </p>

                <div class="mt-1.5 text-xs text-slate-400 dark:text-navy-300">
                    Current Stock
                    <div class="text-slate-700 dark:text-navy-500"> {{ modalVariation.stock_quantity }}</div>
                </div>
            </div>

            <div class="mt-4 space-y-4">
                <label class="block">
                    <span class="font-medium text-slate-700 dark:text-navy-100">Action</span>
                    <select v-model="modalType"
                        class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                        <option value="receive">Receive stock (add)</option>
                        <option value="damage">Mark damaged / lost</option>
                        <option value="set">Set to exact quantity</option>
                    </select>
                </label>

                <label class="block">
                    <span class="font-medium text-slate-700 dark:text-navy-100">
                        {{ modalType === 'set' ? 'New quantity' : 'Quantity' }}
                    </span>
                    <input v-model.number="modalQuantity" type="number" min="0"
                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                </label>

                <label class="block">
                    <span class="font-medium text-slate-700 dark:text-navy-100">Reason (optional)</span>
                    <input v-model="modalReason" placeholder="Enter adjustment reason..."
                        class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                </label>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                    class="btn bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450"
                    :disabled="modalProcessing" @click="closeModal">
                    Cancel
                </button>
                <button type="button"
                    class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                    :disabled="modalProcessing" @click="submitModal">
                    {{ modalProcessing ? 'Saving…' : 'Apply' }}
                </button>
            </div>
        </div>
    </div>
</template>