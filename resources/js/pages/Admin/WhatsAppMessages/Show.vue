
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface WhatsAppMessage {
    id: number;
    recipient: string;
    wamid: string | null;
    notification_id: string | null;
    notification_type: string | null;
    message_type: string;
    template_name: string | null;
    status: string;
    error_code: string | null;
    error_message: string | null;
    provider_response: Record<string, unknown> | null;
    sent_at: string | null;
    delivered_at: string | null;
    read_at: string | null;
    failed_at: string | null;
    created_at: string | null;
    updated_at: string | null;
}

defineProps<{
    message: WhatsAppMessage;
}>();

function formatDate(value: string | null): string {
    if (!value) return '—';

    return new Intl.DateTimeFormat('en-KE', {
        dateStyle: 'medium',
        timeStyle: 'long',
    }).format(new Date(value));
}

function statusClass(status: string): string {
    const classes: Record<string, string> = {
        pending: 'bg-warning/10 text-warning',
        sent: 'bg-info/10 text-info',
        delivered: 'bg-success/10 text-success',
        read: 'bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light',
        failed: 'bg-danger/10 text-danger',
    };

    return classes[status] ?? 'bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-300';
}

function prettyJson(value: Record<string, unknown> | null): string {
    return value ? JSON.stringify(value, null, 2) : 'No provider response recorded.';
}
</script>

<template>
    <Head title="WhatsApp Message Details" />

    <div class="p-4 sm:p-5 lg:p-6">
        <!-- Heading -->
        <div class="flex flex-col justify-between gap-3 py-5 sm:flex-row sm:items-center lg:py-6">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                        WhatsApp Message Details
                    </h2>

                    <span
                        class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold capitalize"
                        :class="statusClass(message.status)"
                    >
                        {{ message.status }}
                    </span>
                </div>

                <p class="mt-1 text-sm text-slate-500 dark:text-navy-300">
                    Review message delivery progress and provider information.
                </p>
            </div>

            <Link
                href="/admin/whatsapp-messages"
                class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-navy-200 dark:hover:bg-navy-700"
            >
                Back to messages
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <!-- Main information -->
            <div class="space-y-5 xl:col-span-2">
                <div class="rounded-2xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800">
                    <h3 class="font-semibold text-slate-800 dark:text-navy-50">
                        Message information
                    </h3>

                    <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-navy-300">Recipient</dt>
                            <dd class="mt-1 break-all text-sm font-semibold text-slate-800 dark:text-navy-100">
                                {{ message.recipient }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-navy-300">Message type</dt>
                            <dd class="mt-1 text-sm font-semibold capitalize text-slate-800 dark:text-navy-100">
                                {{ message.message_type }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-navy-300">Notification</dt>
                            <dd class="mt-1 break-all text-sm text-slate-700 dark:text-navy-100">
                                {{ message.notification_type || '—' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-navy-300">Template name</dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-navy-100">
                                {{ message.template_name || '—' }}
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-slate-500 dark:text-navy-300">WhatsApp message ID</dt>
                            <dd class="mt-1 break-all font-mono text-xs text-slate-700 dark:text-navy-100">
                                {{ message.wamid || 'Not assigned' }}
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium text-slate-500 dark:text-navy-300">Notification ID</dt>
                            <dd class="mt-1 break-all font-mono text-xs text-slate-700 dark:text-navy-100">
                                {{ message.notification_id || '—' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Delivery timeline -->
                <div class="rounded-2xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800">
                    <h3 class="font-semibold text-slate-800 dark:text-navy-50">
                        Delivery timeline
                    </h3>

                    <div class="mt-5 space-y-5">
                        <div
                            v-for="step in [
                                { label: 'Message created', date: message.created_at, description: 'The notification was recorded by the application.' },
                                { label: 'Sent', date: message.sent_at, description: 'The message was accepted for sending.' },
                                { label: 'Delivered', date: message.delivered_at, description: 'WhatsApp reported that the message reached the recipient.' },
                                { label: 'Read', date: message.read_at, description: 'WhatsApp reported that the recipient read the message.' },
                                { label: 'Failed', date: message.failed_at, description: 'WhatsApp reported a delivery failure.' },
                            ]"
                            :key="step.label"
                            class="flex gap-3"
                        >
                            <div class="flex flex-col items-center">
                                <span
                                    class="mt-1 size-3 shrink-0 rounded-full"
                                    :class="step.date
                                        ? step.label === 'Failed' ? 'bg-danger' : 'bg-success'
                                        : 'bg-slate-300 dark:bg-navy-500'"
                                />
                                <span class="mt-1 min-h-8 w-px flex-1 bg-slate-200 dark:bg-slate-600" />
                            </div>

                            <div class="min-w-0 flex-1 pb-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-navy-100">
                                        {{ step.label }}
                                    </p>
                                    <span class="text-xs text-slate-500 dark:text-navy-300">
                                        {{ formatDate(step.date) }}
                                    </span>
                                </div>

                                <p class="mt-1 text-xs text-slate-500 dark:text-navy-300">
                                    {{ step.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Failure information -->
                <div
                    v-if="message.status === 'failed' || message.error_code || message.error_message"
                    class="rounded-2xl border border-danger/20 bg-danger/[0.04] p-5 dark:border-danger/30 dark:bg-danger/[0.06]"
                >
                    <h3 class="font-semibold text-danger">
                        Delivery failure information
                    </h3>

                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-navy-300">Error code</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-navy-100">
                                {{ message.error_code || 'Not provided' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-slate-500 dark:text-navy-300">Failure time</p>
                            <p class="mt-1 text-sm text-slate-800 dark:text-navy-100">
                                {{ formatDate(message.failed_at) }}
                            </p>
                        </div>
                    </div>

                    <div v-if="message.error_message" class="mt-3">
                        <p class="text-xs font-medium text-slate-500 dark:text-navy-300">Error message</p>
                        <p class="mt-1 whitespace-pre-wrap text-sm text-danger">
                            {{ message.error_message }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Provider information -->
            <div class="space-y-5">
                <div class="rounded-2xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800">
                    <h3 class="font-semibold text-slate-800 dark:text-navy-50">
                        Record timestamps
                    </h3>

                    <dl class="mt-4 space-y-4">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-navy-300">Created</dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-navy-100">
                                {{ formatDate(message.created_at) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-navy-300">Last updated</dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-navy-100">
                                {{ formatDate(message.updated_at) }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-slate-150 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-navy-800">
                    <h3 class="font-semibold text-slate-800 dark:text-navy-50">
                        Provider response
                    </h3>
                    <pre class="mt-4 max-h-[480px] overflow-auto rounded-lg bg-slate-50 p-3 text-xs leading-relaxed text-slate-700 dark:bg-navy-900 dark:text-navy-200">{{ prettyJson(message.provider_response) }}</pre>
                </div>
            </div>
        </div>
    </div>
</template>
