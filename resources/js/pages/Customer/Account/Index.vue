<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import ShoppingSVG from '@/components/SVG/shoppingSVG.vue';
import CartSVG from '@/components/SVG/cartSVG.vue';
import ShippingSVG from '@/components/SVG/shippingSVG.vue';
import NoteSVG from '@/components/SVG/NoteSVG.vue';
import UserSVG from '@/components/SVG/userSVG.vue';
import KeySVG from '@/components/SVG/keySVG.vue';

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
                        <CartSVG/>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 dark:text-navy-300">Total Orders</p>
                        <p class="mt-0.5 text-xl font-bold text-slate-800 dark:text-navy-50">{{ stats.orders }}</p>
                    </div>
                </div>

                <!-- Addresses Stat -->
                <div class="card flex items-center p-4 dark:bg-navy-750">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-info/10 text-info mr-4">
                        <ShippingSVG/>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 dark:text-navy-300">Saved Addresses</p>
                        <p class="mt-0.5 text-xl font-bold text-slate-800 dark:text-navy-50">{{ stats.addresses }}</p>
                    </div>
                </div>

                <!-- Reviews Stat -->
                <div class="card flex items-center p-4 dark:bg-navy-750">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-warning/10 text-warning mr-4">
                        <NoteSVG/>
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
                            <UserSVG/>
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
                            <KeySVG/>
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