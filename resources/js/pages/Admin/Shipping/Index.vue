<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { PageProps } from '@/types';
import {
    admin_shipping_index,
    admin_shipping_store,
    admin_shipping_update,
    admin_shipping_toggle,
    admin_shipping_destroy,
    admin_shipping_bulk,
} from '@/routes';
import Pagination from '@/components/Pagination.vue';

interface Zone {
    id: string;
    name: string;
    fee: string;
    is_active: boolean;
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface Props extends PageProps {
    zones: Paginator<Zone>;
    filters: { search: string | null };
    stats: { total: number; active: number; free: number };
}

const props = defineProps<Props>();

const search = ref(props.filters.search ?? '');
watch(search, (val) => {
    router.get(admin_shipping_index.url(), { search: val || undefined }, {
        preserveState: true,
        replace: true,
    });
});

/* -------- New zone form -------- */
const newZone = useForm({
    name: '',
    fee: 0,
    is_active: true,
});

function createZone() {
    newZone.post(admin_shipping_index.url(), { onSuccess: () => newZone.reset() });
}

/* -------- Inline editing -------- */
const editingId = ref<string | null>(null);
const editForm = useForm({ name: '', fee: 0, is_active: true });

function startEdit(zone: Zone) {
    editingId.value = zone.id;
    editForm.name = zone.name;
    editForm.fee = Number(zone.fee);
    editForm.is_active = zone.is_active;
}

function saveEdit(zone: Zone) {
    editForm.patch(admin_shipping_update.url(zone.id), {
        preserveScroll: true,
        onSuccess: () => { editingId.value = null; },
    });
}

function cancelEdit() {
    editingId.value = null;
    editForm.clearErrors();
}

function toggleZone(zone: Zone) {
    router.patch(admin_shipping_toggle.url(zone.id), {}, { preserveScroll: true });
}

function destroyZone(zone: Zone) {
    if (!confirm(`Delete shipping zone "${zone.name}"?`)) return;
    router.delete(admin_shipping_destroy.url(zone.id), { preserveScroll: true });
}

/* -------- Bulk mode -------- */
const bulkMode = ref(false);
const bulkForm = useForm<{ zones: { id: string; fee: number; is_active: boolean }[] }>({
    zones: [],
});

function enterBulk() {
    // Access props.zones.data here since it's now paginated
    bulkForm.zones = props.zones.data.map((z) => ({
        id: z.id,
        fee: Number(z.fee),
        is_active: z.is_active,
    }));
    bulkMode.value = true;
}

function saveBulk() {
    bulkForm.post(admin_shipping_bulk.url(), {
        preserveScroll: true,
        onSuccess: () => { bulkMode.value = false; },
    });
}

/* -------- Bulk helpers -------- */
const bulkFee = ref(0);
function applyBulkFee() {
    bulkForm.zones.forEach((z) => { z.fee = bulkFee.value; });
}
</script>

<template>
    <Head title="Shipping Zones" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div>
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    Shipping Zones
                </h2>
                <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                    {{ stats.total }} zones · {{ stats.active }} active · {{ stats.free }} free delivery
                </p>
            </div>
            <div>
                <button
                    v-if="!bulkMode"
                    type="button"
                    class="btn bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450"
                    @click="enterBulk"
                >
                    Bulk edit fees
                </button>
                <button
                    v-else
                    type="button"
                    class="btn bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450"
                    @click="bulkMode = false"
                >
                    Cancel bulk
                </button>
            </div>
        </div>

        <div class="space-y-4">
            <!-- New zone form -->
            <form v-if="!bulkMode" class="card p-4 sm:p-5" @submit.prevent="createZone">
                <div class="flex flex-col gap-3 md:flex-row md:flex-nowrap md:items-end">
                    <div class="flex-1">
                        <span class="font-medium text-slate-700 dark:text-navy-100 text-sm">Zone / county name</span>
                        <input
                            v-model="newZone.name"
                            placeholder="Enter zone name"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                        <p v-if="newZone.errors.name" class="mt-1 text-xs text-error">{{ newZone.errors.name }}</p>
                    </div>

