<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import { admin_dashboard_index, admin_inventory_index } from '@/routes';

interface StatBlock {
    total_products: number;
    active_products: number;
    total_variations: number;
    total_customers: number;
    low_stock: number;
    out_of_stock: number;
}

interface LowStockRow {
    id: string;
    sku: string;
    stock_quantity: number;
    low_stock_threshold: number;
    product: { id: string; name: string };
}

interface OutOfStockRow {
    id: string;
    sku: string;
    product: { id: string; name: string };
}

interface Props extends PageProps {
    stats: StatBlock;
    lowStockList: LowStockRow[];
    outOfStockList: OutOfStockRow[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                Dashboard
            </h2>
        </div>

        <div class="space-y-6">
            <!-- Stat cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-5">
                <div class="card p-4 sm:p-5">
                    <p class="text-xs uppercase tracking-wider text-slate-400 dark:text-navy-300">Products</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-700 dark:text-navy-100">{{ props.stats.total_products }}</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                        {{ props.stats.active_products }} active · {{ props.stats.total_variations }} variations
                    </p>
                </div>

                <div class="card p-4 sm:p-5">
                    <p class="text-xs uppercase tracking-wider text-slate-400 dark:text-navy-300">Customers</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-700 dark:text-navy-100">{{ props.stats.total_customers }}</p>
                </div>

                <div class="card p-4 sm:p-5">
                    <p class="text-xs uppercase tracking-wider text-slate-400 dark:text-navy-300">Low stock</p>
                    <p class="mt-2 text-2xl font-semibold text-warning">{{ props.stats.low_stock }}</p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                        {{ props.stats.out_of_stock }} out of stock
                    </p>
                </div>
            </div>

            <!-- Attention lists -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Low stock -->
                <div class="card overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                        <h3 class="font-medium text-slate-700 dark:text-navy-100">Low stock — need restocking</h3>
                        <Link
                            :href="admin_inventory_index.url({ filter: 'low' })"
                            class="font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent text-sm"
                        >
                            View all
                        </Link>
                    </div>
                    <div v-if="props.lowStockList.length === 0" class="px-4 py-8 text-center text-sm text-slate-400 dark:text-navy-300">
                        No low-stock variations.
                    </div>
                    <ul v-else class="divide-y divide-slate-200 dark:divide-navy-600">
                        <li
                            v-for="row in props.lowStockList"
                            :key="row.id"
                            class="flex items-center justify-between px-4 py-3 text-sm sm:px-5 hover:bg-slate-50 dark:hover:bg-navy-900/50"
                        >
                            <div class="space-y-0.5">
                                <p class="font-medium text-slate-700 dark:text-navy-100">{{ row.product.name }}</p>
                                <p class="font-mono text-xs text-slate-400 dark:text-navy-300">{{ row.sku }}</p>
                            </div>
                            <div class="text-right space-y-0.5">
                                <p class="font-semibold text-warning">
                                    {{ row.stock_quantity }} left
                                </p>
                                <p class="text-xs text-slate-400 dark:text-navy-300">
                                    Threshold {{ row.low_stock_threshold }}
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Out of stock -->
                <div class="card overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                        <h3 class="font-medium text-slate-700 dark:text-navy-100">Out of stock</h3>
                        <Link
                            :href="admin_inventory_index.url({ filter: 'out' })"
                            class="font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent text-sm"
                        >
                            View all
                        </Link>
                    </div>
                    <div v-if="props.outOfStockList.length === 0" class="px-4 py-8 text-center text-sm text-slate-400 dark:text-navy-300">
                        Everything is in stock.
                    </div>
                    <ul v-else class="divide-y divide-slate-200 dark:divide-navy-600">
                        <li
                            v-for="row in props.outOfStockList"
                            :key="row.id"
                            class="flex items-center justify-between px-4 py-3 text-sm sm:px-5 hover:bg-slate-50 dark:hover:bg-navy-900/50"
                        >
                            <div class="space-y-0.5">
                                <p class="font-medium text-slate-700 dark:text-navy-100">{{ row.product.name }}</p>
                                <p class="font-mono text-xs text-slate-400 dark:text-navy-300">{{ row.sku }}</p>
                            </div>
                            <span class="badge bg-error/10 text-error dark:bg-error/15 font-medium">
                                Out of stock
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>