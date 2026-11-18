<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { PageProps } from '@/types';
import {
    admin_products_index,
    admin_products_update,
    admin_products_destroy,
    admin_products_images_primary,
    admin_products_images_destroy,
} from '@/routes';

interface Option { id: string; name: string }
interface AttributeValue { id: string; value: string }
interface Attribute { id: string; name: string; values: AttributeValue[] }
interface ProductImage { id: string; path: string; url: string | null; is_primary: boolean; alt: string | null }
interface VariationFromServer {
    id: string;
    sku: string;
    price: string | null;
    sale_price: string | null;
    is_default: boolean;
    stock_quantity: number;
    low_stock_threshold: number | null;
    attribute_values: { id: string; value: string; attribute: { name: string } }[];
}
interface Product {
    id: string;
    name: string;
    sku: string;
    category_id: string;
    brand_id: string | null;
    short_description: string | null;
    description: string | null;
    is_active: boolean;
    is_featured: boolean;
    images: ProductImage[];
    variations: VariationFromServer[];
}

interface VariationFormRow {
    id: string | null;
    sku: string;
    price: number | null;
    sale_price: number | null;
    stock_quantity: number;
    is_default: boolean;
    low_stock_threshold: number | null;
    selected: Record<string, string>;
}

const props = defineProps<PageProps & {
    product: Product;
    categories: Option[];
    brands: Option[];
    attributes: Attribute[];
}>();

const form = useForm({
    name: props.product.name,
    sku: props.product.sku,
    category_id: props.product.category_id,
    brand_id: props.product.brand_id ?? '',
    short_description: props.product.short_description ?? '',
    description: props.product.description ?? '',
    is_active: props.product.is_active,
    is_featured: props.product.is_featured,
    show_on_homepage: false,
    images: [] as File[],
    variations: props.product.variations.map((v): VariationFormRow => {
        const selected: Record<string, string> = {};

        for (const av of v.attribute_values) {
            selected[av.attribute.name] = av.id;
        }

        return {
            id: v.id,
            sku: v.sku,
            price: v.price !== null ? Number(v.price) : null,
            sale_price: v.sale_price !== null ? Number(v.sale_price) : null,

            // Normalize database boolean values.
            is_default: v.is_default === true
                || v.is_default === 1
                || v.is_default === '1',

            stock_quantity: v.stock_quantity,
            low_stock_threshold: v.low_stock_threshold,
            selected,
        };
    }),
});

/* ---------------- Variations ---------------- */
const expandedVariation = ref<string | null>(null);

function variationKey(index: number, row: VariationFormRow): string {
    return row.id ?? `new-${index}`;
}

function toggleVariation(key: string) {
    expandedVariation.value = expandedVariation.value === key ? null : key;
}

function addVariation() {
    form.variations.push({
        id: null,
        sku: '',
        price: null,
        sale_price: null,
        is_default: false,
        stock_quantity: 0,
        low_stock_threshold: null,
        selected: {},
    });
    // Auto-expand the newly added variation
    const newIndex = form.variations.length - 1;
    expandedVariation.value = variationKey(newIndex, form.variations[newIndex]);
}

function removeVariation(index: number) {
    form.variations.splice(index, 1);
}

function toggleAttribute(row: VariationFormRow, attributeName: string, valueId: string) {
    if (row.selected[attributeName] === valueId) {
        delete row.selected[attributeName];
    } else {
        row.selected[attributeName] = valueId;
    }
}

/* ---------------- Submit / Delete ---------------- */
function submit() {
    form
        .transform((data) => ({
            ...data,
            variations: data.variations.map((row) => ({
                id: row.id,
                sku: row.sku,
                price: row.price,
                sale_price: row.sale_price,
                is_default: Boolean(row.is_default),
                stock_quantity: row.stock_quantity,
                low_stock_threshold: row.low_stock_threshold,
                attribute_value_ids: Object.values(row.selected),
            })),
        }))
        .put(admin_products_update.url(props.product.id));
}

function deleteProduct() {
    if (confirm('Delete this product? This action cannot be undone.')) {
        form.delete(admin_products_destroy.url(props.product.id));
    }
}

/* ---------------- Image actions ---------------- */
const imageActionBusy = ref<string | null>(null);

function setPrimaryImage(image: { id: string }) {
    imageActionBusy.value = image.id;
    router.patch(
        admin_products_images_primary.url({
            product: props.product.id,
            image: image.id,
        }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                imageActionBusy.value = null;
            },
        },
    );
}

function deleteImage(image: { id: string }) {
    if (!confirm('Delete this image? This cannot be undone.')) return;

    imageActionBusy.value = image.id;
    router.delete(
        admin_products_images_destroy.url({
            product: props.product.id,
            image: image.id,
        }),
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                imageActionBusy.value = null;
            },
        },
    );
}
function setDefaultVariation(selectedRow: VariationFormRow) {
    form.variations.forEach((row) => {
        row.is_default = row === selectedRow;
    });
}


</script>

