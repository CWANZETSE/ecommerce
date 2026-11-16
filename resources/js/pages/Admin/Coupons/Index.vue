<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_coupons_index,
    admin_coupons_create,
    admin_coupons_edit,
    admin_coupons_destroy,
} from '@/routes';
import EmptySVG from '@/components/SVG/emptySVG.vue';

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

const props = defineProps<PageProps & { coupons: Coupon[] }>();

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

        <div class="card overflow-hidden">
            <div class="is-scrollbar-x overflow-x-auto">
    <table class="is-zebra w-full text-left text-sm">
        <thead>
            <tr>
                <th class="whitespace-nowrap rounded-l-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Code
                </th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Type
                </th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Value
                </th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Min order
                </th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Used
                </th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Expires
                </th>
                <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Status
                </th>
                <th class="whitespace-nowrap rounded-r-lg bg-slate-200 px-4 py-3 text-right font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 sm:px-5">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="c in coupons" :key="c.id">
                <td class="whitespace-nowrap rounded-l-lg px-4 py-3 font-mono text-xs font-semibold text-slate-700 dark:text-navy-100 sm:px-5">
                    {{ c.code }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-slate-600 capitalize dark:text-navy-200 sm:px-5">
                    {{ c.type }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                    {{ c.type === 'percent' ? `${c.value}%` : `KES ${c.value}` }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                    {{ c.min_order_amount ? `KES ${c.min_order_amount}` : '—' }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-navy-200 sm:px-5">
                    {{ c.usages_count }}<span v-if="c.usage_limit">/{{ c.usage_limit }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-400 dark:text-navy-300 sm:px-5">
                    {{ c.expires_at ?? '—' }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                    <span
                        :class="
                            c.is_active
                                ? 'badge bg-success/10 text-success dark:bg-success/15'
                                : 'badge bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200'
                        "
                    >
                        {{ c.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="whitespace-nowrap rounded-r-lg px-4 py-3 text-right sm:px-5">
                    <div class="inline-flex items-center space-x-3">
                        <Link
                            :href="admin_coupons_edit.url(c.id)"
                            class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                        >
                            Edit
                        </Link>
                        <button
                            type="button"
                            class="btn border border-secondary/30 bg-secondary/10 font-medium text-secondary hover:bg-secondary/20 focus:bg-secondary/20 active:bg-secondary/25 dark:border-secondary-light/30 dark:bg-secondary-light/10 dark:text-secondary-light dark:hover:bg-secondary-light/20 dark:focus:bg-secondary-light/20 dark:active:bg-secondary-light/25"
                            @click="destroy(c)"
                        >
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
            <tr v-if="coupons.length === 0">
                <td colspan="8" class="px-4 py-8 text-center">
    <div class="flex flex-col items-center justify-center gap-3 text-slate-400 dark:text-navy-300">
        <EmptySVG />
        <span>No coupons yet.</span>
    </div>
</td>
            </tr>
        </tbody>
    </table>
</div>
        </div>
    </div>
</template>