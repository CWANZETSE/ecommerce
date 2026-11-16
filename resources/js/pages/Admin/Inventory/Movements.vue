<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import { admin_inventory_index, admin_inventory_movements } from '@/routes';

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
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    Stock movements
                </h2>
                <div class="hidden h-full py-1 sm:flex">
                    <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
                </div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <Link
                            class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            :href="admin_inventory_index.url()"
                        >
                            Inventory
                        </Link>
                        <svg
                            x-ignore
                            xmlns="http://www.w3.org/2000/svg"
                            class="size-4 text-slate-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                    <li class="text-slate-500 dark:text-navy-300">Movements</li>
                </ul>
            </div>
            <Link
                :href="admin_inventory_index.url()"
                class="btn space-x-1.5 bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
            >
                <span>← Back to inventory</span>
            </Link>
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
            

            <div class="card overflow-hidden">
               <div class="is-scrollbar-x overflow-x-auto">
    <table class="is-zebra w-full text-left text-sm">
        <thead>
            <tr>
                <th class="whitespace-nowrap rounded-l-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    When
                </th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Type
                </th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Change
                </th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Before → After
                </th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Reason
                </th>
                <th class="whitespace-nowrap rounded-r-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    By
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="m in movements.data" :key="m.id">
                <td class="whitespace-nowrap rounded-l-lg px-4 py-3 text-xs text-slate-400 dark:text-navy-300 sm:px-5">
                    {{ m.created_at }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                    {{ typeLabel[m.type] ?? m.type }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 font-mono font-semibold sm:px-5" :class="m.quantity_change > 0 ? 'text-success' : 'text-error'">
                    {{ m.quantity_change > 0 ? '+' : '' }}{{ m.quantity_change }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500 dark:text-navy-300 sm:px-5">
                    <span class="font-mono">{{ m.previous_quantity }}</span> → <span class="font-mono">{{ m.new_quantity }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500 dark:text-navy-300 sm:px-5">
                    {{ m.reason ?? '—' }}
                </td>
                <td class="whitespace-nowrap rounded-r-lg px-4 py-3 text-xs text-slate-600 dark:text-navy-200 sm:px-5">
                    {{ m.user?.name ?? 'system' }}
                </td>
            </tr>
            <tr v-if="movements.data.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-slate-400 dark:text-navy-300">
                    No movements recorded yet.
                </td>
            </tr>
        </tbody>
    </table>
</div>

                <div v-if="movements.links.length > 3" class="flex items-center justify-between border-t border-slate-200 px-4 py-4 dark:border-navy-600 sm:px-5">
                    <nav class="flex flex-wrap gap-1">
                        <template v-for="link in movements.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="btn min-w-[2rem] rounded-lg border px-3 py-1.5 text-xs font-medium"
                                :class="
                                    link.active
                                        ? 'border-primary bg-primary text-white dark:border-accent dark:bg-accent'
                                        : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100 dark:border-navy-450 dark:bg-navy-750 dark:text-navy-100 dark:hover:bg-navy-700'
                                "
                                v-html="link.label"
                            />
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>