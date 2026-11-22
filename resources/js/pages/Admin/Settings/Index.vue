<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import type { PageProps } from "@/types";
import { admin_dashboard, admin_settings_update } from "@/routes";
import SettingSVG from "@/components/SVG/settingSVG.vue";

interface Setting {
    id: string;
    key: string;
    value: string | null;
    type: "text" | "boolean" | "number" | "image";
    group: string;
    image_url: string | null;
}

interface Props extends PageProps {
    groups: Record<string, Setting[]>;
}

const props = defineProps<Props>();

/* ---------- Initial values ---------- */
const initialValues: Record<string, string | boolean> = {};
for (const settings of Object.values(props.groups)) {
    for (const setting of settings) {
        if (setting.type === "boolean") {
            initialValues[setting.key] = setting.value === "true";
        } else {
            initialValues[setting.key] = setting.value ?? "";
        }
    }
}

const values = ref<Record<string, string | boolean>>({ ...initialValues });

const form = useForm({
    settings: [] as { key: string; value: string }[],
    logo: null as File | null,
    remove_logo: false,
});

/* ---------- Logo preview ---------- */
const logoPreview = ref<string | null>(null);
const currentLogo = computed(() => {
    for (const settings of Object.values(props.groups)) {
        const logo = settings.find((s) => s.key === "store_logo");
        if (logo) return logo.image_url;
    }
    return null;
});

function onLogoChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    form.logo = file;
    form.remove_logo = false;

    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
        logoPreview.value = null;
    }

    if (file) {
        logoPreview.value = URL.createObjectURL(file);
    }
}

function clearLogo() {
    form.logo = null;
    form.remove_logo = true;
    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value);
        logoPreview.value = null;
    }
}

function undoRemoveLogo() {
    form.remove_logo = false;
}

const processing = computed(() => form.processing);

/* ---------- Labels ---------- */
const fieldLabels: Record<string, string> = {
    store_name: "Store name",
    store_tagline: "Store tagline",
    store_logo: "Store logo",
    store_email: "Contact email",
    store_phone: "Contact phone",
    store_address: "Physical address",
    currency: "Currency",
    timezone: "Timezone",
    order_prefix: "Order number prefix",
    tax_rate: "Tax rate (%)",
    tax_enabled: "Tax enabled",
    featured_products_limit: "Featured products limit",
    new_arrivals_limit: "New arrivals limit",
    maintenance_mode: "Maintenance mode",
    notification_email_enabled: "Email notifications",
    notification_sms_enabled: "SMS notifications",
    notification_whatsapp_enabled: "WhatsApp notifications",
};

const groupTitles: Record<string, string> = {
    general: "General",
    tax: "Tax",
    contact: "Contact details",
    homepage: "Homepage",
    system: "System",
    notifications: "Notifications",
};

const groupDescriptions: Record<string, string> = {
    general: "Core identity of your storefront — name, branding, currency.",
    tax: "How tax is calculated and displayed on orders.",
    contact: "Where customers can reach you and find your store.",
    homepage: "What appears on the storefront home page.",
    system: "Operational flags and defaults.",
    notifications:
        "Choose which channels are used to send customer notifications.",
};

const groupIcons: Record<string, string> = {
    general: "M3 12h18M3 6h18M3 18h18",
    tax: "M9 14l6-6M9.5 8.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm8 8a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z",
    contact: "M3 8l9 6 9-6M3 8v10a2 2 0 002 2h14a2 2 0 002-2V8M3 8l9-4 9 4",
    homepage: "M3 12l9-9 9 9M5 10v10h14V10",
    system: "M12 15a3 3 0 100-6 3 3 0 000 6zm7-3a7 7 0 01-.1 1.2l2 1.5-2 3.5-2.4-1a7 7 0 01-2.1 1.2L14 21h-4l-.4-2.6a7 7 0 01-2.1-1.2l-2.4 1-2-3.5 2-1.5A7 7 0 015 12z",
    notifications: "M18 8a6 6 0 00-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4",
};

function submit() {
    form.transform((data) => ({
        ...data,
        settings: Object.entries(values.value).map(([key, value]) => ({
            key,
            value: typeof value === "boolean" ? String(value) : value,
        })),
    })).patch(admin_settings_update.url(), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.logo = null;
            if (logoPreview.value) {
                URL.revokeObjectURL(logoPreview.value);
                logoPreview.value = null;
            }
        },
    });
}
</script>

