<script setup lang="ts">
import CreativeDesignSVG from '@/components/SVG/creativeDesignSVG.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Sign In" />

    <!-- App preloader simulation wrapper -->
    <div class="min-h-screen flex grow bg-slate-50 dark:bg-navy-900">
        <!-- Brand Logo Top Left -->
        <div class="fixed top-0 hidden p-6 lg:block lg:px-12 z-10">
            <Link href="/" class="flex items-center space-x-2">
                <div class="flex size-10 items-center justify-center rounded-lg bg-primary text-white font-bold text-lg dark:bg-accent">
                    L
                </div>
                <p class="text-xl font-semibold uppercase text-slate-700 dark:text-navy-100 tracking-wider">
                    Storefront
                </p>
            </Link>
        </div>

        <!-- Left Illustration Section (Hidden on Mobile) -->
        <div class="hidden w-full place-items-center lg:grid bg-slate-100/50 dark:bg-navy-950/50">
            <div class="w-full max-w-lg p-6 text-center">
                <div class="mb-6 inline-flex p-4 rounded-3xl bg-primary/10 dark:bg-accent/10">
                    <CreativeDesignSVG/>
                </div>
            </div>
        </div>

        <!-- Right Login Form Section -->
        <main class="flex w-full flex-col items-center justify-between bg-white dark:bg-navy-700 lg:max-w-md ml-auto shadow-2xl">
            <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
                <div class="text-center">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-xl bg-primary text-white font-bold text-2xl lg:hidden dark:bg-accent">
                        L
                    </div>
                    <div class="mt-4 lg:mt-0">
                        <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">
                            Welcome Back
                        </h2>
                        <p class="text-slate-400 dark:text-navy-300 text-sm mt-1">
                            Please sign in to continue
                        </p>
                    </div>
                </div>

                <form class="mt-10 space-y-4" @submit.prevent="submit">
                    <!-- Email Field -->
                    <div>
                        <label class="relative flex">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="Email address"
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2.5 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            />
                            <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                        </label>
                        <p v-if="form.errors.email" class="mt-1 text-xs text-error">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label class="relative flex">
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                placeholder="Password"
                                class="form-input peer w-full rounded-lg bg-slate-150 px-3 py-2.5 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring-3 dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900 text-slate-700 dark:text-navy-100"
                            />
                            <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                        </label>
                        <p v-if="form.errors.password" class="mt-1 text-xs text-error">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between text-sm">
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="form-checkbox is-outline size-4 rounded-sm border-slate-400/70 bg-slate-100 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:before:bg-accent dark:checked:border-accent"
                            />
                            <span class="text-slate-600 dark:text-navy-300">Remember me</span>
                        </label>
                        <Link href="/forgot-password" class="text-xs text-slate-400 transition-colors hover:text-slate-800 dark:text-navy-300 dark:hover:text-navy-100">
                            Forgot Password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="btn mt-6 h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus disabled:opacity-50 transition-colors"
                    >
                        {{ form.processing ? 'Signing in…' : 'Sign In' }}
                    </button>
                </form>

                <!-- Registration Prompt -->
                <div class="mt-6 text-center text-xs-plus">
                    <p class="text-slate-400 dark:text-navy-300">
                        Don't have an account?
                        <Link href="/register" class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent font-medium ml-1">
                            Create account
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Footer Links -->
            <div class="my-5 flex justify-center text-xs text-slate-400 dark:text-navy-300">
                <Link href="#" class="hover:underline">Privacy Notice</Link>
                <div class="mx-3 my-1 w-px bg-slate-200 dark:bg-navy-500"></div>
                <Link href="#" class="hover:underline">Terms of Service</Link>
            </div>
        </main>
    </div>
</template>