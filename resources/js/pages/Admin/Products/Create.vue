<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import { admin_products_index, admin_products_store } from '@/routes';

import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const description = defineModel<string>({
    default: '',
});

const toolbar = [
    ['bold', 'italic', 'underline', 'strike'],
    [{ header: [1,2, 3, false] }],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ align: [] }],
    ['link'],
    ['clean'],
];

interface Option {
    id: string;
    name: string;
}

interface Attribute {
    id: string;
    name: string;
    values: { id: string; value: string }[];
}

interface VariationFormRow {
    id: string | null;
    price: number | null;
    sale_price: number | null;
    stock_quantity: number;
    is_default: boolean;
    low_stock_threshold: number | null;
    selected: Record<string, string>;
}

defineProps<PageProps & {
    categories: Option[];
    brands: Option[];
    attributes: Attribute[];
}>();
const form = useForm({
    name: '',
    category_id: '',
    brand_id: '',
    short_description: '',
    description: '',
    is_active: true,
    is_featured: false,
    images: [] as File[],
    variations: [] as VariationFormRow[],
});

function addVariation() {
    form.variations.push({
        id: null,
        price: null,
        sale_price: null,
        stock_quantity: 0,
        is_default: form.variations.length === 0,
        low_stock_threshold: null,
        selected: {},
    });
}

function removeVariation(index: number) {
    const wasDefault = form.variations[index].is_default;

    form.variations.splice(index, 1);

    // Automatically select another default if the default was removed.
    if (wasDefault && form.variations.length > 0) {
        form.variations[0].is_default = true;
    }
}

function setDefaultVariation(selectedRow: VariationFormRow) {
    form.variations.forEach((row) => {
        row.is_default = row === selectedRow;
    });

    form.clearErrors('variations');
}

function toggleAttribute(
    row: VariationFormRow,
    attributeName: string,
    valueId: string,
) {
    if (row.selected[attributeName] === valueId) {
        delete row.selected[attributeName];
    } else {
        row.selected[attributeName] = valueId;
    }
}

function validateVariations(): boolean {
    if (form.variations.length === 0) {
        form.errors.variations = 'At least one variation is required.';
        return false;
    }

    const defaultCount = form.variations.filter(
        (row) => row.is_default === true,
    ).length;

    if (defaultCount !== 1) {
        form.errors.variations =
            'Select exactly one variation as the default variation.';
        return false;
    }

    form.clearErrors('variations');

    return true;
}

function submit() {
    if (!validateVariations()) {
        return;
    }

    form
        .transform((data) => ({
            ...data,
            variations: data.variations.map((row) => ({
                id: row.id,
                price: row.price,
                sale_price: row.sale_price,
                is_default: row.is_default === true,
                stock_quantity: row.stock_quantity,
                low_stock_threshold: row.low_stock_threshold,
                attribute_value_ids: Object.values(row.selected),
            })),
        }))
        .post(admin_products_store.url());
}
</script>