                    <div class="shrink-0">
                        <span class="font-medium text-slate-700 dark:text-navy-100 text-sm">Fee (KES)</span>
                        <input
                            v-model.number="newZone.fee"
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                        <p v-if="newZone.errors.fee" class="mt-1 text-xs text-error">{{ newZone.errors.fee }}</p>
                    </div>

                    <div class="flex shrink-0 items-center md:pb-2">
                        <label class="inline-flex items-center space-x-2">
                            <input
                                v-model="newZone.is_active"
                                type="checkbox"
                                class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 bg-slate-100 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                            />
                            <span class="font-medium text-slate-700 dark:text-navy-100 text-sm">Active</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        :disabled="newZone.processing"
                        class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                    >
                        Add zone
                    </button>
                </div>
            </form>

            <!-- Bulk fee tool -->
            <div v-if="bulkMode" class="card p-4 sm:p-5">
                <p class="font-medium text-slate-700 dark:text-navy-100 text-sm">Quick set all fees</p>
                <div class="mt-3 flex flex-wrap items-end gap-3">
                    <div class="w-36">
                        <span class="text-xs font-medium text-slate-500 dark:text-navy-300">Fee (KES)</span>
                        <input
                            v-model.number="bulkFee"
                            type="number"
                            step="0.01"
                            min="0"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        />
                    </div>
                    <button
                        type="button"
                        class="btn bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450"
                        @click="applyBulkFee"
                    >
                        Apply to all
                    </button>
                    <button
                        type="button"
                        class="btn ml-auto bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus"
                        @click="saveBulk"
                    >
                        Save all changes
                    </button>
                </div>
            </div>

            <!-- Search -->
            <div v-if="!bulkMode" class="w-full sm:w-80">
                <input
                    v-model="search"
                    placeholder="Search zones…"
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3.5 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                />
            </div>

