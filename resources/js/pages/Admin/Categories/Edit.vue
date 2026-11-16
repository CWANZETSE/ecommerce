<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import { admin_categories_index, admin_categories_update } from '@/routes';

interface Option { id: string; name: string }
interface Category {
    id: string;
    name: string;
    parent_id: string | null;
    description: string | null;
    image_url: string | null;
    is_active: boolean;
    sort_order: number;
    seo_title: string | null;
    seo_description: string | null;
}

const props = defineProps<PageProps & { category: Category; parents: Option[] }>();

const form = useForm({
    name: props.category.name,
    parent_id: props.category.parent_id ?? '',
    description: props.category.description ?? '',
    image: null as File | null,
    is_active: props.category.is_active,
    sort_order: props.category.sort_order,
    seo_title: props.category.seo_title ?? '',
    seo_description: props.category.seo_description ?? '',
});

function onImageChange(event: Event) {
    const input = event.target as HTMLInputElement;
    form.image = input.files?.[0] ?? null;
}

function submit() {
    form
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(admin_categories_update.url(props.category.id), { forceFormData: true });
}
</script>

<template>
    <Head :title="`Edit — ${category.name}`" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    Edit Category
                </h2>
                <div class="hidden h-full py-1 sm:flex">
                    <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
                </div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <Link
                            class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            :href="admin_categories_index.url()"
                        >
                            Categories
                        </Link>
                        <svg
                            x-ignore
                            xmlns="http://www.w3.org/2000/svg"
                            class="size-4 text-slate-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                    <li class="text-slate-500 dark:text-navy-300">Edit</li>
                </ul>
            </div>
            <Link
                :href="admin_categories_index.url()"
                class="btn space-x-1.5 bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
            >
                <span>← Back</span>
            </Link>
        </div>

        <form class="space-y-6" @submit.prevent="submit">
            <div class="card p-4 sm:p-5">
                <div class="space-y-4">
                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Name</span>
                        <input
                            v-model="form.name"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Enter category name"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-error">{{ form.errors.name }}</p>
                    </label>

                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Parent category</span>
                        <select
                            v-model="form.parent_id"
                            class="form-select mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                        >
                            <option value="">None (top-level)</option>
                            <option v-for="parent in props.parents" :key="parent.id" :value="parent.id">
                                {{ parent.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.parent_id" class="mt-1 text-xs text-error">{{ form.errors.parent_id }}</p>
                    </label>

                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Description</span>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Enter description..."
                        ></textarea>
                    </label>

                    <div class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Image</span>

                        <div v-if="category.image_url" class="mt-2.5 flex items-center gap-3">
                            <div class="h-20 w-20 overflow-hidden rounded-lg border border-slate-200 bg-slate-100 dark:border-navy-500 dark:bg-navy-900">
                                <img :src="category.image_url" :alt="category.name" class="h-full w-full object-cover" />
                            </div>
                            <p class="text-xs text-slate-400 dark:text-navy-300">Current image. Upload a new one to replace it.</p>
                        </div>

                        <input
                            type="file"
                            accept="image/*"
                            class="btn bg-slate-150 mt-3 font-medium text-slate-800 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
                        />
                        <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">PNG, JPG, or SVG. Max 2 MB.</p>
                        <p v-if="form.errors.image" class="mt-1 text-xs text-error">{{ form.errors.image }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Sort order</span>
                            <input
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                        </label>
                        <div class="flex items-center pt-6">
                            <label class="inline-flex items-center space-x-2">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 bg-slate-100 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                />
                                <span class="font-medium text-slate-700 dark:text-navy-100">Active</span>
                            </label>
                        </div>
                    </div>

                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">SEO title</span>
                        <input
                            v-model="form.seo_title"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Enter SEO title"
                        />
                    </label>

                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">SEO description</span>
                        <textarea
                            v-model="form.seo_description"
                            rows="2"
                            class="form-textarea mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Enter SEO description..."
                        ></textarea>
                    </label>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                >
                    {{ form.processing ? 'Saving…' : 'Save changes' }}
                </button>
            </div>
        </form>
    </div>
</template>