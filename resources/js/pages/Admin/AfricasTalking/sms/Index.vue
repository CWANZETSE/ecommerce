<script setup lang="ts">
import { computed, reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import { admin_logs_index } from "@/routes";
import Pagination from "@/components/Pagination.vue";

interface SmsMessage {
    id: number;
    recipient: string;
    notification_type: string | null;
    message_id: string | null;
    status: string;
    status_code: number | null;
    status_description: string | null;
    failure_reason: string | null;
    created_at: string | null;
    sent_at: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedMessages {
    data: SmsMessage[];
    links: PaginationLink[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface Props {
    messages: PaginatedMessages;
    filters: {
        search: string;
        status: string;
    };
    statuses: {
        value: string;
        label: string;
    }[];
    stats: {
        total: number;
        accepted: number;
        failed: number;
        pending: number;
    };
}

const props = defineProps<Props>();

const filters = reactive({
    search: props.filters.search ?? "",
    status: props.filters.status ?? "",
});

const hasFilters = computed(() => Boolean(filters.search || filters.status));

function applyFilters() {
    router.get(
        "/admin/africas-talking/sms",
        {
            search: filters.search || undefined,
            status: filters.status || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function clearFilters() {
    filters.search = "";
    filters.status = "";
    applyFilters();
}

function statusClasses(status: string): string {
    switch (status.toLowerCase()) {
        case "processed":
        case "sent":
            return "badge space-x-2.5 rounded-full bg-success/10 text-success dark:bg-success/15";

        case "queued":
            return "badge space-x-2.5 rounded-full bg-info/10 text-info dark:bg-info/15";

        case "failed":
            return "badge space-x-2.5 rounded-full bg-error/10 text-error dark:bg-error/15";

        case "pending":
            return "badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15";

        default:
            return "badge bg-slate-150 text-slate-800 shadow-soft shadow-slate-200/50 dark:bg-navy-500 dark:text-navy-100 dark:shadow-navy-450/50";
    }
}

function displayStatus(status: string): string {
    return status.charAt(0).toUpperCase() + status.slice(1);
}
</script>

<template>
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
                        Africa's Talking SMS
                    </h2>
                    <p
                        class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15"
                    >
                        View outgoing SMS submissions and provider responses.
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
                        v-model="filters.status"
                        class="form-select w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm text-slate-700 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:text-navy-100 dark:hover:border-navy-400 dark:focus:border-accent sm:w-40"
                        @change="applyFilters"
                    >
                        <option value="">All statuses</option>

                        <option
                            v-for="status in statuses"
                            :key="status.value"
                            :value="status.value"
                        >
                            {{ status.label }}
                        </option>
                    </select>

                    <div class="w-full sm:w-72">
                        <input
                            v-model="filters.search"
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
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 text-center font-bold dark:border-slate-700"
                                >
                                    Status
                                </th>

                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Provider response
                                </th>

                                <th
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                                >
                                    Message ID
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
                                <!-- Status -->
                                <td
                                    class="whitespace-nowrap border border-slate-150 px-4 py-4 text-center dark:border-slate-700"
                                >
                                    <span
                                        class="badge space-x-2.5 rounded-full"
                                        :class="statusClasses(message.status)"
                                    >
                                        {{ displayStatus(message.status) }}
                                    </span>
                                </td>

                                <!-- Provider response -->
                                <td
                                    class="min-w-[170px] border border-slate-150 px-5 py-4 dark:border-slate-700"
                                >
                                    <template
                                        v-if="message.status_code !== null"
                                    >
                                        <span
                                            class="font-semibold text-slate-700 dark:text-navy-100"
                                        >
                                            {{ message.status_code }}
                                        </span>

                                        <span
                                            class="ml-1 text-xs text-slate-500 dark:text-navy-300"
                                        >
                                            {{ message.status_description }}
                                        </span>
                                    </template>

                                    <span
                                        v-else
                                        class="text-xs text-slate-400 dark:text-navy-400"
                                    >
                                        {{
                                            message.failure_reason ||
                                            "No response"
                                        }}
                                    </span>
                                </td>

                                <!-- Message ID -->
                                <td
                                    class="min-w-[160px] whitespace-nowrap border border-slate-150 px-5 py-4 font-mono text-xs text-slate-500 dark:border-slate-700 dark:text-navy-300"
                                >
                                    <span class="block max-w-[170px] truncate">
                                        {{ message.message_id || "—" }}
                                    </span>
                                </td>

                                <!-- Created -->
                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 text-xs text-slate-500 dark:border-slate-700 dark:text-navy-300"
                                >
                                    {{ message.created_at || "—" }}
                                </td>

                                <!-- Actions -->
                                <td
                                    class="whitespace-nowrap border border-slate-150 px-5 py-4 text-center dark:border-slate-700"
                                >
                                    <div
                                        class="inline-flex items-center justify-center gap-2"
                                    >
                                        <Link
                                            :href="`/admin/africas-talking/sms/${message.id}`"
                                            class="inline-flex items-center justify-center rounded-lg border border-slate-150/20 bg-primary/5 p-2.5 text-primary transition-all duration-200 hover:border-slate-150 hover:bg-primary hover:text-white hover:shadow-sm dark:border-accent-light/20 dark:bg-accent-light/10 dark:text-accent-light dark:hover:border-accent dark:hover:bg-accent dark:hover:text-white"
                                            title="View SMS details"
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
                                    No SMS messages match this filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :paginator="messages" />
            </div>
        </div>

        <div
            class="alert flex space-x-2 mt-2 rounded-lg border border-warning px-4 py-4 text-warning"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="size-5"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                    fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"
                ></path>
            </svg>
            <p>
                Provider submission statuses indicate the response received from
                Africa's Talking. They do not confirm delivery to a recipient's
                handset.
            </p>
        </div>
    </div>
</template>
