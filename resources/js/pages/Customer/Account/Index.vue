<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
}

const props = defineProps<PageProps & {
    user: User;
    stats: { orders: number; addresses: number; reviews: number };
}>();

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function saveProfile() {
    profileForm.patch('/customer/account', { preserveScroll: true });
}

function savePassword() {
    passwordForm.patch('/customer/account/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
}
</script>

<template>
    <Head title="My Account" />

        <div class="mx-auto max-w-4xl px-4 py-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-150 pb-6 dark:border-navy-600">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">My Account</h1>
                    <p class="text-sm text-slate-400 dark:text-navy-300 mt-1">Manage your account settings, profile information, and security preferences.</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="badge bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light px-3 py-1 rounded-full text-xs font-semibold">
                        Customer Member
                    </span>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <!-- Orders Stat -->
                <div class="card flex items-center p-4 dark:bg-navy-750">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 dark:text-navy-300">Total Orders</p>
                        <p class="mt-0.5 text-xl font-bold text-slate-800 dark:text-navy-50">{{ stats.orders }}</p>
                    </div>
                </div>

                <!-- Addresses Stat -->
                <div class="card flex items-center p-4 dark:bg-navy-750">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-info/10 text-info mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 dark:text-navy-300">Saved Addresses</p>
                        <p class="mt-0.5 text-xl font-bold text-slate-800 dark:text-navy-50">{{ stats.addresses }}</p>
                    </div>
                </div>

                <!-- Reviews Stat -->
                <div class="card flex items-center p-4 dark:bg-navy-750">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-warning/10 text-warning mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 dark:text-navy-300">Product Reviews</p>
                        <p class="mt-0.5 text-xl font-bold text-slate-800 dark:text-navy-50">{{ stats.reviews }}</p>
                    </div>
                </div>
            </div>

            <!-- Profile & Password Management Grid -->
            <div class="mt-8 grid gap-8 lg:grid-cols-2">
                <!-- Profile Information Card -->
                <section class="card p-6 dark:bg-navy-750">
                    <div class="flex items-center space-x-3 border-b border-slate-150 pb-4 dark:border-navy-600">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-slate-800 dark:text-navy-50">Profile Information</h2>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Update your account profile details</p>
                        </div>
                    </div>

                    <form class="mt-5 space-y-4" @submit.prevent="saveProfile">
                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Full Name</label>
                            <input
                                v-model="profileForm.name"
                                type="text"
                                required
                                class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            />
                            <p v-if="profileForm.errors.name" class="mt-1 text-xs text-error">
                                {{ profileForm.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Email Address</label>
                            <input
                                v-model="profileForm.email"
                                type="email"
                                required
                                class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            />
                            <p v-if="profileForm.errors.email" class="mt-1 text-xs text-error">
                                {{ profileForm.errors.email }}
                            </p>
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                :disabled="profileForm.processing"
                                class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                            >
                                {{ profileForm.processing ? 'Saving changes…' : 'Save profile' }}
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Change Password Card -->
                <section class="card p-6 dark:bg-navy-750">
                    <div class="flex items-center space-x-3 border-b border-slate-150 pb-4 dark:border-navy-600">
                        <div class="flex size-9 items-center justify-center rounded-lg bg-warning/10 text-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-slate-800 dark:text-navy-50">Update Password</h2>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Ensure your account is using a secure password</p>
                        </div>
                    </div>

                    <form class="mt-5 space-y-4" @submit.prevent="savePassword">
                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Current Password</label>
                            <input
                                v-model="passwordForm.current_password"
                                type="password"
                                required
                                class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            />
                            <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-error">
                                {{ passwordForm.errors.current_password }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">New Password</label>
                            <input
                                v-model="passwordForm.password"
                                type="password"
                                required
                                class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            />
                            <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-error">
                                {{ passwordForm.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Confirm New Password</label>
                            <input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                required
                                class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            />
                        </div>

                        <div class="pt-2">
                            <button
                                type="submit"
                                :disabled="passwordForm.processing"
                                class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                            >
                                {{ passwordForm.processing ? 'Updating…' : 'Update password' }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
</template>