<template>

    <Head title="New Product" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    New Product
                </h2>
                <div class="hidden h-full py-1 sm:flex">
                    <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
                </div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <Link
                            class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            :href="admin_products_index.url()">
                            Products
                        </Link>
                        <svg x-ignore xmlns="http://www.w3.org/2000/svg" class="size-4 text-slate-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                    <li class="text-slate-500 dark:text-navy-300">Create</li>
                </ul>
            </div>
            <Link :href="admin_products_index.url()"
                class="btn space-x-1.5 bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90">
                <span>← Back</span>
            </Link>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="card p-4 sm:p-5">
                <div class="space-y-4">
                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Name</span>
                        <input v-model="form.name"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Enter product name" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-error">{{ form.errors.name }}</p>
                    </label>

                    

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Category</span>
                            <select v-model="form.category_id"
                                class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="">Select…</option>
                                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-1 text-xs text-error">{{ form.errors.category_id
                                }}</p>
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Brand</span>
                            <select v-model="form.brand_id"
                                class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="">None</option>
                                <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                            </select>
                        </label>
                    </div>



                    <div class="flex flex-wrap items-center gap-6 pt-2">
                        <label class="inline-flex items-center space-x-2">
                            <input v-model="form.is_active" type="checkbox"
                                class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 bg-slate-100 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent" />
                            <span class="font-medium text-slate-700 dark:text-navy-100">Active</span>
                        </label>
                        <label class="inline-flex items-center space-x-2">
                            <input v-model="form.is_featured" type="checkbox"
                                class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 bg-slate-100 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent" />
                            <span class="font-medium text-slate-700 dark:text-navy-100">Featured</span>
                        </label>
                    </div>

                    
                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">
                            Description
                        </span>
                        <QuillEditor
                            v-model:content="form.description"
                            content-type="html"
                            theme="snow"
                            :toolbar="toolbar"
                            placeholder="Write a detailed product description..."
                            style="min-height: 240px;"
                        />
   
                    </label>

                    <div class="block">
                        <input type="file" multiple accept="image/*"
                            class="btn bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                            @change="(e) => form.images = Array.from((e.target as HTMLInputElement).files ?? [])" />
                    </div>
                </div>
            </div>

            <section class="card p-4 sm:p-5">
                <div class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-navy-600">
                    <h2 class="text-base font-semibold text-slate-700 dark:text-navy-100">Variations</h2>
                    <button type="button"
                        class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light"
                        @click="addVariation">
                        + Add variation
                    </button>
                </div>
                <div
                    class="alert flex rounded-lg my-2 border border-primary/30 bg-primary/10 py-4 px-4 text-primary/70 sm:px-5">
                    Set the lowest-priced variation as the default, as its price is displayed on the storefront before
                    customers select a variation.
                </div>


                <div v-if="form.variations.length === 0"
                    class="alert mt-2 flex space-x-2 rounded-lg border border-error px-4 py-4 text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <p>No variations yet. Add atleast one.</p>
                </div>

                <div v-for="(row, index) in form.variations" :key="`new-${index}`"
                    class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-navy-600 dark:bg-navy-750">
                    <div class="flex items-center justify-between gap-4">
                        <p class="font-medium text-slate-700 dark:text-navy-100">Variation {{ index + 1 }}</p>
                        <button type="button" class="text-sm font-medium text-error hover:underline"
                            @click="removeVariation(index)">
                            Remove
                        </button>
                    </div>


                    <div class="mt-3 grid gap-3 sm:grid-cols-3 lg:grid-cols-6">
                        

                        <div>
                            <label class="text-xs font-medium text-slate-600 dark:text-navy-300">
                                Price (KES) <span class="text-error">*</span>
                            </label>
                            <input v-model.number="row.price" type="number" min="0.01" step="0.01" required
                                class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-2.5 py-1.5 text-sm dark:border-navy-450 dark:bg-navy-900"
                                placeholder="0.00" />
                            <p v-if="form.errors[`variations.${index}.price`]" class="mt-1 text-xs text-error">
                                {{ form.errors[`variations.${index}.price`] }}
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-600 dark:text-navy-300">
                                Sale price (KES)
                            </label>
                            <input v-model.number="row.sale_price" type="number" min="0.01" step="0.01"
                                class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-2.5 py-1.5 text-sm dark:border-navy-450 dark:bg-navy-900"
                                placeholder="Optional" />
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-600 dark:text-navy-300">
                                Stock
                            </label>
                            <input v-model.number="row.stock_quantity" type="number" min="0"
                                class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-2.5 py-1.5 text-sm dark:border-navy-450 dark:bg-navy-900" />
                        </div>

                        <div>
                            <label class="text-xs font-medium text-slate-600 dark:text-navy-300">
                                Low stock threshold
                            </label>
                            <input v-model.number="row.low_stock_threshold" type="number" min="0"
                                class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-2.5 py-1.5 text-sm dark:border-navy-450 dark:bg-navy-900" />
                        </div>

                        <div class="flex items-end pb-1">
                            <label class="inline-flex cursor-pointer items-center gap-2">
                                <input type="radio" :name="`default_variation`" :checked="row.is_default"
                                    class="form-checkbox is-outline size-5 rounded-sm border-slate-400/70 before:!bg-warning checked:!border-warning hover:!border-warning focus:!border-warning dark:border-navy-400"
                                    @change="setDefaultVariation(row)" />
                                <span class="text-sm font-medium text-slate-700 dark:text-navy-100">
                                    Default variation
                                </span>
                            </label>
                        </div>
                    </div>


                    <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-3">
                        <div v-for="attr in attributes" :key="attr.id"
                            class="min-w-0 rounded-lg border border-slate-200/60 p-3 dark:border-navy-500/40">
                            <p class="text-xs font-medium text-slate-500 dark:text-navy-300">
                                {{ attr.name }}
                            </p>

                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <button v-for="val in attr.values" :key="val.id" type="button"
                                    class="btn rounded-full border font-medium transition-colors" :class="row.selected[attr.name] === val.id
                                            ? 'border-primary bg-primary/10 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light'
                                            : 'border-slate-300 bg-white text-slate-800 hover:bg-slate-100 dark:border-navy-500 dark:bg-navy-800 dark:text-navy-50 dark:hover:bg-navy-700'
                                        " @click="toggleAttribute(row, attr.name, val.id)">
                                    {{ val.value }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex justify-end pt-2">
                <button type="submit" :disabled="form.processing"
                    class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    {{ form.processing ? 'Saving…' : 'Create Product' }}
                </button>
            </div>
        </form>
    </div>
</template>