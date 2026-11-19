
<script setup lang="ts">
import CreativeDesignSVG from '@/components/SVG/creativeDesignSVG.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Form, Head, Link } from '@inertiajs/vue3';
import { ShoppingBag } from '@lucide/vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Create an account',
        description: 'Enter your details below to create your account',
    },
});
</script>

<template>
    <Head title="Register" />

    <div class="flex min-h-screen grow bg-slate-50 dark:bg-navy-900">
        <!-- Brand Logo Top Left -->
        <div class="fixed top-0 z-10 hidden p-6 lg:block lg:px-12">
            <Link href="/" class="flex shrink-0 items-center gap-2.5">
                <img
                    v-if="$page.props.store_logo_url"
                    :src="$page.props.store_logo_url"
                    :alt="$page.props.store_name"
                    class="h-30 w-auto object-contain"
                />

                <span
                    v-else
                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary font-bold text-white shadow-md dark:bg-accent"
                >
                    <ShoppingBag class="size-4.5" />
                </span>

                <span class="hidden text-lg font-bold text-slate-800 dark:text-navy-50 sm:inline">
                    {{ $page.props.store_name }}
                </span>
            </Link>
        </div>

        <!-- Left Illustration Section -->
        <div class="hidden w-full place-items-center bg-slate-100/50 dark:bg-navy-950/50 lg:grid">
            <div class="w-full max-w-lg p-6 text-center">
                <div class="mb-6 inline-flex rounded-3xl bg-primary/10 p-4 dark:bg-accent/10">
                    <CreativeDesignSVG />
                </div>
            </div>
        </div>

        <!-- Right Register Form Section -->
        <main
            class="ml-auto flex w-full flex-col items-center justify-between bg-white shadow-2xl dark:bg-navy-700 lg:max-w-md"
        >
            <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
                <!-- Header -->
                <div class="text-center">
                    <!-- Mobile Brand Logo -->
                    <div class="flex justify-center lg:hidden">
                        <Link href="/" class="flex items-center justify-center gap-2.5">
                            <img
                                v-if="$page.props.store_logo_url"
                                :src="$page.props.store_logo_url"
                                :alt="$page.props.store_name"
                                class="h-30 w-auto object-contain"
                            />

                            <span
                                v-else
                                class="flex size-14 shrink-0 items-center justify-center rounded-xl bg-primary text-2xl font-bold text-white dark:bg-accent"
                            >
                                <ShoppingBag class="size-6" />
                            </span>
                        </Link>
                    </div>

                    <div class="mt-4 lg:mt-0">
                        <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">
                            Create Account
                        </h2>

                        <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">
                            Create your account to get started
                        </p>
                    </div>
                </div>

                <!-- Register Form -->
                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="mt-8 space-y-4"
                >
                    <!-- Name -->
                    <div>
                        <label class="relative flex">
                            <input
                                id="name"
                                type="text"
                                name="name"
                                required
                                v-focus
                                tabindex="1"
                                autocomplete="name"
                                placeholder="Full name"
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2.5 pl-9 text-slate-700 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:text-navy-100 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                            />

                            <span
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="size-5 transition-colors duration-200"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0"
                                    />
                                </svg>
                            </span>
                        </label>

                        <InputError :message="errors.name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="relative flex">
                            <input
                                id="email"
                                type="email"
                                name="email"
                                required
                                tabindex="2"
                                autocomplete="email"
                                placeholder="Email address"
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2.5 pl-9 text-slate-700 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:text-navy-100 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900"
                            />

                            <span
                                class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="size-5 transition-colors duration-200"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>
                            </span>
                        </label>

                        <InputError :message="errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <PasswordInput
                            id="password"
                            required
                            tabindex="3"
                            autocomplete="new-password"
                            name="password"
                            placeholder="Password"
                            :passwordrules="passwordRules"
                        />

                        <InputError :message="errors.password" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <PasswordInput
                            id="password_confirmation"
                            required
                            tabindex="4"
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="Confirm password"
                            :passwordrules="passwordRules"
                        />

                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        tabindex="5"
                        :disabled="processing"
                        data-test="register-user-button"
                        class="btn mt-6 h-10 w-full bg-primary font-medium text-white transition-colors hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 disabled:opacity-50 dark:bg-accent dark:hover:bg-accent-focus"
                    >
                        {{ processing ? 'Creating account…' : 'Create Account' }}
                    </button>

                    <!-- Login Prompt -->
                    <div class="mt-6 text-center text-xs-plus">
                        <p class="text-slate-400 dark:text-navy-300">
                            Already have an account?

                            <Link
                                :href="login()"
                                tabindex="6"
                                class="ml-1 font-medium text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            >
                                Sign In
                            </Link>
                        </p>
                    </div>
                </Form>
            </div>

            <!-- Footer -->
            <div class="my-5 flex justify-center text-xs text-slate-400 dark:text-navy-300">
                <Link href="#" class="hover:underline">Privacy Notice</Link>

                <div class="mx-3 my-1 w-px bg-slate-200 dark:border-navy-500"></div>

                <Link href="#" class="hover:underline">Terms of Service</Link>
            </div>
        </main>
    </div>
</template>
