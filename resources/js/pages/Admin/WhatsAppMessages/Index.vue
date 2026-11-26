
<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import type { PageProps } from '@/types';
import Pagination from '@/components/Pagination.vue';
import { admin_logs_index } from '@/routes';

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

const props = defineProps<PageProps & {
    messages: Paginator<WhatsAppMessage>;
    filters: Filters;
    statistics: Statistics;
}>();

const form = reactive<Filters>({
    search: props.filters.search ?? '',
    status: props.filters.status ?? 'all',
    from: props.filters.from ?? '',
    to: props.filters.to ?? '',
    per_page: props.filters.per_page ?? 10,
});

function applyFilters() {
    router.get('/admin/whatsapp-messages', {
        search: form.search || undefined,
        status: form.status !== 'all' ? form.status : undefined,
        from: form.from || undefined,
        to: form.to || undefined,
        per_page: form.per_page,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearFilters() {
    form.search = '';
    form.status = 'all';
    form.from = '';
    form.to = '';
    form.per_page = 10;

    applyFilters();
}

function statusClass(status: string): string {
    const classes: Record<string, string> = {
        pending: 'bg-warning/10 text-warning',
        sent: 'bg-info/10 text-info',
        delivered: 'bg-success/10 text-success',
        read: 'bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light',
        failed: 'bg-error/10 text-error',
    };

    return classes[status] ?? 'bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-300';
}

function formatDate(value: string | null): string {
    if (!value) return '—';

    return new Intl.DateTimeFormat('en-KE', {
        dateStyle: 'medium',
        timeStyle: 'short',
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
            <div class="flex flex-col justify-between gap-3 py-5 sm:flex-row sm:items-center lg:py-6">
            <div>
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    WhatsApp Message Logs
                </h2>
                <p class="badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15">
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

        <!-- Filters -->
        <div class="mt-5 rounded-2xl border border-slate-150 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-navy-800">
            <form @submit.prevent="applyFilters">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-navy-200">
                            Search
                        </label>
                        <input
                            v-model="form.search"
                            type="search"
                            placeholder="Phone, message ID, notification..."
                            class="w-full form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-navy-200">
                            Status
                        </label>
                        <select
                            v-model="form.status"
                            class="w-full form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                        >
                            <option value="all">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="sent">Sent</option>
                            <option value="delivered">Delivered</option>
                            <option value="read">Read</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-navy-200">
                            From
                        </label>
                        <input
                            v-model="form.from"
                            type="date"
                            class="w-full form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-navy-200">
                            To
                        </label>
                        <input
                            v-model="form.to"
                            type="date"
                            :min="form.from || undefined"
                            class="w-full form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-navy-200">
                            Per page
                        </label>
                        <select
                            v-model.number="form.per_page"
                            class="w-full form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent"
                        >
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap justify-end gap-2">
                    <button
                        type="button"
                        class="btn bg-warning/10 font-medium text-warning hover:bg-warning/20 focus:bg-warning/20 active:bg-warning/25"
                        @click="clearFilters"
                    >
                        Clear filters
                    </button>

                    <button
                        type="submit"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-focus"
                    >
                        Apply filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Messages table -->
        <div class="mt-5 overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
            <div class="flex items-center justify-between border-b border-slate-150 px-4 py-4 dark:border-slate-700 sm:px-5">
                <div>
                    <h3 class="font-semibold text-slate-800 dark:text-navy-50">
                        Message history
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-navy-300">
                        {{ messages.total.toLocaleString() }} messages found
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300">
                            <th class="whitespace-nowrap px-5 py-4 font-bold">Recipient</th>
                            <th class="whitespace-nowrap px-5 py-4 font-bold">Notification</th>
                            <th class="whitespace-nowrap px-5 py-4 font-bold">Message type</th>
                            <th class="whitespace-nowrap px-5 py-4 font-bold">Status</th>
                            <th class="whitespace-nowrap px-5 py-4 font-bold">Created</th>
                            <th class="whitespace-nowrap px-5 py-4 text-right font-bold">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                        <tr
                            v-for="message in messages.data"
                            :key="message.id"
                            class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                        >
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-semibold text-slate-700 dark:text-navy-100">
                                    {{ message.recipient }}
                                </p>
                                <p class="mt-1 max-w-[220px] truncate text-xs text-slate-400 dark:text-navy-300">
                                    {{ message.wamid || 'No WhatsApp message ID' }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 dark:text-navy-200">
                                {{ message.notification_type || '—' }}
                                <p v-if="message.template_name" class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                    {{ message.template_name }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm capitalize text-slate-600 dark:text-navy-200">
                                {{ message.message_type }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="statusClass(message.status)"
                                >
                                    {{ statusLabel(message.status) }}
                                </span>
                                <p v-if="message.error_code" class="mt-1 text-xs text-error">
                                    Error {{ message.error_code }}
                                </p>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500 dark:text-navy-300">
                                {{ formatDate(message.created_at) }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <Link
                                    :href="`/admin/whatsapp-messages/${message.id}`"
                                    class="inline-flex items-center rounded-lg border border-primary/20 bg-primary/[0.06] px-3 py-2 text-xs font-semibold text-primary transition hover:bg-primary/15 dark:border-accent-light/20 dark:bg-accent-light/[0.06] dark:text-accent-light dark:hover:bg-accent-light/15"
                                >
                                    View details
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="messages.data.length === 0">
                            <td
                                colspan="6"
                                class="px-5 py-12 text-center text-sm text-slate-400 dark:text-navy-300"
                            >
                                <p class="font-medium text-slate-600 dark:text-navy-200">
                                    No WhatsApp messages found
                                </p>
                                <p class="mt-1">
                                    Try changing your search criteria or filters.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :paginator="messages" />
        </div>
    </div>
</template>
