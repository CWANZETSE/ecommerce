<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import { reactive } from "vue";
import type { PageProps } from "@/types";
import Pagination from "@/components/Pagination.vue";
import { admin_logs_index } from "@/routes";

interface WhatsAppMessage {
    id: number;
    recipient: string;
    wamid: string | null;
    notification_type: string | null;
    message_type: string;
    template_name: string | null;
    status: string;
    error_code: string | null;
    error_message: string | null;
    sent_at: string | null;
    delivered_at: string | null;
    read_at: string | null;
    failed_at: string | null;
    created_at: string | null;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface Filters {
    search: string;
    status: string;
    from: string;
    to: string;
    per_page: number;
}

interface Statistics {
    total: number;
    pending: number;
    sent: number;
    delivered: number;
    read: number;
    failed: number;
}

const props = defineProps<
    PageProps & {
        messages: Paginator<WhatsAppMessage>;
        filters: Filters;
        statistics: Statistics;
    }
>();

const form = reactive<Filters>({
    search: props.filters.search ?? "",
    status: props.filters.status ?? "all",
    from: props.filters.from ?? "",
    to: props.filters.to ?? "",
    per_page: props.filters.per_page ?? 10,
});

function applyFilters() {
    router.get(
        "/admin/whatsapp-messages",
        {
            search: form.search || undefined,
            status: form.status !== "all" ? form.status : undefined,
            from: form.from || undefined,
            to: form.to || undefined,
            per_page: form.per_page,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function clearFilters() {
    form.search = "";
    form.status = "all";
    form.from = "";
    form.to = "";
    form.per_page = 10;

    applyFilters();
}

function statusClass(status: string): string {
    const classes: Record<string, string> = {
        pending: "bg-warning/10 text-warning",
        sent: "bg-info/10 text-info",
        delivered: "bg-success/10 text-success",
        read: "bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light",
        failed: "bg-error/10 text-error",
    };

    return (
        classes[status] ??
        "bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-300"
    );
}

function formatDate(value: string | null): string {
    if (!value) return "—";

    return new Intl.DateTimeFormat("en-KE", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

function statusLabel(status: string): string {
    return status.charAt(0).toUpperCase() + status.slice(1);
}
</script>


<template>
    <Head title="WhatsApp Messages" />

    <div class="p-4 sm:p-5 lg:p-6">
        <!-- Page heading -->
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div
                class="flex flex-col justify-between gap-3 py-5 sm:flex-row sm:items-center lg:py-6"
            >
                <div>
                    <h2
                        class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl"
                    >
                        WhatsApp Message Logs
                    </h2>
                    <p
                        class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15"
                    >
                        Monitor WhatsApp notification delivery and read statuses.
                    </p>
                </div>
            </div>

            <Link
                :href="admin_logs_index.url()"
                class="btn space-x-1.5 bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
            >
                <span>← Back</span>
            </Link>
        </div>

        <!-- Messages table -->
        <div class="space-y-4">
            <!-- Search and filters -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex md:w-3/4 flex-col gap-3 sm:w-full sm:flex-row">
                    <select
                        v-model="form.status"
                        class="form-select w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm text-slate-700 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:text-navy-100 dark:hover:border-navy-400 dark:focus:border-accent sm:w-40"
                        @change="applyFilters"
                    >
                        <option value="all">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="sent">Sent</option>
                        <option value="delivered">Delivered</option>
                        <option value="read">Read</option>
                        <option value="failed">Failed</option>
                    </select>

                    <div class="w-full sm:w-72">
                        <input
                            v-model="form.search"
                            type="search"
                            placeholder="Search recipient or message ID…"
                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                </div>
            </div>

            <!-- Table container -->
            <div
                class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800"
            >
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300"
                            >
                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Recipient
                                </th>

                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Notification
                                </th>

                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Message type
                                </th>

                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 text-center font-bold dark:border-slate-700"
                                >
                                    Status
                                </th>

                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    WhatsApp message ID
                                </th>

                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Created
                                </th>

                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 text-center font-bold dark:border-slate-700"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="text-sm">
                            <tr
                                v-for="message in messages.data"
                                :key="message.id"
                                class="transition-colors duration-200 hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                            >
                                <!-- Recipient -->
                                <td
                                    class="min-w-[150px] whitespace-nowrap border border-slate-150 px-5 py-4 dark:border-slate-700"
                                >
                                    <p
                                        class="font-semibold text-slate-900 dark:text-navy-50"
                                    >
                                        {{ message.recipient }}
                                    </p>
                                </td>

                                <!-- Notification -->
                                <td
                                    class="min-w-[180px] border border-slate-150 px-5 py-4 dark:border-slate-700"
                                >
                                    <p
                                        class="max-w-[220px] truncate text-xs text-slate-600 dark:text-navy-200"
                                    >
                                        {{ message.notification_type || "—" }}
                                    </p>

                                    <p
                                        v-if="message.template_name"
                                        class="mt-1 max-w-[220px] truncate text-xs text-slate-400 dark:text-navy-400"
                                    >
                                        Template: {{ message.template_name }}
                                    </p>
                                </td>

                                <!-- Message type -->
                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 text-xs capitalize text-slate-600 dark:border-slate-700 dark:text-navy-200"
                                >
                                    {{ message.message_type || "—" }}
                                </td>

                                <!-- Status -->
                                <td
                                    class="whitespace-nowrap border border-slate-150 px-4 py-4 text-center dark:border-slate-700"
                                >
                                    <span
                                        class="badge space-x-2.5 rounded-full"
                                        :class="statusClass(message.status)"
                                    >
                                        {{ statusLabel(message.status) }}
                                    </span>

                                    <p
                                        v-if="message.error_code"
                                        class="mt-1 text-xs text-danger"
                                    >
                                        Error {{ message.error_code }}
                                    </p>
                                </td>

                                <!-- WhatsApp message ID -->
                                <td
                                    class="min-w-[160px] whitespace-nowrap border border-slate-150 px-5 py-4 font-mono text-xs text-slate-500 dark:border-slate-700 dark:text-navy-300"
                                >
                                    <span class="block max-w-[190px] truncate">
                                        {{ message.wamid || "—" }}
                                    </span>
                                </td>

                                <!-- Created -->
                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 text-xs text-slate-500 dark:border-slate-700 dark:text-navy-300"
                                >
                                    {{ formatDate(message.created_at) }}
                                </td>

                                <!-- Actions -->
                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 text-center dark:border-slate-700"
                                >
                                    <div
                                        class="inline-flex items-center justify-center gap-2"
                                    >
                                        <Link
                                            :href="`/admin/whatsapp-messages/${message.id}`"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-150/20 bg-primary/5 p-2.5 text-primary transition-all duration-200 hover:border-slate-150 hover:bg-primary hover:text-white hover:shadow-sm dark:border-accent-light/20 dark:bg-accent-light/10 dark:text-accent-light dark:hover:border-accent dark:hover:bg-accent dark:hover:text-white"
                                            title="View WhatsApp details"
                                        >
                                            <svg
                                                class="size-5 shrink-0"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    opacity="0.4"
                                                    d="M12 5C7.5 5 3.7 7.8 2 12C3.7 16.2 7.5 19 12 19C16.5 19 20.3 16.2 22 12C20.3 7.8 16.5 5 12 5Z"
                                                    fill="currentColor"
                                                />
                                                <path
                                                    d="M12 8C9.8 8 8 9.8 8 12C8 14.2 9.8 16 12 16C14.2 16 16 14.2 16 12C16 9.8 14.2 8 12 8ZM12 14C10.9 14 10 13.1 10 12C10 10.9 10.9 10 12 10C13.1 10 14 10.9 14 12C14 13.1 13.1 14 12 14Z"
                                                    fill="currentColor"
                                                />
                                            </svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="messages.data.length === 0">
                                <td
                                    colspan="7"
                                    class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300"
                                >
                                    No WhatsApp messages match this filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :paginator="messages" />
            </div>
        </div>
    </div>
</template>

