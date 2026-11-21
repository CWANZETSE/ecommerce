<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import { admin_dashboard, admin_inventory_index, admin_inventory_movements } from '@/routes';
import ReportsSVG from '@/components/SVG/reportsSVG.vue';
import ReceiptSVG from '@/components/SVG/receiptSVG.vue';

interface Movement {
    id: string;
    type: string;
    quantity_change: number;
    previous_quantity: number;
    new_quantity: number;
    reason: string | null;
    created_at: string;
    user: { id: string; name: string } | null;
}

interface Variation {
    id: string;
    sku: string;
    stock_quantity: number;
    product: { id: string; name: string; sku: string };
    attribute_values: { id: string; value: string; attribute: { name: string } }[];
}

interface Props extends PageProps {
    variation: Variation;
    movements: {
        data: Movement[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}

const props = defineProps<Props>();

const typeLabel: Record<string, string> = {
    initial: 'Initial',
    receive: 'Received',
    adjust: 'Adjustment',
    damage: 'Damaged',
    sale: 'Sale',
    return: 'Return',
    refund_return: 'Refund return',
};
</script>

<template>
    <Head :title="`Movements — ${variation.sku}`" />

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
                        Stock Movements
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
                            Movements
                        </li>
                    </ol>
                </div>
                <div
                    class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <ReceiptSVG />
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-lg bg-slate-150 px-2.5 py-2 dark:bg-navy-600">
                    <div class="flex items-center justify-between space-x-1">
                      <p>
                        {{ variation.product.name }}
                      </p>
                      <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5 text-secondary dark:text-secondary-light" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                      </svg>
                    </div>

                    <span class="font-mono text-xs">{{ variation.sku }}</span>

                    <div class="progress mt-3 h-1.5 bg-secondary/15 dark:bg-secondary-light/25">
                      <div class="is-active relative w-8/12 overflow-hidden rounded-full bg-secondary dark:bg-secondary-light"></div>
                    </div>

                    <div class="mt-1.5 flex items-center justify-between text-xs text-slate-400 dark:text-navy-300">
                      Current stock
                      <span> {{ variation.stock_quantity }}</span>
                    </div>
                  </div>
            

            
<div class="overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300">
                    <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                        When
                    </th>
                    <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                        Type
                    </th>
                    <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                        Change
                    </th>
                    <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                        Before → After
                    </th>
                    <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                        Reason
                    </th>
                    <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                        By
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                <tr
                    v-for="m in movements.data"
                    :key="m.id"
                    class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                >
                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-navy-300">
                        {{ m.created_at }}
                    </td>

                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                        {{ typeLabel[m.type] ?? m.type }}
                    </td>

                    <td
                        class="whitespace-nowrap border border-slate-150 px-5 py-3 font-mono font-bold dark:border-slate-700"
                        :class="m.quantity_change > 0 ? 'text-success' : 'text-error'"
                    >
                        {{ m.quantity_change > 0 ? '+' : '' }}{{ m.quantity_change }}
                    </td>

                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-xs text-slate-600 dark:border-slate-700 dark:text-navy-200">
                        <span class="font-mono font-medium">{{ m.previous_quantity }}</span>
                        <span class="mx-1.5 text-slate-400 dark:text-navy-400">→</span>
                        <span class="font-mono font-semibold">{{ m.new_quantity }}</span>
                    </td>

                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-xs text-slate-600 dark:border-slate-700 dark:text-navy-200">
                        {{ m.reason ?? '—' }}
                    </td>

                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-xs font-medium text-slate-600 dark:border-slate-700 dark:text-navy-200">
                        {{ m.user?.name ?? 'system' }}
                    </td>
                </tr>

                <tr v-if="movements.data.length === 0">
                    <td
                        colspan="6"
                        class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300"
                    >
                        No movements recorded yet.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div
        v-if="movements.links.length > 3"
        class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-150 px-4 py-4 dark:border-slate-700 sm:px-5"
    >
        <nav class="flex flex-wrap gap-1">
            <template v-for="link in movements.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="inline-flex min-w-[2.25rem] items-center justify-center rounded-lg border px-3 py-2 text-xs font-semibold transition-colors"
                    :class="link.active
                        ? 'border-primary bg-primary text-white dark:border-accent dark:bg-accent'
                        : 'border-slate-200 bg-white text-slate-600 hover:bg-primary/[0.05] hover:text-primary dark:border-navy-600 dark:bg-navy-800 dark:text-navy-200 dark:hover:bg-primary/[0.08]'"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</div>

        </div>
    </div>
</template>