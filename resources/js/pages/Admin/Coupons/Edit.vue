<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { PageProps } from '@/types';
import { admin_coupons_index, admin_coupons_update, admin_coupons_destroy } from '@/routes';

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
    usages_count?: number;
}

const props = defineProps<PageProps & { coupon: Coupon }>();

const hasUsages = computed(() => (props.coupon.usages_count ?? 0) > 0);
const isPercent = computed(() => form.type === 'percent');

function toDatetimeLocal(value: string | null): string {
    if (!value) return '';
    return value.slice(0, 16).replace(' ', 'T');
}

const form = useForm({
    code: props.coupon.code,
    type: props.coupon.type,
    value: Number(props.coupon.value),
    min_order_amount: props.coupon.min_order_amount ? Number(props.coupon.min_order_amount) : null,
    max_discount: props.coupon.max_discount ? Number(props.coupon.max_discount) : null,
    starts_at: toDatetimeLocal(props.coupon.starts_at),
    expires_at: toDatetimeLocal(props.coupon.expires_at),
    usage_limit: props.coupon.usage_limit,
    per_customer_limit: props.coupon.per_customer_limit,
    first_order_only: props.coupon.first_order_only,
    is_active: props.coupon.is_active,
});

function submit() {
    form.put(admin_coupons_update.url(props.coupon.id));
}

function destroy() {
    if (hasUsages.value) return;
    if (!confirm(`Delete coupon "${props.coupon.code}"? This cannot be undone.`)) return;
    form.delete(admin_coupons_destroy.url(props.coupon.id));
}
</script>

<template>
    <Head :title="`Edit — ${coupon.code}`" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <div class="space-y-1">
                    <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                        Edit Coupon
                    </h2>
                    <p class="font-mono text-xs text-slate-400 dark:text-navy-300">{{ coupon.code }}</p>
                </div>
            </div>
            <Link
                :href="admin_coupons_index.url()"
                class="btn space-x-1.5 bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
            >
                <span>← Back</span>
            </Link>
        </div>

        <div class="space-y-6">
            <!-- Locked warning -->
            <div
                v-if="hasUsages"
                class="alert rounded-lg border border-warning/30 bg-warning/10 px-4 py-3 text-sm text-warning dark:border-warning/40 dark:bg-warning/15"
            >
                This coupon has been used <strong class="font-semibold">{{ props.coupon.usages_count }}</strong>
                time{{ props.coupon.usages_count === 1 ? '' : 's' }}.
                The <strong class="font-semibold">code</strong>, <strong class="font-semibold">type</strong>, and <strong class="font-semibold">value</strong> are locked
                to preserve order history. You can still edit limits and dates, or deactivate it.
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <div class="card p-4 sm:p-5">
                    <div class="space-y-4">
                        <!-- Code + Type -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100">Code</span>
                                <input
                                    v-model="form.code"
                                    :disabled="hasUsages"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                />
                                <p v-if="hasUsages" class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                    Code cannot be changed after first use.
                                </p>
                                <p v-if="form.errors.code" class="mt-1 text-xs text-error">{{ form.errors.code }}</p>
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100">Type</span>
                                <select
                                    v-model="form.type"
                                    :disabled="hasUsages"
                                    class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                >
                                    <option value="percent">Percentage</option>
                                    <option value="fixed">Fixed amount</option>
                                </select>
                                <p v-if="hasUsages" class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                    Type cannot be changed after first use.
                                </p>
                                <p v-if="form.errors.type" class="mt-1 text-xs text-error">{{ form.errors.type }}</p>
                            </label>
                        </div>

                        <!-- Value + Min order -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100">
                                    {{ isPercent ? 'Percent off' : 'Amount off (KES)' }}
                                </span>
                                <input
                                    v-model.number="form.value"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    :disabled="hasUsages"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                />
                                <p v-if="hasUsages" class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                    Value cannot be changed after first use.
                                </p>
                                <p v-if="form.errors.value" class="mt-1 text-xs text-error">{{ form.errors.value }}</p>
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100">Min order (optional)</span>
                                <input
                                    v-model.number="form.min_order_amount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                />
                                <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                    Applies to future redemptions only.
                                </p>
                                <p v-if="form.errors.min_order_amount" class="mt-1 text-xs text-error">{{ form.errors.min_order_amount }}</p>
                            </label>
                        </div>

                        <!-- Max discount + per-customer limit -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100">Max discount cap (optional)</span>
                                <input
                                    v-model.number="form.max_discount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                />
                                <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                    Limits the discount amount on percentage coupons.
                                </p>
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100">Per-customer limit</span>
                                <input
                                    v-model.number="form.per_customer_limit"
                                    type="number"
                                    min="1"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                />
                                <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                    Maximum uses per customer.
                                </p>
                            </label>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100">Starts at (optional)</span>
                                <input
                                    v-model="form.starts_at"
                                    type="datetime-local"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                />
                            </label>
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100">Expires at (optional)</span>
                                <input
                                    v-model="form.expires_at"
                                    type="datetime-local"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                />
                                <p v-if="form.errors.expires_at" class="mt-1 text-xs text-error">{{ form.errors.expires_at }}</p>
                            </label>
                        </div>

                        <!-- Total usage limit -->
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Total usage limit (optional)</span>
                            <input
                                v-model.number="form.usage_limit"
                                type="number"
                                min="1"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                Leave empty for unlimited redemptions.
                            </p>
                        </label>

                        <!-- Flags -->
                        <div class="flex flex-wrap gap-6 border-t border-slate-200 pt-4 dark:border-navy-600">
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

                <!-- Actions -->
                <div class="flex flex-wrap justify-end items-center gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                    >
                        {{ form.processing ? 'Saving…' : 'Save changes' }}
                    </button>

                    <button
                        v-if="!hasUsages"
                        type="button"
                        class="btn border border-secondary/30 bg-secondary/10 font-medium text-secondary hover:bg-secondary/20 focus:bg-secondary/20 active:bg-secondary/25 dark:border-secondary-light/30 dark:bg-secondary-light/10 dark:text-secondary-light dark:hover:bg-secondary-light/20 dark:focus:bg-secondary-light/20 dark:active:bg-secondary-light/25"
                        @click="destroy"
                    >
                        Delete coupon
                    </button>
                    <p v-else class="text-xs text-slate-400 dark:text-navy-300">
                        This coupon cannot be deleted. Uncheck <strong class="font-semibold">Active</strong> above to disable it instead.
                    </p>
                </div>
            </form>
        </div>
    </div>
</template>