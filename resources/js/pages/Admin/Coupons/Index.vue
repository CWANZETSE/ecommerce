<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_coupons_create,
    admin_coupons_edit,
    admin_coupons_destroy,
} from '@/routes';
import EmptySVG from '@/components/SVG/emptySVG.vue';
import Pagination from '@/components/Pagination.vue';

interface Coupon {
    id: string;
    code: string;
    type: 'percent' | 'fixed';
    value: string;
    min_order_amount: string | null;
    max_discount: string | null;
    starts_at: string | null;
    expires_at: string | null;
    usage_limit: number | null;
    per_customer_limit: number;
    first_order_only: boolean;
    is_active: boolean;
    usages_count: number;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<PageProps & { coupons: Paginator<Coupon> }>();

function destroy(coupon: Coupon) {
    if (confirm(`Delete coupon "${coupon.code}"?`)) {
        router.delete(admin_coupons_destroy.url(coupon.id));
    }
}
</script>

<template>
    <Head title="Coupons" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    Coupons
                </h2>
            </div>
            <Link
                :href="admin_coupons_create.url()"
                class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
            >
                New Coupon
            </Link>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300">
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Code
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Type
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Value
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Min order
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Used
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Expires
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                Status
                            </th>
                            <th class="whitespace-nowrap border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                        <!-- Loop through coupons.data -->
                        <tr
                            v-for="c in coupons.data"
                            :key="c.id"
                            class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                        >
                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 font-mono text-xs font-bold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                {{ c.code }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 capitalize text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                {{ c.type }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                {{ c.type === 'percent' ? `${c.value}%` : `KES ${c.value}` }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                {{ c.min_order_amount ? `KES ${c.min_order_amount}` : '—' }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-slate-600 dark:border-slate-700 dark:text-navy-200">
                                {{ c.usages_count }}<span v-if="c.usage_limit">/{{ c.usage_limit }}</span>
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-navy-300">
                                {{ c.expires_at ?? '—' }}
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <span
                                    :class="c.is_active
                                        ? 'bg-success/10 text-success'
                                        : 'bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-300'"
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                >
                                    {{ c.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="admin_coupons_edit.url(c.id)"
                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-primary/20 bg-primary/[0.06] px-3 text-xs font-semibold text-primary transition-colors hover:bg-primary/15 focus:bg-primary/15 active:bg-primary/20 dark:border-accent-light/20 dark:bg-accent-light/[0.06] dark:text-accent-light dark:hover:bg-accent-light/15"
                                    >
                                        Edit
                                    </Link>

                                    <button
                                        type="button"
                                        class="inline-flex h-9 items-center justify-center rounded-lg border border-secondary/20 bg-secondary/[0.06] px-3 text-xs font-semibold text-secondary transition-colors hover:bg-secondary/15 focus:bg-secondary/15 active:bg-secondary/20 dark:border-secondary-light/20 dark:bg-secondary-light/[0.06] dark:text-secondary-light dark:hover:bg-secondary-light/15"
                                        @click="destroy(c)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Check coupons.data.length -->
                        <tr v-if="coupons.data.length === 0">
                            <td
                                colspan="8"
                                class="border border-slate-150 px-5 py-10 dark:border-slate-700"
                            >
                                <div class="flex flex-col items-center justify-center gap-3 text-sm text-slate-400 dark:text-navy-300">
                                    <EmptySVG />
                                    <span>No coupons yet.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Add unified pagination component -->
            <Pagination :paginator="coupons" />
            
        </div>
    </div>
</template>