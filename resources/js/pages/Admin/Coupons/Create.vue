<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import { admin_coupons_index, admin_coupons_store } from '@/routes';

defineProps<PageProps>();

const form = useForm({
    code: '',
    type: 'percent' as 'percent' | 'fixed',
    value: 10,
    min_order_amount: null as number | null,
    max_discount: null as number | null,
    starts_at: '',
    expires_at: '',
    usage_limit: null as number | null,
    per_customer_limit: 1,
    first_order_only: false,
    is_active: true,
});

function submit() {
    form.post(admin_coupons_store.url());
}
</script>

<template>
    <Head title="New Coupon" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    New Coupon
                </h2>
                <div class="hidden h-full py-1 sm:flex">
                    <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
                </div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <Link
                            class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            :href="admin_coupons_index.url()"
                        >
                            Coupons
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
                    <li class="text-slate-500 dark:text-navy-300">Create</li>
                </ul>
            </div>
            <Link
                :href="admin_coupons_index.url()"
                class="btn space-x-1.5 bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
            >
                <span>← Back</span>
            </Link>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="card p-4 sm:p-5">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Code</span>
                            <input
                                v-model="form.code"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 uppercase placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                placeholder="SAVE10"
                            />
                            <p v-if="form.errors.code" class="mt-1 text-xs text-error">{{ form.errors.code }}</p>
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Type</span>
                            <select
                                v-model="form.type"
                                class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            >
                                <option value="percent">Percentage</option>
                                <option value="fixed">Fixed amount</option>
                            </select>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Value</span>
                            <input
                                v-model.number="form.value"
                                type="number"
                                step="0.01"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                            <p v-if="form.errors.value" class="mt-1 text-xs text-error">{{ form.errors.value }}</p>
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Min order (optional)</span>
                            <input
                                v-model.number="form.min_order_amount"
                                type="number"
                                step="0.01"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Max discount cap (optional)</span>
                            <input
                                v-model.number="form.max_discount"
                                type="number"
                                step="0.01"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Per-customer limit</span>
                            <input
                                v-model.number="form.per_customer_limit"
                                type="number"
                                min="1"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                        </label>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Starts at</span>
                            <input
                                v-model="form.starts_at"
                                type="datetime-local"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Expires at</span>
                            <input
                                v-model="form.expires_at"
                                type="datetime-local"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                            <p v-if="form.errors.expires_at" class="mt-1 text-xs text-error">{{ form.errors.expires_at }}</p>
                        </label>
                    </div>

                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Total usage limit (optional)</span>
                        <input
                            v-model.number="form.usage_limit"
                            type="number"
                            min="1"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                    </label>

                    <div class="flex flex-wrap gap-6 pt-2">
                        <label class="inline-flex items-center space-x-2">
                            <input
                                v-model="form.first_order_only"
                                type="checkbox"
                                class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 bg-slate-100 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                            />
                            <span class="font-medium text-slate-700 dark:text-navy-100 text-sm">First order only</span>
                        </label>
                        <label class="inline-flex items-center space-x-2">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 bg-slate-100 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                            />
                            <span class="font-medium text-slate-700 dark:text-navy-100 text-sm">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                >
                    {{ form.processing ? 'Saving…' : 'Create Coupon' }}
                </button>
            </div>
        </form>
    </div>
</template>