<script setup lang="ts">
import { Link } from "@inertiajs/vue3";

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
        case "processed":
        case "sent":
        case "queued":
            return "bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400";

        case "failed":
            return "bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400";

        case "pending":
            return "bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400";

        default:
            return "bg-gray-100 text-gray-700 dark:bg-navy-700 dark:text-gray-300";
    }
}

function displayStatus(status: string): string {
    return status.charAt(0).toUpperCase() + status.slice(1);
}
</script>

<template>
    <div class="p-4 sm:p-5 lg:p-6">
        <!-- Heading -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    SMS message details
                </h1>

                <span
                    class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-semibold"
                    :class="statusClasses(message.status)"
                >
                    {{ displayStatus(message.status) }}
                </span>
            </div>

            <div class="mb-2">
                <Link
                    href="/admin/africas-talking/sms"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-primary dark:text-gray-400"
                >
                    <span aria-hidden="true">←</span>
                    SMS messages
                </Link>
            </div>
        </div>

        <!-- Main details -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <section
                    class="rounded-xl border border-gray-200 bg-white p-5 dark:border-navy-700 dark:bg-navy-800"
                >
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Message information
                    </h2>

                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Recipient
                            </p>
                            <p
                                class="mt-1 font-semibold text-gray-900 dark:text-white"
                            >
                                {{ message.recipient }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Notification
                            </p>
                            <p
                                class="mt-1 break-words text-sm text-gray-800 dark:text-gray-200"
                            >
                                {{ message.notification_type || "—" }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Notification ID
                            </p>
                            <p
                                class="mt-1 break-all font-mono text-xs text-gray-700 dark:text-gray-300"
                            >
                                {{ message.notification_id || "—" }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Africa's Talking message ID
                            </p>
                            <p
                                class="mt-1 break-all font-mono text-xs text-gray-700 dark:text-gray-300"
                            >
                                {{ message.message_id || "Not provided" }}
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Provider status code
                            </p>
                            <p
                                class="mt-1 font-semibold text-gray-900 dark:text-white"
                            >
                                {{ message.status_code ?? "—" }}
                                <span
                                    v-if="message.status_description"
                                    class="font-normal text-gray-500 dark:text-gray-400"
                                >
                                    · {{ message.status_description }}
                                </span>
                            </p>
                        </div>

                        <div>
                            <p
                                class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                            >
                                Submission cost
                            </p>
                            <p
                                class="mt-1 font-semibold text-gray-900 dark:text-white"
                            >
                                {{ message.cost || "—" }}
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

                    <p
                        class="mt-2 text-sm leading-6 text-red-700 dark:text-red-300"
                    >
                        {{ message.failure_reason }}
                    </p>
                </section>
            </div>

            <!-- Timeline -->
            <aside>
                <section
                    class="rounded-xl border border-gray-200 bg-white p-5 dark:border-navy-700 dark:bg-navy-800"
                >
                    <h2 class="font-semibold text-gray-900 dark:text-white">
                        Submission timeline
                    </h2>

                    <div class="mt-5 space-y-5">
                        <div class="flex gap-3">
                            <span
                                class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-gray-400"
                            />

                            <div>
                                <p
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    Record created
                                </p>
                                <p
                                    class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ message.created_at || "—" }}
                                </p>
                            </div>
                        </div>

                        <div v-if="message.sent_at" class="flex gap-3">
                            <span
                                class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500"
                            />

                            <div>
                                <p
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    Provider submission recorded
                                </p>
                                <p
                                    class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ message.sent_at }}
                                </p>
                            </div>
                        </div>

                        <div v-if="message.failed_at" class="flex gap-3">
                            <span
                                class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-red-500"
                            />

                            <div>
                                <p
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    Submission failed
                                </p>
                                <p
                                    class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ message.failed_at }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        

        <div class="alert flex overflow-hidden rounded-lg bg-warning/10 text-warning dark:bg-warning/15">
                  <div class="flex flex-1 items-center space-x-3 p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
            These timestamps and statuses describe the submission process. They
            do not establish whether the recipient received or read the SMS.
        </p>
                    </div>
                  </div>
                </div>
    </div>
</template>