            <!-- Zones table -->
            <div class="overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-150 bg-primary/[0.025] text-2xs uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-primary/[0.04] dark:text-navy-300">
                                <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                    Zone
                                </th>
                                <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                    Fee (KES)
                                </th>
                                <th class="whitespace-nowrap border border-slate-150 px-5 py-4 font-bold dark:border-slate-700">
                                    Status
                                </th>
                                <th class="whitespace-nowrap border border-slate-150 px-5 py-4 text-right font-bold dark:border-slate-700">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-150 dark:divide-slate-150/15">
                            <!-- Loop through zones.data -->
                            <tr
                                v-for="zone in zones.data"
                                :key="zone.id"
                                class="transition-colors hover:bg-primary/[0.025] dark:hover:bg-primary/[0.05]"
                            >
                                <!-- Bulk Edit Row -->
                                <template v-if="bulkMode">
                                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                        <span class="inline-flex items-center gap-2.5">
                                            <svg class="size-5 shrink-0 text-slate-400 dark:text-navy-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9.96055 14.0401V12.9301C9.96055 12.5001 9.64055 12.2901 9.25055 12.4601L4.24055 14.6201C3.45055 14.9601 2.81055 14.5401 2.81055 13.6901V12.3701C2.81055 11.6901 3.32055 10.9001 3.95055 10.6401L9.65055 8.19006C9.81055 8.11006 9.95055 7.90006 9.95055 7.72006V4.66006C9.95055 3.70006 10.6605 2.55006 11.5105 2.11006C11.8105 1.96006 12.1705 1.96006 12.4705 2.11006C13.3305 2.55006 14.0405 3.69006 14.0405 4.65006V7.71006C14.0405 7.89006 14.1805 8.10006 14.3405 8.18006L15.3805 8.62006L9.96055 14.0401Z" fill="currentColor"/>
                                                <path opacity="0.4" d="M21.1892 12.3701V13.6901C21.1892 14.5401 20.5492 14.9601 19.7592 14.6201L14.7492 12.4601C14.3492 12.3001 14.0392 12.5001 14.0392 12.9301V15.8401C14.0392 16.0701 14.1692 16.4001 14.3392 16.5601L16.6592 18.8901C16.8992 19.1301 17.0092 19.6101 16.8992 19.9401L16.4492 21.3001C16.2492 21.8901 15.5792 22.1601 15.0292 21.8901L12.6492 19.8901C12.2892 19.5801 11.6992 19.5801 11.3392 19.8901L8.95918 21.8901C8.40918 22.1701 7.73918 21.8901 7.55918 21.3001L7.10918 19.9401C6.99918 19.6001 7.10918 19.1301 7.34918 18.8901L9.66918 16.5601C9.82918 16.4001 9.95918 16.0701 9.95918 15.8401V14.0401L15.3792 8.62012L20.0492 10.6301C20.6792 10.9001 21.1892 11.6901 21.1892 12.3701Z" fill="currentColor"/>
                                            </svg>
                                            {{ zone.name }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                        <input
                                            v-model.number="bulkForm.zones.find(z => z.id === zone.id)!.fee"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="form-input w-32 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition-colors hover:border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-navy-500 dark:bg-navy-900 dark:hover:border-navy-400 dark:focus:border-accent"
                                        />
                                    </td>

                                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                        <label class="inline-flex cursor-pointer items-center gap-2">
                                            <input
                                                v-model="bulkForm.zones.find(z => z.id === zone.id)!.is_active"
                                                type="checkbox"
                                                class="form-checkbox is-basic size-4 rounded border-slate-300 bg-white text-primary checked:border-primary checked:bg-primary focus:ring-primary/20 dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent"
                                            />
                                            <span class="text-xs font-medium text-slate-700 dark:text-navy-100">
                                                Active
                                            </span>
                                        </label>
                                    </td>

                                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-right text-xs text-slate-400 dark:border-slate-700 dark:text-navy-300">
                                        —
                                    </td>
                                </template>

                                <!-- Inline Edit Row -->
                                <template v-else-if="editingId === zone.id">
                                    <td class="border border-slate-150 px-5 py-3 dark:border-slate-700">
                                        <input
                                            v-model="editForm.name"
                                            class="form-input w-full min-w-[150px] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition-colors hover:border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-navy-500 dark:bg-navy-900 dark:hover:border-navy-400 dark:focus:border-accent"
                                        />
                                        <p v-if="editForm.errors.name" class="mt-1.5 text-xs font-medium text-error">
                                            {{ editForm.errors.name }}
                                        </p>
                                    </td>

                                    <td class="border border-slate-150 px-5 py-3 dark:border-slate-700">
                                        <input
                                            v-model.number="editForm.fee"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="form-input w-32 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm transition-colors hover:border-slate-300 focus:border-primary focus:ring-1 focus:ring-primary/20 dark:border-navy-500 dark:bg-navy-900 dark:hover:border-navy-400 dark:focus:border-accent"
                                        />
                                        <p v-if="editForm.errors.fee" class="mt-1.5 text-xs font-medium text-error">
                                            {{ editForm.errors.fee }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                        <label class="inline-flex cursor-pointer items-center gap-2">
                                            <input
                                                v-model="editForm.is_active"
                                                type="checkbox"
                                                class="form-checkbox is-basic size-4 rounded border-slate-300 bg-white text-primary checked:border-primary checked:bg-primary focus:ring-primary/20 dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent"
                                            />
                                            <span class="text-xs font-medium text-slate-700 dark:text-navy-100">
                                                Active
                                            </span>
                                        </label>
                                    </td>

                                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-right dark:border-slate-700">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                class="inline-flex h-9 items-center justify-center rounded-lg border border-primary/20 bg-primary/[0.06] px-3 text-xs font-semibold text-primary transition-colors hover:bg-primary/15 dark:border-accent-light/20 dark:bg-accent-light/[0.06] dark:text-accent-light dark:hover:bg-accent-light/15"
                                                @click="saveEdit(zone)"
                                            >
                                                Save
                                            </button>

                                            <button
                                                type="button"
                                                class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50 dark:border-navy-500 dark:bg-navy-800 dark:text-navy-200 dark:hover:bg-navy-700"
                                                @click="cancelEdit"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </td>
                                </template>

                                <!-- Normal Row -->
                                <template v-else>
                                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 font-semibold text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                        <span class="inline-flex items-center gap-2.5">
                                            <svg class="size-5 shrink-0 text-slate-400 dark:text-navy-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9.96055 14.0401V12.9301C9.96055 12.5001 9.64055 12.2901 9.25055 12.4601L4.24055 14.6201C3.45055 14.9601 2.81055 14.5401 2.81055 13.6901V12.3701C2.81055 11.6901 3.32055 10.9001 3.95055 10.6401L9.65055 8.19006C9.81055 8.11006 9.95055 7.90006 9.95055 7.72006V4.66006C9.95055 3.70006 10.6605 2.55006 11.5105 2.11006C11.8105 1.96006 12.1705 1.96006 12.4705 2.11006C13.3305 2.55006 14.0405 3.69006 14.0405 4.65006V7.71006C14.0405 7.89006 14.1805 8.10006 14.3405 8.18006L15.3805 8.62006L9.96055 14.0401Z" fill="currentColor"/>
                                                <path opacity="0.4" d="M21.1892 12.3701V13.6901C21.1892 14.5401 20.5492 14.9601 19.7592 14.6201L14.7492 12.4601C14.3492 12.3001 14.0392 12.5001 14.0392 12.9301V15.8401C14.0392 16.0701 14.1692 16.4001 14.3392 16.5601L16.6592 18.8901C16.8992 19.1301 17.0092 19.6101 16.8992 19.9401L16.4492 21.3001C16.2492 21.8901 15.5792 22.1601 15.0292 21.8901L12.6492 19.8901C12.2892 19.5801 11.6992 19.5801 11.3392 19.8901L8.95918 21.8901C8.40918 22.1701 7.73918 21.8901 7.55918 21.3001L7.10918 19.9401C6.99918 19.6001 7.10918 19.1301 7.34918 18.8901L9.66918 16.5601C9.82918 16.4001 9.95918 16.0701 9.95918 15.8401V14.0401L15.3792 8.62012L20.0492 10.6301C20.6792 10.9001 21.1892 11.6901 21.1892 12.3701Z" fill="currentColor"/>
                                            </svg>
                                            {{ zone.name }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 font-medium text-slate-700 dark:border-slate-700 dark:text-navy-100">
                                        KES {{ zone.fee }}
                                    </td>

                                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 dark:border-slate-700">
                                        <span
                                            :class="zone.is_active
                                                ? 'bg-success/10 text-success'
                                                : 'bg-slate-100 text-slate-500 dark:bg-navy-600 dark:text-navy-300'"
                                            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
                                        >
                                            {{ zone.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>

                                    <td class="whitespace-nowrap border border-slate-150 px-5 py-3 text-right dark:border-slate-700">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                class="inline-flex h-9 items-center justify-center rounded-lg border border-primary/20 bg-primary/[0.06] px-3 text-xs font-semibold text-primary transition-colors hover:bg-primary/15 dark:border-accent-light/20 dark:bg-accent-light/[0.06] dark:text-accent-light dark:hover:bg-accent-light/15"
                                                @click="startEdit(zone)"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                type="button"
                                                class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50 dark:border-navy-500 dark:bg-navy-800 dark:text-navy-200 dark:hover:bg-navy-700"
                                                @click="toggleZone(zone)"
                                            >
                                                {{ zone.is_active ? 'Disable' : 'Enable' }}
                                            </button>

                                            <button
                                                type="button"
                                                class="inline-flex h-9 items-center justify-center rounded-lg border border-error/20 bg-error/[0.06] px-3 text-xs font-semibold text-error transition-colors hover:bg-error/15 dark:border-error/25 dark:bg-error/[0.08] dark:hover:bg-error/15"
                                                @click="destroyZone(zone)"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </template>
                            </tr>

                            <!-- Check zones.data.length -->
                            <tr v-if="zones.data.length === 0">
                                <td
                                    colspan="4"
                                    class="border border-slate-150 px-5 py-10 text-center text-sm text-slate-400 dark:border-slate-700 dark:text-navy-300"
                                >
                                    No zones match your search.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :paginator="zones" />
                
            </div>
        </div>
    </div>
</template>