<template>

    <Head :title="`Edit — ${product.name}`" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    Edit Product
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
                    <li class="text-slate-500 dark:text-navy-300">Edit</li>
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

                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Product SKU</span>
                        <input v-model="form.sku"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Enter SKU" />
                        <p v-if="form.errors.sku" class="mt-1 text-xs text-error">{{ form.errors.sku }}</p>
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
                        <label class="inline-flex items-center space-x-2">
                            <input v-model="form.show_on_homepage" type="checkbox"
                                class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 bg-slate-100 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent" />
                            <span class="font-medium text-slate-700 dark:text-navy-100">Show on homepage</span>
                        </label>
                    </div>

                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Short description</span>
                        <textarea v-model="form.short_description" rows="2"
                            class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Enter short description..."></textarea>
                    </label>

                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Description</span>
                        <textarea v-model="form.description" rows="6"
                            class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Enter full description..."></textarea>
                    </label>

                    <div class="block">
                        <input type="file" multiple accept="image/*"
                            class="btn bg-slate-150 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                            @change="(e) => form.images = Array.from((e.target as HTMLInputElement).files ?? [])" />
                    </div>

                    <div v-if="product.images.length"
                        class="rounded-lg border border-slate-200 p-4 dark:border-navy-600">
                        <p class="font-medium text-slate-700 dark:text-navy-100">Existing images</p>
                        <div class="mt-3 flex flex-row flex-nowrap gap-3 overflow-x-auto pb-1">
                            <div v-for="img in product.images" :key="img.id"
                                class="group relative h-24 w-24 shrink-0 overflow-hidden rounded-lg border bg-slate-100 dark:border-navy-500 dark:bg-navy-900"
                                :class="img.is_primary ? 'ring-2 ring-primary dark:ring-accent' : ''">
                                <img :src="img.url!" :alt="img.alt ?? product.name"
                                    class="h-full w-full object-cover" />

                                <span v-if="img.is_primary"
                                    class="absolute left-1 top-1 z-10 rounded-full bg-primary/90 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white shadow-sm dark:bg-accent/90">
                                    Primary
                                </span>

                                <div class="absolute inset-0 z-20 flex flex-col items-center justify-center gap-1.5 bg-black/55 transition-opacity duration-200
                                           md:invisible md:opacity-0
                                           group-hover:visible group-hover:opacity-100">
                                    <button v-if="!img.is_primary" type="button"
                                        class="rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-semibold text-slate-800 shadow-sm transition hover:bg-white disabled:opacity-50"
                                        :disabled="imageActionBusy === img.id" @click="setPrimaryImage(img)">
                                        Set primary
                                    </button>
                                    <button type="button"
                                        class="rounded-full bg-error px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm transition hover:bg-error/90 disabled:opacity-50"
                                        :disabled="imageActionBusy === img.id" @click="deleteImage(img)">
                                        {{ imageActionBusy === img.id ? '…' : 'Delete' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Variations (accordion) -->
            <section class="card p-4 sm:p-5">
                <div class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-navy-600">
                    <h2 class="text-base font-semibold text-slate-700 dark:text-navy-100">Variations</h2>
                    <button type="button"
                        class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light"
                        @click="addVariation">
                        + Add variation
                    </button>
                </div>

                <p v-if="form.errors.variations" class="mt-3 text-xs text-error">{{ form.errors.variations }}</p>

                <p v-if="form.variations.length === 0" class="mt-4 text-sm text-error">
                    No variations yet. Add at least one.
                </p>

                <!-- Accordion -->
                <div class="flex flex-col space-y-3 sm:space-y-4 lg:space-y-5">
                    <div v-for="(row, index) in form.variations" :key="variationKey(index, row)"
                        class="overflow-hidden rounded-lg border border-slate-150 dark:border-navy-500">
                        <!-- Header -->
                        <div class="flex cursor-pointer items-center justify-between bg-slate-150 px-4 py-4 transition-colors hover:bg-slate-200/60 dark:bg-navy-500 dark:hover:bg-navy-450/60 sm:px-5"
                            @click="toggleVariation(variationKey(index, row))">
                            <div class="flex min-w-0 items-center space-x-3.5">
                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-warning text-xs font-semibold uppercase text-white dark:bg-accent">
                                    #{{ index + 1 }}
                                </div>
                                <div class="min-w-0">
                                    <p class="line-clamp-1 text-slate-700 dark:text-navy-100">
                                        {{ row.sku || 'Untitled variation' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-navy-300">
                                        {{ Object.keys(row.selected).length }} attribute{{
                                            Object.keys(row.selected).length === 1 ? '' : 's' }}
                                        <span v-if="row.stock_quantity !== null"> · {{ row.stock_quantity }} in
                                            stock</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button"
                                    class="badge space-x-2.5 rounded-full bg-error/10 text-error hover:underline dark:bg-error/15"
                                    @click.stop="removeVariation(index)">
                                    Remove
                                </button>
                                <button type="button"
                                    class="btn -mr-1.5 size-8 rounded-full p-0 text-slate-600 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:text-navy-200 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                                    :aria-expanded="expandedVariation === variationKey(index, row)"
                                    :aria-controls="`variation-${variationKey(index, row)}-panel`"
                                    @click.stop="toggleVariation(variationKey(index, row))">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="size-4 transition-transform duration-200"
                                        :class="expandedVariation === variationKey(index, row) ? '-rotate-180' : ''"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Body (grid trick for smooth collapse) -->
                        <div :id="`variation-${variationKey(index, row)}-panel`"
                            class="grid transition-[grid-template-rows,opacity] duration-300 ease-in-out" :class="expandedVariation === variationKey(index, row)
                                ? 'grid-rows-[1fr] opacity-100'
                                : 'grid-rows-[0fr] opacity-0'
                                ">
                            <div class="overflow-hidden">
                                <div
                                    class="alert mx-4 mt-2 flex overflow-hidden rounded-lg bg-warning/10 text-warning dark:bg-warning/15">
                                    <div class="flex flex-1 items-center space-x-3 p-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                        <div class="flex-1">Variant stock levels can be adjusted by navigating to
                                            Inventory → Adjust Stock.</div>
                                    </div>

                                    <div class="w-1.5 bg-warning"></div>
                                </div>
                                <div class="px-4 py-4 sm:px-5">
                                    <div class="grid gap-3 sm:grid-cols-4">
                                        <div>
                                            <label
                                                class="text-xs font-medium text-slate-600 dark:text-navy-300">SKU</label>
                                            <input v-model="row.sku"
                                                class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-2.5 py-1.5 text-sm dark:border-navy-450 dark:bg-navy-900" />
                                        </div>
                                        <div>
    <label class="text-xs font-medium text-slate-600 dark:text-navy-300">
        Price (KES) <span class="text-error">*</span>
    </label>
    <input
        v-model.number="row.price"
        type="number"
        min="0.01"
        step="0.01"
        required
        class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-2.5 py-1.5 text-sm dark:border-navy-450 dark:bg-navy-900"
        placeholder="0.00"
    />
    <p
        v-if="form.errors[`variations.${index}.price`]"
        class="mt-1 text-xs text-error"
    >
        {{ form.errors[`variations.${index}.price`] }}
    </p>
</div>
                                        <div>
                                            <label class="text-xs font-medium text-slate-600 dark:text-navy-300">Sale
                                                price</label>
                                            <input v-model.number="row.sale_price" type="number" step="0.01"
                                                class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-2.5 py-1.5 text-sm dark:border-navy-450 dark:bg-navy-900" />
                                        </div>

                                        <div>
                                            <label class="text-xs font-medium text-slate-600 dark:text-navy-300">Low
                                                stock</label>
                                            <input v-model.number="row.low_stock_threshold" type="number"
                                                class="form-input mt-1 w-full rounded-lg border border-slate-300 bg-transparent px-2.5 py-1.5 text-sm dark:border-navy-450 dark:bg-navy-900" />
                                        </div>
                                        <div>
    <label class="inline-flex cursor-pointer items-center gap-2">
        <input
            type="radio"
            name="default_variation"
            :checked="row.is_default"
            class="size-4 border-slate-400 text-primary focus:ring-primary dark:border-navy-500 dark:bg-navy-900"
            @change="setDefaultVariation(row)"
        />
        <span class="font-medium text-slate-700 dark:text-navy-100">
            Default variation
        </span>
    </label>
</div>
                                    </div>

                                    <div class="mt-4 space-y-3">
                                        <div v-for="attr in attributes" :key="attr.id">
                                            <p class="text-xs font-medium text-slate-500 dark:text-navy-300">{{
                                                attr.name }}</p>
                                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                                <button v-for="val in attr.values" :key="val.id" type="button"
                                                    class="btn rounded-full bg-slate-150 font-medium text-slate-800 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                                                    :class="row.selected[attr.name] === val.id ? 'border-primary bg-primary/10 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-300 bg-white hover:bg-slate-100 dark:border-navy-500 dark:bg-navy-800 dark:hover:bg-navy-700'"
                                                    @click="toggleAttribute(row, attr.name, val.id)">
                                                    {{ val.value }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <p v-if="form.errors[`variations.${index}.sku`]" class="mt-2 text-xs text-error">
                                        {{ form.errors[`variations.${index}.sku`] }}
                                    </p>
                                    <p v-if="form.errors[`variations.${index}.attribute_value_ids`]"
                                        class="mt-2 text-xs text-error">
                                        {{ form.errors[`variations.${index}.attribute_value_ids`] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex gap-3 pt-2">
                <button type="submit" :disabled="form.processing"
                    class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                    {{ form.processing ? 'Saving…' : 'Save changes' }}
                </button>
                <button type="button"
                    class="btn border border-secondary/30 bg-secondary/10 font-medium text-secondary hover:bg-secondary/20 focus:bg-secondary/20 active:bg-secondary/25 dark:border-secondary-light/30 dark:bg-secondary-light/10 dark:text-secondary-light dark:hover:bg-secondary-light/20 dark:focus:bg-secondary-light/20 dark:active:bg-secondary-light/25"
                    @click="deleteProduct">
                    Delete product
                </button>
            </div>
        </form>
    </div>
</template>