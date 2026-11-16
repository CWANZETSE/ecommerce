<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

interface Address {
    id: string;
    label: string | null;
    recipient_name: string;
    phone: string;
    county: string;
    town: string;
    address: string;
    instructions: string | null;
    is_default: boolean;
}

const props = defineProps<PageProps & {
    addresses: Address[];
    counties: { name: string }[];
}>();

const editingId = ref<string | null>(null);

const form = useForm({
    label: '',
    recipient_name: '',
    phone: '',
    county: '',
    town: '',
    address: '',
    instructions: '',
    is_default: false,
});

function startNew() {
    editingId.value = 'new';
    form.reset();
    form.clearErrors();
}

function startEdit(address: Address) {
    editingId.value = address.id;
    form.label = address.label ?? '';
    form.recipient_name = address.recipient_name;
    form.phone = address.phone;
    form.county = address.county;
    form.town = address.town;
    form.address = address.address;
    form.instructions = address.instructions ?? '';
    form.is_default = address.is_default;
    form.clearErrors();
}

function cancel() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
}

function submit() {
    if (editingId.value === 'new') {
        form.post('/customer/addresses', {
            preserveScroll: true,
            onSuccess: () => cancel(),
        });
    } else if (editingId.value) {
        form.patch(`/customer/addresses/${editingId.value}`, {
            preserveScroll: true,
            onSuccess: () => cancel(),
        });
    }
}

function destroy(address: Address) {
    if (!confirm(`Delete the address "${address.label ?? address.town}"?`)) return;
    router.delete(`/customer/addresses/${address.id}`, { preserveScroll: true });
}

