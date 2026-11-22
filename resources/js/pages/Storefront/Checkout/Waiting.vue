<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import type { PageProps } from '@/types';
import { customer_checkout_retry } from '@/routes';

interface Props extends PageProps {
    order: {
        ulid: string;
        order_number: string;
        grand_total: string;
        customer_phone: string;
    };
    payment_ulid: string | null;
}

const props = defineProps<Props>();

const status = ref<'waiting' | 'paid' | 'failed' | 'timeout'>('waiting');
const elapsed = ref(0);
const pollCount = ref(0);
let pollTimer: ReturnType<typeof setInterval> | null = null;
let clockTimer: ReturnType<typeof setInterval> | null = null;

const MAX_WAIT_SECONDS = 120;
const POLL_INTERVAL_MS = 3000;

async function checkStatus() {
    if (!props.payment_ulid) return;

    try {
        const res = await fetch(`/checkout/status/${props.payment_ulid}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();

        pollCount.value++;

        if (data.paid) {
            status.value = 'paid';
            stopPolling();
            setTimeout(() => {
                router.visit(`/checkout/success/${props.order.ulid}`);
            }, 1200);
            return;
        }

        if (data.failed) {
            status.value = 'failed';
            stopPolling();
            return;
        }

        if (elapsed.value >= MAX_WAIT_SECONDS) {
            status.value = 'timeout';
            stopPolling();
        }
    } catch {
        // Network blip — keep polling
    }
}

function stopPolling() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    if (clockTimer) { clearInterval(clockTimer); clockTimer = null; }
}

const retry = () => {
    router.post(customer_checkout_retry.url(props.order.ulid), {}, {
        preserveScroll: true,
        onError: () => {
            alert('Could not retry payment. Please try again.');
        }
    });
};

onMounted(() => {
    pollTimer = setInterval(checkStatus, POLL_INTERVAL_MS);
    clockTimer = setInterval(() => { elapsed.value++; }, 1000);
    // Initial check immediately
    checkStatus();
});

onUnmounted(stopPolling);
</script>

<template>
    <Head title="Waiting for M-Pesa" />

    <div class="mx-auto max-w-md px-4 py-12 text-center">
        <!-- Waiting -->
        <template v-if="status === 'waiting'">
            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-primary/10">
                <svg class="size-8 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
            </div>

            <h1 class="mt-6 text-lg font-semibold text-slate-800 dark:text-navy-50">
                Waiting for M-Pesa confirmation
            </h1>

            <p class="mt-2 text-sm text-slate-500 dark:text-navy-300">
                We sent a payment prompt to <strong>{{ order.customer_phone }}</strong>.
                Enter your M-Pesa PIN to complete the payment.
            </p>

            <div class="mt-6 rounded-lg border border-slate-150 p-4 dark:border-navy-600">
                <p class="text-xs text-slate-500 dark:text-navy-300">Order reference</p>
                <p class="font-mono text-sm font-medium text-slate-800 dark:text-navy-50">
                    {{ order.order_number }}
                </p>
                <p class="mt-2 text-lg font-bold text-slate-800 dark:text-navy-50">
                    KES {{ order.grand_total }}
                </p>
            </div>

            <p class="mt-4 text-xs text-slate-400 dark:text-navy-500">
                Do not close this page. Checking status… ({{ elapsed }}s)
            </p>
        </template>

        <!-- Paid -->
        <template v-else-if="status === 'paid'">
            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-success/10">
                <svg class="size-8 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="mt-6 text-lg font-semibold text-success">Payment confirmed</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-navy-300">Redirecting to your receipt…</p>
        </template>

        <!-- Failed -->
        <template v-else-if="status === 'failed'">
            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-error/10">
                <svg class="size-8 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h1 class="mt-6 text-lg font-semibold text-error">Payment failed</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-navy-300">
                The M-Pesa payment was not completed. Your order is still open.
            </p>
            <button
                type="button"
                class="btn mt-6 h-10 w-full bg-primary px-4 text-sm text-white hover:bg-primary-focus dark:bg-accent"
                @click="retry"
            >
                Try again
            </button>
        </template>

        <!-- Timeout -->
        <template v-else-if="status === 'timeout'">
            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-warning/10">
                <svg class="size-8 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="mt-6 text-lg font-semibold text-warning">Payment not confirmed</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-navy-300">
                We did not receive confirmation from M-Pesa. You can try again or contact support if money was deducted.
            </p>
            <button
                type="button"
                class="btn mt-6 h-10 w-full bg-primary px-4 text-sm text-white hover:bg-primary-focus dark:bg-accent"
                @click="retry"
            >
                Retry payment
            </button>
        </template>
    </div>
</template>