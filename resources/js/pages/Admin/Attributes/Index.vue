<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { PageProps } from '@/types';
import {
    admin_attributes_index,
    admin_attributes_store,
    admin_attributes_update,
    admin_attributes_destroy,
    admin_attributes_values_destroy,
    admin_attributes_values_store,
    admin_dashboard,
} from '@/routes';
import Pagination from '@/components/Pagination.vue';
import AttributeSVG from '@/components/SVG/attributeSVG.vue';

interface AttributeValue { id: string; value: string }
interface Attribute {
    id: string;
    name: string;
    slug: string;
    values: AttributeValue[];
}

interface Paginator<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<PageProps & { attributes: Paginator<Attribute> }>();

// New attribute
const newAttr = useForm({ name: '' });
function createAttribute() {
    newAttr.post(admin_attributes_store.url(), { onSuccess: () => newAttr.reset() });
}

// New value per attribute (independent form per row)
const valueForms = ref<Record<string, string>>({});

function addValue(attribute: Attribute) {
    const value = (valueForms.value[attribute.id] ?? '').trim();
    if (!value) return;
    router.post(admin_attributes_values_store.url(attribute.id), { value }, {
        preserveScroll: true,
        onSuccess: () => { valueForms.value[attribute.id] = ''; },
    });
}

function removeValue(attribute: Attribute, value: AttributeValue) {
    if (!confirm(`Remove value "${value.value}"?`)) return;
    router.delete(admin_attributes_values_destroy.url(attribute.id, value.id), { preserveScroll: true });
}

function renameAttribute(attribute: Attribute) {
    const name = prompt('Rename attribute', attribute.name);
    if (!name || name === attribute.name) return;
    router.patch(admin_attributes_update.url(attribute.id), { name }, { preserveScroll: true });
}

function deleteAttribute(attribute: Attribute) {
    if (!confirm(`Delete attribute "${attribute.name}" and all its values?`)) return;
    router.delete(admin_attributes_destroy.url(attribute.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Attributes" />

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
                        Product Attributes
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
                            Attributes
                        </li>
                    </ol>
                </div>
                <div
                    class="hidden lg:flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                >
                    <AttributeSVG />
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-4 sm:p-5">
                <p class="text-sm text-slate-500 dark:text-navy-300">
                    Attributes like <strong class="text-slate-700 dark:text-navy-100">Size</strong> or <strong class="text-slate-700 dark:text-navy-100">Colour</strong> define the options available when building product variations.
                </p>

                <!-- Create attribute -->
                <form class="mt-4 flex max-w-md gap-2" @submit.prevent="createAttribute">
                    <input
                        v-model="newAttr.name"
                        placeholder="New attribute name (e.g. Colour)"
                        class="form-input flex-1 rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                    />
                    <button
                        type="submit"
                        :disabled="newAttr.processing"
                        class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                    >
                        Add Attribute
                    </button>
                </form>
                <p v-if="newAttr.errors.name" class="mt-1 text-xs text-error">{{ newAttr.errors.name }}</p>
            </div>

            <div class="space-y-4">
                <!-- Added .data here to loop through the paginated items -->
                <div
                    v-for="attribute in props.attributes.data"
                    :key="attribute.id"
                    class="card overflow-hidden"
                >
                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3.5 dark:border-navy-600 dark:bg-navy-900 sm:px-5">
                        <div>
                            <p class="font-medium text-slate-700 dark:text-navy-100">{{ attribute.name }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                type="button"
                                class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                                title="Rename"
                                @click="renameAttribute(attribute)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                class="btn border border-secondary/30 bg-secondary/10 font-medium text-secondary hover:bg-secondary/20 focus:bg-secondary/20 active:bg-secondary/25 dark:border-secondary-light/30 dark:bg-secondary-light/10 dark:text-secondary-light dark:hover:bg-secondary-light/20 dark:focus:bg-secondary-light/20 dark:active:bg-secondary-light/25"
                                title="Delete"
                                @click="deleteAttribute(attribute)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5">
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="value in attribute.values"
                                :key="value.id"
                                class="btn bg-primary/10 rounded-full font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                            >
                                {{ value.value }}
                                <button
                                    type="button"
                                    class="text-secondary hover:text-secondary dark:text-navy-300 dark:hover:text-secondary ml-3"
                                    @click="removeValue(attribute, value)"
                                >
                                    ×
                                </button>
                            </span>
                            <span v-if="attribute.values.length === 0" class="text-xs text-slate-400 dark:text-navy-300">
                                No values yet.
                            </span>
                        </div>

                        <div class="mt-4 flex max-w-xs gap-2">
                            <input
                                v-model="valueForms[attribute.id]"
                                placeholder="Add value (e.g. Red)"
                                class="form-input flex-1 rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-sm placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                                @keydown.enter.prevent="addValue(attribute)"
                            />
                            <button
                                type="button"
                                class="btn bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                                @click="addValue(attribute)"
                            >
                                Add
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Check length of .data -->
                <div v-if="props.attributes.data.length === 0" class="card px-4 py-8 text-center text-slate-400 dark:text-navy-300">
                    No attributes yet. Create one to start building variations.
                </div>
            </div>

            <!-- Add pagination wrapper -->
            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-150 bg-white shadow-sm dark:border-slate-700 dark:bg-navy-800">
                <Pagination :paginator="attributes" />
            </div>
            
        </div>
    </div>
</template>