<template>
    <Head title="Settings" />

    <div class="min-h-screen bg-slate-50/60 dark:bg-navy-900">
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
            <!-- Header -->
            <div
                class="p-6 border-0 shadow-md dark:shadow-dark-md mb-6 py-4 bg-primary/10 dark:bg-primary/10 overflow-hidden rounded-md border-none !shadow-none dark:!shadow-none"
                style="border-radius: 12px"
            >
                <div class="flex items-center justify-between gap-6 relative">
                    <div>
                        <h4
                            class="font-semibold text-xl text-dark dark:text-white mb-3"
                        >
                            Store Settings
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
                                Settings
                            </li>
                        </ol>
                    </div>
                    <div
                        class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                    >
                        <SettingSVG />
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit">
                <div class="grid gap-6 lg:grid-cols-12">
                    <!-- Main column -->
                    <div class="space-y-6 lg:col-span-8">
                        <div
                            v-for="(settings, group) in groups"
                            :key="group"
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-navy-600 dark:bg-navy-750"
                        >
                            <!-- Section header -->
                            <div
                                class="flex items-start gap-3 border-b border-slate-100 px-5 py-4 dark:border-navy-600 sm:px-6"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-navy-800 dark:text-navy-200"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="size-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.75"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            :d="
                                                groupIcons[group] ??
                                                groupIcons.general
                                            "
                                        />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2
                                        class="text-base font-semibold text-slate-900 dark:text-navy-50"
                                    >
                                        {{ groupTitles[group] ?? group }}
                                    </h2>
                                    <p
                                        v-if="groupDescriptions[group]"
                                        class="mt-0.5 text-xs text-slate-500 dark:text-navy-300"
                                    >
                                        {{ groupDescriptions[group] }}
                                    </p>
                                </div>
                            </div>

                            <!-- Fields -->
                            <div
                                class="divide-y divide-slate-100 dark:divide-navy-600"
                            >
                                <div
                                    v-for="setting in settings"
                                    :key="setting.id"
                                    class="px-5 py-4 sm:px-6"
                                >
                                    <!-- Image / logo -->
                                    <template v-if="setting.type === 'image'">
                                        <div
                                            class="flex flex-col gap-4 sm:flex-row sm:items-start"
                                        >
                                            <div
                                                class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-navy-600 dark:bg-navy-900"
                                            >
                                                <img
                                                    v-if="logoPreview"
                                                    :src="logoPreview"
                                                    alt="New logo preview"
                                                    class="h-full w-full object-contain p-1.5"
                                                />
                                                <img
                                                    v-else-if="
                                                        currentLogo &&
                                                        !form.remove_logo
                                                    "
                                                    :src="currentLogo"
                                                    alt="Current logo"
                                                    class="h-full w-full object-contain p-1.5"
                                                />
                                                <svg
                                                    v-else
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="size-7 text-slate-300 dark:text-navy-500"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="text-sm font-medium text-slate-700 dark:text-navy-100"
                                                >
                                                    {{
                                                        fieldLabels[
                                                            setting.key
                                                        ] ?? setting.key
                                                    }}
                                                </p>
                                                <p
                                                    class="mt-0.5 text-xs text-slate-500 dark:text-navy-300"
                                                >
                                                    PNG, JPG, or SVG. Max 2 MB.
                                                    Shown in the storefront
                                                    header.
                                                </p>

                                                <div
                                                    class="mt-3 flex flex-wrap items-center gap-2"
                                                >
                                                    <label
                                                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50 dark:border-navy-600 dark:bg-navy-800 dark:text-navy-100 dark:hover:bg-navy-700"
                                                    >
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="size-3.5"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5-5m0 0l5 5m-5-5v12"
                                                            />
                                                        </svg>
                                                        <span>Choose file</span>
                                                        <input
                                                            type="file"
                                                            accept="image/*"
                                                            class="sr-only"
                                                            @change="
                                                                onLogoChange
                                                            "
                                                        />
                                                    </label>

                                                    <button
                                                        v-if="form.remove_logo"
                                                        type="button"
                                                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-primary hover:bg-primary/5 dark:text-accent-light dark:hover:bg-accent-light/10"
                                                        @click="undoRemoveLogo"
                                                    >
                                                        Undo remove
                                                    </button>
                                                    <button
                                                        v-else-if="
                                                            currentLogo ||
                                                            logoPreview
                                                        "
                                                        type="button"
                                                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-error hover:bg-error/5"
                                                        @click="clearLogo"
                                                    >
                                                        Remove logo
                                                    </button>
                                                </div>

                                                <p
                                                    v-if="form.errors.logo"
                                                    class="mt-1.5 text-xs text-error"
                                                >
                                                    {{ form.errors.logo }}
                                                </p>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Boolean toggle (modern switch) -->
                                    <template
                                        v-else-if="setting.type === 'boolean'"
                                    >
                                        <label
                                            class="inline-flex items-center space-x-2"
                                        >
                                            <input
                                                v-model="values[setting.key]"
                                                class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:!bg-success checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:before:bg-white"
                                                type="checkbox"
                                            />
                                            <span>{{
                                                fieldLabels[setting.key] ??
                                                setting.key
                                            }}</span>
                                        </label>
                                    </template>

                                    <!-- Number -->
                                    <template
                                        v-else-if="setting.type === 'number'"
                                    >
                                        <label class="block">
                                            <span
                                                class="text-sm font-medium text-slate-700 dark:text-navy-100"
                                            >
                                                {{
                                                    fieldLabels[setting.key] ??
                                                    setting.key
                                                }}
                                            </span>
                                            <input
                                                v-model.number="
                                                    values[setting.key]
                                                "
                                                type="number"
                                                min="0"
                                                class="form-input mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 transition-colors hover:border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-navy-600 dark:bg-navy-900 dark:text-navy-100 dark:hover:border-navy-500 dark:focus:border-accent dark:focus:ring-accent/20"
                                            />
                                            <span
                                                v-if="
                                                    setting.key ===
                                                        'featured_products_limit' ||
                                                    setting.key ===
                                                        'new_arrivals_limit'
                                                "
                                                class="mt-1.5 block text-xs text-slate-500 dark:text-navy-300"
                                            >
                                                How many products appear on the
                                                homepage. 0 hides the section.
                                            </span>
                                        </label>
                                    </template>

                                    <!-- Text -->
                                    <template v-else>
                                        <label class="block">
                                            <span
                                                class="text-sm font-medium text-slate-700 dark:text-navy-100"
                                            >
                                                {{
                                                    fieldLabels[setting.key] ??
                                                    setting.key
                                                }}
                                            </span>
                                            <input
                                                v-model="values[setting.key]"
                                                class="form-input mt-2 w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 transition-colors placeholder:text-slate-400/70 hover:border-slate-300 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-navy-600 dark:bg-navy-900 dark:text-navy-100 dark:hover:border-navy-500 dark:focus:border-accent dark:focus:ring-accent/20"
                                            />
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sticky sidebar -->
                    <aside class="lg:col-span-4">
                        <div class="sticky top-6 space-y-4">
                            <!-- Status card -->
                            <div
                                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-navy-600 dark:bg-navy-750"
                            >
                                <div
                                    class="border-b border-slate-100 px-5 py-4 dark:border-navy-600"
                                >
                                    <h3
                                        class="text-sm font-semibold text-slate-900 dark:text-navy-50"
                                    >
                                        Publish changes
                                    </h3>
                                </div>
                                <div class="space-y-3 p-5">
                                    <p
                                        class="text-xs leading-relaxed text-slate-500 dark:text-navy-300"
                                    >
                                        Your settings take effect immediately
                                        after saving. Some changes, like the
                                        logo, will be visible to customers on
                                        their next page load.
                                    </p>
                                    <button
                                        type="submit"
                                        :disabled="processing"
                                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-focus active:bg-primary-focus/90 disabled:opacity-50 dark:bg-accent dark:hover:bg-accent-focus"
                                    >
                                        <svg
                                            v-if="processing"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="size-4 animate-spin"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-opacity="0.25"
                                                stroke-width="3"
                                            />
                                            <path
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"
                                            />
                                        </svg>
                                        <span>{{
                                            processing
                                                ? "Saving…"
                                                : "Save settings"
                                        }}</span>
                                    </button>
                                    <p
                                        class="text-center text-[11px] text-slate-400 dark:text-navy-400"
                                    >
                                        Changes apply to the public storefront.
                                    </p>
                                </div>
                            </div>

                            <!-- Quick reference -->
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5 dark:border-navy-600 dark:bg-navy-800/40"
                            >
                                <h4
                                    class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-navy-300"
                                >
                                    Tips
                                </h4>
                                <ul
                                    class="mt-3 space-y-2.5 text-xs leading-relaxed text-slate-600 dark:text-navy-300"
                                >
                                    <li class="flex gap-2">
                                        <span
                                            class="mt-1.5 size-1 shrink-0 rounded-full bg-slate-400 dark:bg-navy-500"
                                        ></span>
                                        <span
                                            >Set the homepage limits to
                                            <strong
                                                class="font-semibold text-slate-800 dark:text-navy-100"
                                                >0</strong
                                            >
                                            to hide a section entirely.</span
                                        >
                                    </li>
                                    
                                    <li class="flex gap-2">
                                        <span
                                            class="mt-1.5 size-1 shrink-0 rounded-full bg-slate-400 dark:bg-navy-500"
                                        ></span>
                                        <span
                                            >Logos display best with a
                                            transparent background.</span
                                        >
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
    </div>
</template>
