<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { PageProps } from '@/types';
import { admin_settings_update } from '@/routes';

interface Setting {
    id: string;
    key: string;
    value: string | null;
    type: 'text' | 'boolean';
    group: string;
}

interface Props extends PageProps {
    groups: Record<string, Setting[]>;
}

const props = defineProps<Props>();

// Build initial state keyed by setting.key
const initialValues: Record<string, string | boolean> = {};
for (const settings of Object.values(props.groups)) {
    for (const setting of settings) {
        initialValues[setting.key] =
            setting.type === 'boolean'
                ? setting.value === 'true'
                : (setting.value ?? '');
    }
}

const values = ref<Record<string, string | boolean>>({ ...initialValues });

const form = useForm({
    settings: [] as { key: string; value: string }[],
});

const processing = computed(() => form.processing);

const fieldLabels: Record<string, string> = {
    store_name: 'Store name',
    store_email: 'Contact email',
    store_phone: 'Contact phone',
    store_address: 'Physical address',
    currency: 'Currency',
    timezone: 'Timezone',
    tax_rate: 'Tax rate (%)',
    tax_enabled: 'Tax enabled',
    free_shipping_threshold: 'Free shipping above (KES)',
    maintenance_mode: 'Maintenance mode',
};

const groupTitles: Record<string, string> = {
    general: 'General',
    tax: 'Tax',
    contact: 'Contact details',
    shipping: 'Shipping',
    system: 'System',
};

function submit() {
    form.settings = Object.entries(values.value).map(([key, value]) => ({
        key,
        value: typeof value === 'boolean' ? String(value) : value,
    }));

    form.patch(admin_settings_update.url(), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Settings" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                Store Settings
            </h2>
        </div>

        <form class="max-w-3xl space-y-6" @submit.prevent="submit">
            <div
                v-for="(settings, group) in groups"
                :key="group"
                class="card p-4 sm:p-5"
            >
                <h3 class="font-medium text-slate-700 dark:text-navy-100 text-base border-b border-slate-150 pb-3 dark:border-navy-600">
                    {{ groupTitles[group] ?? group }}
                </h3>

                <div class="mt-4 space-y-4">
                    <div v-for="setting in settings" :key="setting.id">
                        <!-- Boolean toggle -->
                        <template v-if="setting.type === 'boolean'">
                            <label class="inline-flex items-center space-x-2 cursor-pointer">
                                <input
                                    v-model="values[setting.key]"
                                    type="checkbox"
                                    class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 bg-slate-100 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                />
                                <span class="font-medium text-slate-700 dark:text-navy-100 text-sm">
                                    {{ fieldLabels[setting.key] ?? setting.key }}
                                </span>
                            </label>
                        </template>

                        <!-- Text input -->
                        <template v-else>
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100 text-sm">
                                    {{ fieldLabels[setting.key] ?? setting.key }}
                                </span>
                                <input
                                    v-model="values[setting.key]"
                                    class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                />
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="processing"
                    class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                >
                    {{ processing ? 'Saving…' : 'Save settings' }}
                </button>
            </div>
        </form>
    </div>
</template>