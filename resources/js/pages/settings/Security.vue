<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';
import type { Props as ManagePasskeysProps } from '@/components/ManagePasskeys.vue';
import ManagePasskeys from '@/components/ManagePasskeys.vue';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';

// oxfmt-ignore
type Props = {
    passwordRules: string;
} & ManagePasskeysProps &
    ManageTwoFactorProps;

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Security settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Security settings" />

    <StorefrontLayout>
        <div class="mx-auto max-w-4xl px-4 py-8 space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-150 pb-6 dark:border-navy-600">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-navy-50">Security Settings</h1>
                    <p class="text-sm text-slate-400 dark:text-navy-300 mt-1">Manage your account password, two-factor authentication, and passkeys.</p>
                </div>
            </div>

            <!-- Password Update Card -->
            <div class="card p-6 dark:bg-navy-750 border border-slate-150 dark:border-navy-600 space-y-6">
                <Heading
                    variant="small"
                    title="Update Password"
                    description="Ensure your account is using a long, random password to stay secure"
                />

                <Form
                    v-bind="SecurityController.update.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    class="space-y-5 pt-2"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="current_password" class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Current password</Label>
                        <PasswordInput
                            id="current_password"
                            name="current_password"
                            class="form-input mt-1 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            autocomplete="current-password"
                            placeholder="Current password"
                        />
                        <InputError class="mt-1 text-xs text-error" :message="errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password" class="text-xs+ font-medium text-slate-600 dark:text-navy-200">New password</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            class="form-input mt-1 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            autocomplete="new-password"
                            placeholder="New password"
                            :passwordrules="props.passwordRules"
                        />
                        <InputError class="mt-1 text-xs text-error" :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="text-xs+ font-medium text-slate-600 dark:text-navy-200">Confirm password</Label>
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-input mt-1 w-full rounded-lg bg-slate-150 px-3 py-2.5 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                            :passwordrules="props.passwordRules"
                        />
                        <InputError class="mt-1 text-xs text-error" :message="errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4 pt-2 border-t border-slate-150 dark:border-navy-600">
                        <Button
                            :disabled="processing"
                            data-test="update-password-button"
                            class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus disabled:opacity-50"
                        >
                            Save password
                        </Button>
                    </div>
                </Form>
            </div>

            <!-- Two-Factor Authentication Card Container -->
            <div class="card p-6 dark:bg-navy-750 border border-slate-150 dark:border-navy-600">
                <ManageTwoFactor
                    :canManageTwoFactor="canManageTwoFactor"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>

            <!-- Passkeys Card Container -->
            <div class="card p-6 dark:bg-navy-750 border border-slate-150 dark:border-navy-600">
                <ManagePasskeys
                    :canManagePasskeys="canManagePasskeys"
                    :passkeys="passkeys"
                />
            </div>
        </div>
    </StorefrontLayout>
</template>