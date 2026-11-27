<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface SmsMessage {
    id: number;
    recipient: string;
    notification_type: string | null;
    notification_id: string | null;
    message_id: string | null;
    status: string;
    status_code: number | null;
    status_description: string | null;
    cost: string | null;
    failure_reason: string | null;
    provider_response: Record<string, unknown> | null;
    created_at: string | null;
    sent_at: string | null;
    failed_at: string | null;
}

defineProps<{
    message: SmsMessage;
}>();

function statusClasses(status: string): string {
    switch (status.toLowerCase()) {
        case 'processed':
        case 'sent':
        case 'queued':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400';

        case 'failed':
            return 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400';

        case 'pending':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400';

        default:
            return 'bg-gray-100 text-gray-700 dark:bg-navy-700 dark:text-gray-300';
    }
}

function displayStatus(status: string): string {
    return status.charAt(0).toUpperCase() + status.slice(1);
}
</script>

<template>
    <div class="space-y-6 p-4 sm:p-6">
        <!-- Heading -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2">
                    <Link
                        href="/admin/africas-talking/sms"
                        class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-primary dark:text-gray-400"
                    >
                        <span aria-hidden="true">←</span>
                        SMS messages
                    </Link>
                </div>

                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    SMS message details
                </h1>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Submission record #{{ message.id }}
                </p>
            </div>

            <span
                class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-semibold"
                :class="statusClasses(message.status)"
            >
                {{ displayStatus(message.status) }}
            </span>
        </div>

        <!-- Main details -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-navy-700 dark:bg-navy-800">
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Message information
                    </h2>

                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Recipient
                            </p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                {{ message.recipient }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Notification
                            </p>
                            <p class="mt-1 break-words text-sm text-gray-800 dark:text-gray-200">
                                {{ message.notification_type || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Notification ID
                            </p>
                            <p class="mt-1 break-all font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ message.notification_id || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Africa's Talking message ID
                            </p>
                            <p class="mt-1 break-all font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ message.message_id || 'Not provided' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Provider status code
                            </p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                {{ message.status_code ?? '—' }}
                                <span
                                    v-if="message.status_description"
                                    class="font-normal text-gray-500 dark:text-gray-400"
                                >
                                    · {{ message.status_description }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Submission cost
                            </p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                                {{ message.cost || '—' }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Failure information -->
                <section
                    v-if="message.failure_reason"
                    class="rounded-xl border border-red-200 bg-red-50/60 p-5 dark:border-red-500/20 dark:bg-red-500/5"
                >
                    <h2 class="font-semibold text-red-700 dark:text-red-400">
                        Failure reason
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-red-700 dark:text-red-300">
                        {{ message.failure_reason }}
                    </p>
                </section>

                <!-- Provider response -->
                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-navy-700 dark:bg-navy-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-navy-700">
                        <h2 class="font-semibold text-gray-900 dark:text-white">
                            Provider response
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Raw response recorded from the SMS provider.
                        </p>
                    </div>

                    <div class="p-5">
                        <pre
                            v-if="message.provider_response"
                            class="overflow-x-auto rounded-lg bg-gray-50 p-4 text-xs leading-6 text-gray-700 dark:bg-navy-900 dark:text-gray-300"
                        >{{ JSON.stringify(message.provider_response, null, 2) }}</pre>

                        <p
                            v-else
                            class="text-sm text-gray-500 dark:text-gray-400"
                        >
                            No provider response was recorded.
                        </p>
                    </div>
                </section>
            </div>

            <!-- Timeline -->
            <aside>
                <section class="rounded-xl border border-gray-200 bg-white p-5 dark:border-navy-700 dark:bg-navy-800">
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Submission timeline
                    </h2>

                    <div class="mt-5 space-y-5">
                        <div class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-gray-400" />

                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Record created
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ message.created_at || '—' }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="message.sent_at"
                            class="flex gap-3"
                        >
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500" />

                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Provider submission recorded
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ message.sent_at }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="message.failed_at"
                            class="flex gap-3"
                        >
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-red-500" />

                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    Submission failed
                                </p>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ message.failed_at }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400">
            These timestamps and statuses describe the submission process.
            They do not establish whether the recipient received or read the SMS.
        </p>
    </div>
</template>