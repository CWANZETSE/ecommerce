<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { reactive, ref, watch, computed } from "vue";
import {
    Search,
    Filter,
    RotateCcw,
    ChevronDown,
    ChevronUp,
    ClipboardList,
    X,
} from "@lucide/vue";
import {
    admin_dashboard,
    admin_logs_index,
    admin_whatsapp_messages_index,
    admin_africas_talking_sms_index,
} from "@/routes";
import Pagination from "@/components/Pagination.vue";
import NoteSVG from "@/components/SVG/NoteSVG.vue";

interface User {
    id: number;
    name: string;
}

interface ActivityLog {
    id: number;
    user_id: number | null;
    user: User | null;
    action: string;
    description: string | null;
    subject_type: string;
    subject_id: string | number;
    properties: Record<string, any> | null;
    from_records: Record<string, any> | null;
    to_records: Record<string, any> | null;
    ip: string | null;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Filters {
    search: string;
    user_id: string | number;
    action: string;
    subject_type: string;
    date_from: string;
    date_to: string;
}

const props = defineProps<{
    logs: {
        data: ActivityLog[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };
    users: User[];
    actions: string[];
    subjectTypes: string[];
    filters: Filters;
}>();

// Local filter state.
const form = reactive<Filters>({
    search: props.filters.search ?? "",
    user_id: props.filters.user_id ?? "",
    action: props.filters.action ?? "",
    subject_type: props.filters.subject_type ?? "",
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
});

const page = usePage();

const isActivityLogs = computed(() =>
    page.url.startsWith(admin_logs_index.url()),
);

const isWhatsappLogs = computed(() =>
    page.url.startsWith(admin_whatsapp_messages_index.url()),
);
const isAfricasTalkingLogs = computed(() =>
    page.url.startsWith(admin_africas_talking_sms_index.url()),
);
const goToActivityLogs = () => {
    if (!isActivityLogs.value) {
        router.visit(admin_logs_index.url());
    }
};

const goToAfricasTalkingLogs = () => {
    if (!isAfricasTalkingLogs.value) {
        router.visit(admin_africas_talking_sms_index.url());
    }
};

const goToWhatsappLogs = () => {
    if (!isWhatsappLogs.value) {
        router.visit(admin_whatsapp_messages_index.url()); // or admin_whatsapp_messages_index.url()
    }
};
// Keep local filter values synchronized with server props.
watch(
    () => props.filters,
    (filters) => {
        Object.assign(form, {
            search: filters.search ?? "",
            user_id: filters.user_id ?? "",
            action: filters.action ?? "",
            subject_type: filters.subject_type ?? "",
            date_from: filters.date_from ?? "",
            date_to: filters.date_to ?? "",
        });
    },
    { deep: true },
);

// Submit filters to Laravel.
const applyFilters = () => {
    const params = {
        search: form.search.trim(),
        user_id: form.user_id,
        action: form.action,
        subject_type: form.subject_type,
        date_from: form.date_from,
        date_to: form.date_to,
    };
    router.get(admin_logs_index.url(), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Reset all filters.
const resetFilters = () => {
    Object.assign(form, {
        search: "",
        user_id: "",
        action: "",
        subject_type: "",
        date_from: "",
        date_to: "",
    });

    applyFilters();
};

// Selected activity log for the changes dialog.
const selectedLog = ref<ActivityLog | null>(null);

const openChanges = (log: ActivityLog) => {
    selectedLog.value = log;
};

const closeChanges = () => {
    selectedLog.value = null;
};

// Format date.
const formatDate = (date: string) => {
    return new Date(date).toLocaleString("en-KE", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Format JSON.
const formatJson = (value: Record<string, any> | null) => {
    return value ? JSON.stringify(value, null, 2) : "No data";
};

// Action badge styling.
const actionColor = (action: string) => {
    switch (action) {
        case "Created":
            return "badge space-x-2.5 rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light";

        case "Updated":
            return "badge space-x-2.5 rounded-full bg-warning/10 text-warning dark:bg-warning/15";

        case "Deleted":
            return "badge space-x-2.5 rounded-full bg-error/10 text-error dark:bg-error/15";

        default:
            return "badge space-x-2.5 rounded-full bg-info/10 text-info dark:bg-info/15";
    }
};
</script>

<template>
    <Head title="Activity Logs" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div
            class="p-6 border-0 shadow-md dark:shadow-dark-md mb-6 py-4 bg-primary/10 dark:bg-primary/10 overflow-hidden rounded-md border-none !shadow-none dark:!shadow-none"
            style="border-radius: 12px"
        >
            <div class="flex items-center justify-between gap-6 relative">
                <div>
                    <h4
                        class="font-semibold text-xl text-dark dark:text-white mb-3"
                    >
                        Logs
                    </h4>
                    <ol
                        class="flex items-center whitespace-nowrap"
                        aria-label="Breadcrumb"
                    >
                        <li class="flex items-center">
                            <Link
                                class="opacity-80 text-sm text-link dark:text-darklink leading-none"
                                :href="admin_dashboard.url()"
                                data-discover="true"
                                >Home</Link
                            >
                        </li>
                        <li>
                            <div
                                class="p-0.5 rounded-full bg-primary/10 dark:bg-primary mx-2.5 flex items-center"
                            ></div>
                        </li>
                        <li
                            class="flex items-center text-sm text-default dark:text-darklink leading-none"
                            aria-current="page"
                        >
                            Logs
                        </li>
                    </ol>
                </div>
                <div
                    class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <NoteSVG />
                </div>
            </div>
        </div>
        <div class="gap-2 flex justify-between w-1/2">
            <label class="inline-flex items-center space-x-2 cursor-pointer">
                <input
                    :checked="isActivityLogs"
                    @change="goToActivityLogs"
                    class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 checked:!border-success checked:bg-success hover:!border-success focus:!border-success dark:border-navy-400"
                    name="log-type"
                    type="radio"
                />
                <p>Activity Logs</p>
            </label>

            <label class="inline-flex items-center space-x-2 cursor-pointer">
                <input
                    :checked="isWhatsappLogs"
                    @change="goToWhatsappLogs"
                    class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 checked:!border-success checked:bg-success hover:!border-success focus:!border-success dark:border-navy-400"
                    name="log-type"
                    type="radio"
                />
                <p>Whatsapp Logs</p>
            </label>
            <label class="inline-flex items-center space-x-2 cursor-pointer">
                <input
                    :checked="isAfricasTalkingLogs"
                    @change="goToAfricasTalkingLogs"
                    class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 checked:!border-success checked:bg-success hover:!border-success focus:!border-success dark:border-navy-400"
                    name="log-type"
                    type="radio"
                />
                <p>AfricasTalking Logs</p>
            </label>
        </div>
        <!-- Filters -->
        <div
            class="mt-4 overflow-hidden rounded-2xl bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800"
        >
            <div
                class="border-b border-slate-150 bg-primary/[0.025] px-5 py-4 dark:border-slate-700 dark:bg-primary/[0.04]"
            >
                <div class="flex items-center gap-2">
                    <Filter
                        class="size-4 text-primary dark:text-accent-light"
                    />

                    <h2
                        class="text-sm font-bold text-slate-700 dark:text-navy-100"
                    >
                        Filter Activity Logs
                    </h2>
                </div>

                <p class="mt-1 text-xs text-slate-500 dark:text-navy-300">
                    Narrow down activity records using the filters below.
                </p>
            </div>

            <form @submit.prevent="applyFilters" class="space-y-4 p-5">
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <!-- Search -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-semibold text-slate-600 dark:text-navy-200"
                        >
                            Search
                        </label>

                        <div class="relative">
                            <Search
                                class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400 dark:text-navy-300"
                            />

                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="Search activity..."
                                class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                        </div>
                    </div>

                    <!-- User -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-semibold text-slate-600 dark:text-navy-200"
                        >
                            End User
                        </label>
                        <select
                            v-model="form.user_id"
                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        >
                            <option value="">All users</option>

                            <option
                                v-for="user in users"
                                :key="user.id"
                                :value="String(user.id)"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Action -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-semibold text-slate-600 dark:text-navy-200"
                        >
                            Action
                        </label>

                        <select
                            v-model="form.action"
                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        >
                            <option value="">All actions</option>

                            <option
                                v-for="action in actions"
                                :key="action"
                                :value="action"
                            >
                                {{
                                    action.charAt(0).toUpperCase() +
                                    action.slice(1)
                                }}
                            </option>
                        </select>
                    </div>

                    <!-- Subject type -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-semibold text-slate-600 dark:text-navy-200"
                        >
                            Record Type
                        </label>

                        <select
                            v-model="form.subject_type"
                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        >
                            <option value="">All record types</option>

                            <option
                                v-for="type in subjectTypes"
                                :key="type"
                                :value="type"
                            >
                                {{ type }}
                            </option>
                        </select>
                    </div>

                    <!-- Date from -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-semibold text-slate-600 dark:text-navy-200"
                        >
                            From Date
                        </label>

                        <input
                            v-model="form.date_from"
                            type="date"
                            :max="form.date_to || undefined"
                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                    </div>

                    <!-- Date to -->
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-semibold text-slate-600 dark:text-navy-200"
                        >
                            To Date
                        </label>

                        <input
                            v-model="form.date_to"
                            type="date"
                            :min="form.date_from || undefined"
                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                    </div>
                </div>

                <!-- Filter actions -->
                <div
                    class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-150 pt-4 dark:border-slate-700"
                >
                    <button
                        type="button"
                        @click="resetFilters"
                        class="inline-flex h-9 items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 focus:bg-slate-50 active:bg-slate-100 dark:border-slate-600 dark:bg-navy-800 dark:text-navy-200 dark:hover:bg-navy-700 dark:focus:bg-navy-700"
                    >
                        <RotateCcw class="size-4" />
                        Reset
                    </button>

                    <button
                        type="submit"
                        class="inline-flex h-9 items-center gap-2 rounded-lg bg-primary px-4 text-sm font-medium text-white transition-colors hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus dark:bg-accent-light dark:text-navy-900 dark:hover:bg-accent dark:focus:bg-accent"
                    >
                        <Search class="size-4" />
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Activity table -->
        <div
            class="mt-5 overflow-hidden rounded-2xl bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800"
        >
            <!-- Table heading -->
            <div
                class="flex flex-col justify-between gap-2 border-b border-slate-150 px-5 py-4 sm:flex-row sm:items-center dark:border-slate-700"
            >
                <div>
                    <h2 class="font-semibold text-slate-700 dark:text-navy-100">
                        Activity History
                    </h2>

                    <p class="mt-0.5 text-xs text-slate-500 dark:text-navy-300">
                        Recorded activities across your application.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300"
                        >
                            <th
                                class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                            >
                                End User
                            </th>

                            <th
                                class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                            >
                                Action
                            </th>

                            <th
                                class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                            >
                                Record
                            </th>

                            <th
                                class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                            >
                                Description
                            </th>

                            <th
                                class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                            >
                                IP Address
                            </th>

                            <th
                                class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700"
                            >
                                Date
                            </th>

                            <th
                                class="whitespace-nowrap border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700"
                            >
                                Changes
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-slate-150 dark:divide-slate-150/15"
                    >
                        <!-- Empty state -->
                        <tr v-if="logs.data.length === 0">
                            <td
                                colspan="7"
                                class="border border-slate-150 px-5 py-12 text-center dark:border-slate-700"
                            >
                                <ClipboardList
                                    class="mx-auto mb-3 size-9 text-slate-300 dark:text-navy-400"
                                />

                                <p
                                    class="font-semibold text-slate-600 dark:text-navy-200"
                                >
                                    No activity logs found
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400 dark:text-navy-400"
                                >
                                    Try adjusting your filters.
                                </p>
                            </td>
                        </tr>

                        <!-- Activity rows -->
                        <tr
                            v-for="log in logs.data"
                            :key="log.id"
                            class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                        >
                            <!-- User -->
                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700"
                            >
                                <span
                                    class="font-semibold text-slate-700 dark:text-navy-100"
                                >
                                    {{
                                        log.user?.name ??
                                        "System / Deleted user"
                                    }}
                                </span>
                            </td>

                            <!-- Action -->
                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700"
                            >
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="actionColor(log.action)"
                                >
                                    {{ log.action }}
                                </span>
                            </td>

                            <!-- Record -->
                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700"
                            >
                                <div
                                    class="font-semibold text-slate-700 dark:text-navy-100"
                                >
                                    {{ log.subject_type }}
                                </div>

                                <div
                                    class="mt-0.5 text-xs text-slate-400 dark:text-navy-400"
                                >
                                    {{ log.subject_id }}
                                </div>
                            </td>

                            <!-- Description -->
                            <td
                                class="min-w-[200px] max-w-xs border border-slate-150 px-5 py-3 dark:border-slate-700"
                            >
                                <span
                                    class="line-clamp-2 text-sm text-slate-600 dark:text-navy-200"
                                >
                                    {{ log.description ?? "—" }}
                                </span>
                            </td>

                            <!-- IP address -->
                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 text-sm text-slate-600 dark:border-slate-700 dark:text-navy-200"
                            >
                                {{ log.ip ?? "—" }}
                            </td>

                            <!-- Date -->
                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 text-sm text-slate-600 dark:border-slate-700 dark:text-navy-200"
                            >
                                {{ formatDate(log.created_at) }}
                            </td>

                            <!-- Changes -->
                            <td
                                class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700"
                            >
                                <div class="flex items-center justify-end">
                                    <button
                                        v-if="
                                            log.from_records ||
                                            log.to_records ||
                                            log.properties
                                        "
                                        type="button"
                                        @click="openChanges(log)"
                                        class="btn space-x-2 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90"
                                    >
                                        View Changes
                                        <ChevronDown class="size-3.5" />
                                    </button>

                                    <span
                                        v-else
                                        class="text-xs text-slate-400 dark:text-navy-400"
                                    >
                                        No changes
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination :paginator="logs" />
        </div>
    </div>

    <!-- Activity changes dialog -->
    <Teleport to="body">
        <div
            v-if="selectedLog"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
            @click.self="closeChanges"
            @keydown.esc="closeChanges"
        >
            <div
                class="max-h-[85vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-5 shadow-xl dark:border-slate-700 dark:bg-navy-800"
            >
                <!-- Dialog header -->
                <div
                    class="mb-5 flex items-center justify-between border-b border-slate-150 pb-4 dark:border-slate-700"
                >
                    <div>
                        <h3
                            class="font-semibold text-slate-800 dark:text-navy-50"
                        >
                            Activity Changes
                        </h3>

                        <p
                            class="mt-1 text-xs text-slate-500 dark:text-navy-300"
                        >
                            {{ selectedLog.subject_type }} #{{
                                selectedLog.subject_id
                            }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeChanges"
                        class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors hover:bg-slate-50 dark:border-slate-600 dark:bg-navy-800 dark:text-navy-300 dark:hover:bg-navy-700"
                        aria-label="Close dialog"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <!-- Previous and new values -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div
                        class="overflow-hidden rounded-xl border border-slate-150 dark:border-slate-700"
                    >
                        <div
                            class="border-b border-slate-150 bg-secondary/[0.04] px-4 py-3 dark:border-slate-700 dark:bg-secondary-light/[0.04]"
                        >
                            <h4
                                class="text-sm font-semibold text-secondary dark:text-secondary-light"
                            >
                                Previous Values
                            </h4>
                        </div>

                        <pre
                            class="max-h-80 overflow-auto bg-slate-50 p-4 text-xs leading-relaxed text-slate-700 dark:bg-navy-900 dark:text-navy-200"
                            >{{ formatJson(selectedLog.from_records) }}</pre
                        >
                    </div>

                    <div
                        class="overflow-hidden rounded-xl border border-slate-150 dark:border-slate-700"
                    >
                        <div
                            class="border-b border-slate-150 bg-success/[0.04] px-4 py-3 dark:border-slate-700 dark:bg-success/[0.06]"
                        >
                            <h4 class="text-sm font-semibold text-success">
                                New Values
                            </h4>
                        </div>

                        <pre
                            class="max-h-80 overflow-auto bg-slate-50 p-4 text-xs leading-relaxed text-slate-700 dark:bg-navy-900 dark:text-navy-200"
                            >{{ formatJson(selectedLog.to_records) }}</pre
                        >
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
