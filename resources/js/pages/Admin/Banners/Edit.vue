<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import type { PageProps } from '@/types';
import {
    admin_banners_index,
    admin_banners_update,
    admin_banners_destroy,
} from '@/routes';

interface Banner {
    id: string;
    title: string;
    subtitle: string | null;
    image_path: string;
    link_url: string | null;
    link_text: string | null;
    sort_order: number;
    is_active: boolean;
}

const props = defineProps<PageProps & { banner: Banner }>();

const form = useForm({
    title: props.banner.title,
    subtitle: props.banner.subtitle ?? '',
    link_url: props.banner.link_url ?? '',
    link_text: props.banner.link_text ?? '',
    sort_order: props.banner.sort_order,
    is_active: props.banner.is_active,
    image: null as File | null,
    _method: 'put',
});

function onImageChange(e: Event) {
    const input = e.target as HTMLInputElement;
    form.image = input.files?.[0] ?? null;
}

function submit() {
    form.post(admin_banners_update.url(props.banner.id), { forceFormData: true });
}

function deleteBanner() {
    if (!confirm(`Delete banner "${props.banner.title}"? This cannot be undone.`)) return;

    router.delete(admin_banners_destroy.url(props.banner.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Controller already redirects to admin_banners_index with a flash message
        },
    });
}
</script>

<template>
    <Head :title="`Edit — ${banner.title}`" />

    <div class="p-4 sm:p-5 lg:p-6">
        <div class="flex items-center justify-between py-5 lg:py-6">
            <div class="flex items-center space-x-4">
                <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">
                    Edit Banner
                </h2>
                <div class="hidden h-full py-1 sm:flex">
                    <div class="h-full w-px bg-slate-300 dark:bg-navy-600"></div>
                </div>
                <ul class="hidden flex-wrap items-center space-x-2 sm:flex">
                    <li class="flex items-center space-x-2">
                        <Link
                            class="text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent"
                            :href="admin_banners_index.url()"
                        >
                            Banners
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
                :href="admin_banners_index.url()"
                class="btn space-x-1.5 bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 focus:bg-slate-200 active:bg-slate-200/80 dark:bg-navy-500 dark:text-navy-50 dark:hover:bg-navy-450 dark:focus:bg-navy-450 dark:active:bg-navy-450/90"
            >
                <span>← Back</span>
            </Link>
        </div>

        <form class="space-y-6 max-w-2xl" @submit.prevent="submit">
            <div class="card p-4 sm:p-5">
                <div class="space-y-4">
                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Title</span>
                        <input
                            v-model="form.title"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Enter banner title"
                        />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-error">{{ form.errors.title }}</p>
                    </label>

                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Subtitle</span>
                        <input
                            v-model="form.subtitle"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            placeholder="Enter subtitle (optional)"
                        />
                    </label>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Link URL</span>
                            <input
                                v-model="form.link_url"
                                placeholder="/customer/products?category=electronics"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                        </label>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Link text</span>
                            <input
                                v-model="form.link_text"
                                placeholder="Shop now"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                        </label>
                    </div>

                    <div>
                        <span class="font-medium text-slate-700 dark:text-navy-100 block mb-2">Current Image</span>
                        <div class="overflow-hidden rounded-lg border border-slate-200 dark:border-navy-600 bg-slate-50 dark:bg-navy-900 p-2 inline-block">
                            <img :src="`/storage/${banner.image_path}`" :alt="banner.title" class="h-32 w-auto rounded object-cover shadow-sm" />
                        </div>
                    </div>

                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Replace image</span>
                        <input
                            type="file"
                            accept="image/*"
                            class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent file:mr-4 file:rounded-l-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:file:bg-navy-600 dark:file:text-navy-100"
                            @change="onImageChange"
                        />
                        <p v-if="form.errors.image" class="mt-1 text-xs text-error">{{ form.errors.image }}</p>
                    </label>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 pt-2">
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Sort order</span>
                            <input
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"
                            />
                        </label>
                        <div class="flex items-center pt-6">
                            <label class="inline-flex items-center space-x-2">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="form-checkbox is-basic size-5 rounded-sm border-slate-400/70 bg-slate-100 checked:border-primary checked:bg-primary hover:border-primary focus:border-primary dark:border-navy-500 dark:bg-navy-900 dark:checked:border-accent dark:checked:bg-accent dark:hover:border-accent dark:focus:border-accent"
                                />
                                <span class="font-medium text-slate-700 dark:text-navy-100 text-sm">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="btn bg-primary/10 font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 disabled:opacity-50 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25"
                >
                    {{ form.processing ? 'Saving…' : 'Save changes' }}
                </button>

                <button
                    type="button"
                    class="btn border border-secondary/30 bg-secondary/10 font-medium text-secondary hover:bg-secondary/20 focus:bg-secondary/20 active:bg-secondary/25 dark:border-secondary-light/30 dark:bg-secondary-light/10 dark:text-secondary-light dark:hover:bg-secondary-light/20 dark:focus:bg-secondary-light/20 dark:active:bg-secondary-light/25"

                    :disabled="form.processing"
                    @click="deleteBanner"
                >
                    Delete banner
                </button>
            </div>
        </form>
    </div>
</template>