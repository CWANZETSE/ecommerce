<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Profile settings" />

    <StorefrontLayout>
        <div class="mx-auto max-w-4xl px-4 py-8 space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-150 pb-6 dark:border-navy-600">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">Profile Settings</h1>
                    <p class="text-sm text-slate-400 dark:text-navy-300 mt-1">Manage your account credentials, profile information, and email status.</p>
                </div>
            </div>

            <!-- Profile Form Card -->
            <div class="card p-6 dark:bg-navy-750 border border-slate-150 dark:border-navy-600 space-y-6">
                <Heading
                    variant="small"
                    title="Profile Information"
                    description="Update your name and email address"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-5 pt-2"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="name" class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Name</Label>
                        <Input
                            id="name"
                            class="form-input mt-1 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError class="mt-1 text-xs text-error" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email" class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="form-input mt-1 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-1 text-xs text-error" :message="errors.email" />
                    </div>

                    <div v-if="page.props.mustVerifyEmail && !user.email_verified_at" class="rounded-lg bg-warning/10 p-4 text-warning">
                        <p class="text-sm">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="font-medium underline underline-offset-4 hover:opacity-80"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="page.props.status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-success"
                        >
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-2 border-t border-slate-150 dark:border-navy-600">
                        <Button 
                            :disabled="processing" 
                            data-test="update-profile-button"
                            class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus disabled:opacity-50"
                        >
                            Save changes
                        </Button>
                    </div>
                </Form>
            </div>

            <!-- Delete User Section Wrapper -->
            <div class="card p-6 dark:bg-navy-750 border border-slate-150 dark:border-navy-600">
                <DeleteUser />
            </div>
        </div>
    </StorefrontLayout>
</template>