function setDefault(address: Address) {
    router.patch(`/customer/addresses/${address.id}/default`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="My Addresses" />

        <div class="mx-auto max-w-4xl px-4 py-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-150 pb-6 dark:border-navy-600">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">My Addresses</h1>
                    <p class="text-sm text-slate-400 dark:text-navy-300 mt-1">Manage your delivery locations and shipping preferences.</p>
                </div>
                <div>
                    <button
                        v-if="editingId === null"
                        type="button"
                        class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus"
                        @click="startNew"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add address
                    </button>
                </div>
            </div>

            <!-- Form Card -->
            <div v-if="editingId !== null" class="card mt-6 p-6 dark:bg-navy-750">
                <div class="flex items-center space-x-3 border-b border-slate-150 pb-4 dark:border-navy-600">
                    <div class="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-800 dark:text-navy-50">
                            {{ editingId === 'new' ? 'Add New Delivery Address' : 'Edit Delivery Address' }}
                        </h2>
                        <p class="text-xs text-slate-400 dark:text-navy-300">Fill in the contact and location details</p>
                    </div>
                </div>

                <form class="mt-5 space-y-4" @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Label (optional)</label>
                            <input
                                v-model="form.label"
                                placeholder="e.g. Home, Office"
                                class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            />
                        </div>
                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Recipient Name</label>
                            <input
                                v-model="form.recipient_name"
                                required
                                placeholder="Full name"
                                class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            />
                            <p v-if="form.errors.recipient_name" class="mt-1 text-xs text-error">
                                {{ form.errors.recipient_name }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Phone Number</label>
                            <input
                                v-model="form.phone"
                                required
                                placeholder="0712345678"
                                class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-error">
                                {{ form.errors.phone }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">County</label>
                            <select
                                v-model="form.county"
                                required
                                class="form-select mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            >
                                <option value="">Select county…</option>
                                <option v-for="c in counties" :key="c.name" :value="c.name">{{ c.name }}</option>
                            </select>
                            <p v-if="form.errors.county" class="mt-1 text-xs text-error">
                                {{ form.errors.county }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Town / City</label>
                        <input
                            v-model="form.town"
                            required
                            placeholder="e.g. Kitengela"
                            class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                        />
                        <p v-if="form.errors.town" class="mt-1 text-xs text-error">
                            {{ form.errors.town }}
                        </p>
                    </div>

                    <div>
                        <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Street Address / Building</label>
                        <textarea
                            v-model="form.address"
                            required
                            rows="2"
                            placeholder="Street name, apartment, building number"
                            class="form-textarea mt-1.5 w-full rounded-lg bg-slate-150 p-3 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                        />
                        <p v-if="form.errors.address" class="mt-1 text-xs text-error">
                            {{ form.errors.address }}
                        </p>
                    </div>

                    <div>
                        <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Delivery Instructions (optional)</label>
                        <textarea
                            v-model="form.instructions"
                            rows="2"
                            placeholder="Specific landmarks or delivery notes"
                            class="form-textarea mt-1.5 w-full rounded-lg bg-slate-150 p-3 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                        />
                    </div>

                    <div class="flex items-center space-x-2 pt-2">
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input
                                v-model="form.is_default"
                                type="checkbox"
                                class="form-checkbox is-outline size-4 rounded-sm border-slate-400/70 bg-slate-100 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:before:bg-accent dark:checked:border-accent"
                            />
                            <span class="text-sm text-slate-600 dark:text-navy-300">Set as default shipping address</span>
                        </label>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-slate-150 dark:border-navy-600">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving…' : 'Save address' }}
                        </button>
                        <button
                            type="button"
                            class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500"
                            @click="cancel"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- List Section -->
            <div class="mt-6 space-y-4">
                <div
                    v-for="address in addresses"
                    :key="address.id"
                    class="card p-5 transition-all duration-300 hover:shadow-lg dark:bg-navy-750"
                    :class="address.is_default ? 'border-2 border-primary dark:border-accent' : 'border border-slate-150 dark:border-navy-600'"
                >
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="space-y-1.5">
                            <div class="flex items-center space-x-2">
                                <span class="text-base font-semibold text-slate-800 dark:text-navy-50">
                                    {{ address.label ?? 'Delivery Address' }}
                                </span>
                                <span v-if="address.is_default" class="badge bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light px-2.5 py-0.5 rounded-full text-xs font-semibold">
                                    Default
                                </span>
                            </div>
                            <p class="text-sm font-medium text-slate-700 dark:text-navy-200">
                                {{ address.recipient_name }} <span class="text-slate-400 mx-1">•</span> {{ address.phone }}
                            </p>
                            <p class="text-sm text-slate-500 dark:text-navy-300">
                                <span class="font-medium text-slate-700 dark:text-navy-100">{{ address.county }}</span> — {{ address.town }}
                            </p>
                            <p class="text-sm text-slate-500 dark:text-navy-300">
                                {{ address.address }}
                            </p>
                            <p v-if="address.instructions" class="text-xs italic text-slate-400 dark:text-navy-300 pt-1">
                                Note: "{{ address.instructions }}"
                            </p>
                        </div>
                        <div class="flex sm:flex-col items-end justify-between sm:justify-start gap-2 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-150 dark:border-navy-600 text-xs">
                            <button
                                type="button"
                                class="font-medium text-primary hover:text-primary-focus dark:text-accent-light dark:hover:text-accent transition-colors"
                                @click="startEdit(address)"
                            >
                                Edit
                            </button>
                            <button
                                v-if="!address.is_default"
                                type="button"
                                class="font-medium text-slate-500 hover:text-slate-800 dark:text-navy-300 dark:hover:text-navy-100 transition-colors"
                                @click="setDefault(address)"
                            >
                                Set default
                            </button>
                            <button
                                type="button"
                                class="font-medium text-error hover:text-error/80 transition-colors"
                                @click="destroy(address)"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="addresses.length === 0 && editingId === null" class="card px-6 py-12 text-center text-slate-400 dark:bg-navy-750 dark:text-navy-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto size-12 text-slate-300 dark:text-navy-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p class="text-base font-medium text-slate-600 dark:text-navy-200">No addresses saved yet</p>
                    <p class="text-sm mt-1">Add a delivery location to streamline your checkout experience.</p>
                </div>
            </div>
        </div>
</template>